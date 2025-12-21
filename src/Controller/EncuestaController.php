<?php

namespace App\Controller;

use App\Entity\Contrato;
use App\Entity\Encuesta;
use App\Entity\EncuestaPreguntas;
use App\Entity\FuncionEncuesta;
use App\Entity\FuncionRespuesta;
use App\Entity\Grupo;
use App\Entity\UsuarioTipo;
use App\Form\EncuestaType;
use App\Repository\ContratoRepository;
use App\Repository\CuentaRepository;
use App\Repository\EncuestaPreguntasRepository;
use App\Repository\EncuestaRepository;
use App\Repository\EstadoEncuestaRepository;
use App\Repository\FuncionRespuestaRepository;
use App\Repository\GrupoRepository;
use App\Repository\UsuarioTipoRepository;
use App\Repository\VwContratoRepository;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/encuesta")
 */
class EncuestaController extends AbstractController
{
    /**
     * @Route("/", name="encuesta_index")
     */
    public function index(Request $request,  
                        PaginatorInterface $paginator,
                        VwContratoRepository $contratoRepository,
                        CuentaRepository $cuentaRepository,
                        UsuarioTipoRepository $usuarioTipoRepository
                        ): Response
    {
        $this->denyAccessUnlessGranted('view','Encuesta');
        $user=$this->getUser();
        $perfiles = $usuarioTipoRepository->findBy([],['nombre'=>'Asc']);
         $perfil=0;
        $filtro=null;
        $error='';
        $error_toast="";
        $otros="";
        $folio="";
        $tipoFecha=0;
        $status=null;
        $statusNombre=null;
        if(null !== $request->query->get('error_toast')){
            $error_toast=$request->query->get('error_toast');
        }
        $compania=null;
        if(null !== $request->query->get('bFolio') && $request->query->get('bFolio')!=''){
            $folio=$request->query->get('bFolio');
            $otros=" (c.folioContrato= '$folio' or c.agenda=$folio) ";

            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');
            $fecha=$otros. " and a.status in (7,14)";

        }else{
            if(null !== $request->query->get('bFiltro') && $request->query->get('bFiltro')!=''){
                $filtro=$request->query->get('bFiltro');
            }
            if(null !== $request->query->get('bCompania') && $request->query->get('bCompania')!=0){
                $compania=$request->query->get('bCompania');
            }
            if(null !== $request->query->get('bFecha')){
                $aux_fecha=explode(" - ",$request->query->get('bFecha'));
                $dateInicio=$aux_fecha[0];
                $dateFin=$aux_fecha[1];
            }else{
                $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*1);
                //$dateInicio=date('Y-m-d');
                
                $dateFin=date('Y-m-d');
            }
            if(null !== $request->query->get('bTipoFecha')){
                $tipoFecha = $request->query->get('bTipoFecha');
     
            }

            

            switch($tipoFecha){
                case 0:
                    $fecha="c.fechaCreacion between '$dateInicio' and '$dateFin 23:59:59' and a.status in (7,14)" ;
                    break;
                case 1:
                    $fecha="c.FechaEncuesta between '$dateInicio' and '$dateFin 23:59:59' and a.status in (7,14) and e.id=2" ;
                    $status=0;
                    $statusNombre='Encuestas';
                 
                    break;
                case 2:
                    $fecha="c.FechaGestion between '$dateInicio' and '$dateFin 23:59:59' and a.status in (7,14) and e.id=2" ;
                    $status=1;
                    $statusNombre='Gestiones';
                  
                    break;
            }

            if(null !== $request->query->get('bStatus') && trim($request->query->get('bStatus')!='')){
                if(trim($request->query->get('bStatus'))=='Encuestas'){
                    $status=0;
                    $statusNombre='Encuestas';
                }
                if(trim($request->query->get('bStatus'))=='Gestiones'){
                    $status=1;
                    $statusNombre='Gestiones';
                }       
                
            }
        }
      
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 1:
            case 8:
            case 13:
                $query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha,false,$status);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                $resumen=$contratoRepository->findByEncuestaResumenFechas(null,$user->getEmpresaActual(),$compania,$fecha,$filtro,$tipoFecha,$status,$dateInicio,$dateFin);
                break;
            case 4://Cobradores
                $grupos;
                foreach($user->getUsuarioGrupos() as $usuarioGrupo){
                    $grupos[]=$usuarioGrupo->getGrupo()->getId();
                }
                if($grupos == null){
                    $fecha.=" and c.grupo is null ";
                }else{
                    if(count($grupos)>0){
                        $fecha.=" and c.grupo in (".implode(",",$grupos).") ";
                    }else{
                        $fecha.=" and c.grupo is null ";
                    }
                }
                //$fecha.=" and c.idLote in (".implode(",",$lotes).") ";
                $query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha,false,$status);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                
                $resumen=$contratoRepository->findByEncuestaResumenFechas(null,$user->getEmpresaActual(),$compania,$fecha,$filtro,$tipoFecha,$status,$dateInicio,$dateFin);
                break;
            default:
                $query=$contratoRepository->findByPers($user->getId(),null,$compania,$filtro,null,$fecha,false,$status);
                $companias=$cuentaRepository->findByPers($user->getId());
                
                $resumen=$contratoRepository->findByEncuestaResumenFechas($user->getId(),$user->getEmpresaActual(),$compania,$fecha,$filtro,$tipoFecha,$status,$dateInicio,$dateFin);
                
            break;
        }
        //$resumen=null;
        //$companias=$cuentaRepository->findByPers($user->getId());
        //$query=$contratoRepository->findAll();
        $contratos=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/,
            array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));
    
        return $this->render('encuesta/index.html.twig', [
            'controller_name' => 'EncuestaController',
            'contratos' => $contratos,
            'bFiltro'=>$filtro,
            'bFolio'=>$folio,
            'tipoFecha'=>$tipoFecha,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'pagina'=>"Calidad",
            'error'=>$error,
            'error_toast'=>$error_toast,
            'resumenes'=>$resumen,
            "bStatus"=>$statusNombre,
            'perfiles'=>$perfiles,
            'bPerfil'=>$perfil,
            'TipoFiltro'=>'Encuestas',
        ]);
    }
    /**
     * @Route("/indextest", name="encuesta_indextest")
     */
    public function indextest(Request $request,  
                        PaginatorInterface $paginator,
                        ContratoRepository $contratoRepository,
                        CuentaRepository $cuentaRepository
                        ): Response
    {
        $this->denyAccessUnlessGranted('view','Encuestatest');
        $user=$this->getUser();

        $filtro=null;
        $error='';
        $error_toast="";
        $otros="";
        $folio="";
        $tipoFecha=0;
        $status=null;
        $statusNombre=null;
        if(null !== $request->query->get('error_toast')){
            $error_toast=$request->query->get('error_toast');
        }
        $compania=null;
        if(null !== $request->query->get('bFolio') && $request->query->get('bFolio')!=''){
            $folio=$request->query->get('bFolio');
            $otros=" c.folio= '$folio'";

            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');
            $fecha=$otros. " and a.status in (7,14)";

        }else{
            if(null !== $request->query->get('bFiltro') && $request->query->get('bFiltro')!=''){
                $filtro=$request->query->get('bFiltro');
            }
            if(null !== $request->query->get('bCompania') && $request->query->get('bCompania')!=0){
                $compania=$request->query->get('bCompania');
            }
            if(null !== $request->query->get('bFecha')){
                $aux_fecha=explode(" - ",$request->query->get('bFecha'));
                $dateInicio=$aux_fecha[0];
                $dateFin=$aux_fecha[1];
            }else{
                $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*1);
                //$dateInicio=date('Y-m-d');
                
                $dateFin=date('Y-m-d');
            }
            if(null !== $request->query->get('bTipoFecha')){
                $tipoFecha = $request->query->get('bTipoFecha');
     
            }

            

            switch($tipoFecha){
                case 0:
                    $fecha="c.fechaCreacion between '$dateInicio' and '$dateFin 23:59:59' and a.status in (7,14)" ;
                    break;
                case 1:
                    $fecha="c.FechaEncuesta between '$dateInicio' and '$dateFin 23:59:59' and a.status in (7,14) and e.id=2" ;
                    $status=0;
                    $statusNombre='Encuestas';
                 
                    break;
                case 2:
                    $fecha="c.FechaGestion between '$dateInicio' and '$dateFin 23:59:59' and a.status in (7,14) and e.id=2" ;
                    $status=1;
                    $statusNombre='Gestiones';
                  
                    break;
            }

            if(null !== $request->query->get('bStatus') && trim($request->query->get('bStatus')!='')){
                if(trim($request->query->get('bStatus'))=='Encuestas'){
                    $status=0;
                    $statusNombre='Encuestas';
                }
                if(trim($request->query->get('bStatus'))=='Gestiones'){
                    $status=1;
                    $statusNombre='Gestiones';
                }       
                
            }
        }
      
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 1:
            case 8:
            case 13:
                $query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha,false,$status);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                //$resumen=$contratoRepository->findByEncuestaResumenFechas(null,$user->getEmpresaActual(),$compania,$fecha,$filtro,$tipoFecha,$status,$dateInicio,$dateFin);
                break;
            case 4://Cobradores
                $grupos;
                foreach($user->getUsuarioGrupos() as $usuarioGrupo){
                    $grupos[]=$usuarioGrupo->getGrupo()->getId();
                }
                if($grupos == null){
                    $fecha.=" and c.grupo is null ";
                }else{
                    if(count($grupos)>0){
                        $fecha.=" and c.grupo in (".implode(",",$grupos).") ";
                    }else{
                        $fecha.=" and c.grupo is null ";
                    }
                }
                //$fecha.=" and c.idLote in (".implode(",",$lotes).") ";
                $query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha,false,$status);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                
                //$resumen=$contratoRepository->findByEncuestaResumenFechas(null,$user->getEmpresaActual(),$compania,$fecha,$filtro,$tipoFecha,$status,$dateInicio,$dateFin);
                break;
            default:
                $query=$contratoRepository->findByPers($user->getId(),null,$compania,$filtro,null,$fecha,false,$status);
                $companias=$cuentaRepository->findByPers($user->getId());
                
                //$resumen=$contratoRepository->findByEncuestaResumenFechas($user->getId(),$user->getEmpresaActual(),$compania,$fecha,$filtro,$tipoFecha,$status,$dateInicio,$dateFin);
                
            break;
        }
        $resumen=null;
        //$companias=$cuentaRepository->findByPers($user->getId());
        //$query=$contratoRepository->findAll();
        $contratos=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/,
            array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));
    
        return $this->render('encuesta/indextest.html.twig', [
            'controller_name' => 'EncuestaController',
            'contratos' => $contratos,
            'bFiltro'=>$filtro,
            'bFolio'=>$folio,
            'tipoFecha'=>$tipoFecha,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'pagina'=>"Calidad",
            'error'=>$error,
            'error_toast'=>$error_toast,
            'resumenes'=>$resumen,
            "bStatus"=>$statusNombre
        ]);
    }


    /**
     * @Route("/resumenencuestadores", name="agenda_resumenencuestadores", methods={"GET","POST"})
     */
    public function resumenencuestadores(Request $request,String $fechainicio, String $fechafin, $status,$totalStatus,$tipoFecha, VwContratoRepository $vwContratoRepository): Response
    {
        $user=$this->getUser();
 
        if(is_null($status)){
            $nombre_status="Encuestas";
            $idstatus=0;
        }else{
            $nombre_status=$status;
            if($status=='Gestiones') $idstatus=1;
            if($status=='Encuestas') $idstatus=0;
        }
        $totalStatus=1000;
        switch($tipoFecha){
            case 0:
                $fecha="c.fechaCreacion between '$fechainicio' and '$fechafin 23:59:59' and a.status in (7,14)" ;
                break;
            case 1:
                $fecha="c.FechaEncuesta between '$fechainicio' and '$fechafin 23:59:59' and a.status in (7,14) " ;
                break;
            case 2:
                $fecha="c.FechaGestion between '$fechainicio' and '$fechafin 23:59:59' and a.status in (7,14)" ;
                break;
        }

        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 4:
            case 1:
                
                $queryresumen=$vwContratoRepository->findByEncuestaResumenEncuestador($fecha,$idstatus);
            break;
            default:
                $queryresumen=$vwContratoRepository->findByEncuestaResumenEncuestador($fecha,$idstatus);
            break; 
  
        }
        
        return $this->render('encuesta/_resumenEncuestadores.html.twig',[
            'encuestadores'=>$queryresumen,
            'total'=>$totalStatus,
            'nombre_status'=>$nombre_status,
        ]);
    }

    /**
     * @Route("/{id}/verEncuestas", name="encuesta_ver_encuestas", methods={"GET","POST"})
     */
    public function verEncuestas(Request $request,Contrato $contrato,EstadoEncuestaRepository $estadoEncuestaRepository): Response
    {


        return $this->render('encuesta/verEncuestas.html.twig', [
            'controller_name' => 'EncuestaController',
            'encuestas' => $contrato->getEncuestas(),
            'contrato'=>$contrato
            
        ]);
    }
    /**
     * @Route("/{id}/new_gestion", name="encuesta_new_gestion", methods={"GET","POST"})
     */
    public function newGestion(Request $request,
                            Contrato $contrato,
                            EstadoEncuestaRepository $estadoEncuestaRepository, 
                            FuncionRespuestaRepository $funcionRespuestaRepository,
                            VwContratoRepository $vwContratoRepository): Response
    {
        $user=$this->getUser();
        $encuesta = new Encuesta();
        $encuesta->setUsuarioCreacion($user);
        $encuesta->setContrato($contrato);
        $encuesta->setFechaCreacion(new DateTime(date("Y-m-d H:i")));
        $form = $this->createForm(EncuestaType::class, $encuesta);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $respuesta = $request->request->get('cboRespuesta');

            $encuesta->setFuncionRespuesta($funcionRespuestaRepository->find($respuesta));
            $encuesta->setEstado($estadoEncuestaRepository->find(1));
            $entityManager = $this->getDoctrine()->getManager();
            
            if($encuesta->getFuncionRespuesta()->getId()==1){
                
               // $contrato->setEstadoEncuesta($estadoEncuestaRepository->find(1));
                return $this->redirectToRoute('encuesta_new',['id'=>$contrato->getId()]);
            }else{
                $contrato= $encuesta->getContrato();
                $contrato->setEstadoEncuesta($estadoEncuestaRepository->find(2));
                $contrato->setFechaEncuesta(new DateTime(date("Y-m-d h:i:s")));
                $contrato->setObservacionEncuesta($encuesta->getObservacion());
                $encuesta->setFechaCreacion(new DateTime(date("Y-m-d h:i:s")));
                $encuesta->setEstado($estadoEncuestaRepository->find(2));
                

                $entityManager->persist($encuesta);
                $entityManager->flush();
                $entityManager->persist($contrato);
                $entityManager->flush();

                if($encuesta->getFuncionRespuesta()->getId()==8){
                   
                    return $this->redirectToRoute('app_ticket_new',['id'=>$contrato->getId()]);
                }else{
                    return $this->redirectToRoute('encuesta_ver_encuestas',['id'=>$contrato->getId()]);
                }
            }
        }
        $vwContrato = $vwContratoRepository->find($contrato->getId());
        
        return $this->render('encuesta/funcionEncuesta.html.twig', [
            'controller_name' => 'EncuestaController',
            'contrato'=>$contrato,
            'form' => $form->createView(),
            "encuesta"=>$encuesta,
            "tieneEncuestas"=>$vwContrato->getQtyEncuesta()
            
        ]);
    }
    /**
     * @Route("/{id}/grupo", name="encuesta_grupo", methods={"GET","POST"})
     */
    public function grupo(Request $request, 
                        Contrato $contrato,
                        GrupoRepository $grupoRepository
                        ): Response
    { 
        $this->denyAccessUnlessGranted('edit','encuesta_grupo');
        $user=$this->getUser();
        
        if($request->request->get('cboGrupo')){
            $contrato->setGrupo($grupoRepository->find($request->request->get('cboGrupo')));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contrato);
            $entityManager->flush();
            return $this->redirectToRoute('encuesta_index',['id'=>$contrato->getId()]);
        }


        return $this->render('encuesta/grupo.html.twig', [
            'contrato'=>$contrato,
            'pagina'=>'Editar Grupo Contrato',
            'grupos'=>$grupoRepository->findAll(),
        ]);
    }
    /**
     * @Route("/{id}/abogado", name="encuesta_abogado", methods={"GET","POST"})
     */
    public function abogado(Request $request, 
                        Grupo $grupo): Response
    { 
        $usuarioGrupos =$grupo->getUsuarioGrupos();
        $abogados=array();
        foreach($usuarioGrupos as $usuarioGrupo){
            $abogados=array("nombre"=>$usuarioGrupo->getUsuario()->getNombre());
        }
        return $this->json($abogados);
    }
    /**
     * @Route("/{id}/new", name="encuesta_new", methods={"GET","POST"})
     */
    public function new(Request $request,Contrato $contrato,EstadoEncuestaRepository $estadoEncuestaRepository,FuncionRespuestaRepository $funcionRespuestaRepository): Response
    {
        $this->denyAccessUnlessGranted('create','Encuesta');

        //$contrato=$encuesta->getContrato();
        $encuesta= new Encuesta();
        $user=$this->getUser();
        /**
         * ----Tipo pregunta
         * 1.- selector 1 (1 al 7)
         * 2.- selector 2 (no recuerdo -- nunca)
         * 3.- selector 4 (si -- no)
         * 3.- pregunta abierta
         **/
        if($request->request->get('cboSelector1')!=""){
            $entityManager = $this->getDoctrine()->getManager();
           
            $encuesta->setFuncionRespuesta($funcionRespuestaRepository->find(1));
            $encuesta->setUsuarioCreacion($user);
            $encuesta->setContrato($contrato);
            $encuesta->setFechaCreacion(new DateTime(date("Y-m-d")));
            $encuesta->setObservacion($request->request->get('txtObservacion'));
            $encuesta->setEstado($estadoEncuestaRepository->find($request->request->get("hdEstado")));
            $crearTicket=false;
            
            $encuesta->setFechaCierre(new DateTime(date("Y-m-d")));
   
            $entityManager->persist($encuesta);
            $entityManager->flush();

            $contrato->setEstadoEncuesta($estadoEncuestaRepository->find(2));
            $contrato->setFechaEncuesta(new DateTime(date("Y-m-d H:i:s")));
            $contrato->setObservacionEncuesta($request->request->get('txtObservacion'));
            $contrato->setQtyEncuesta(intval($contrato->getQtyEncuesta())+1);
            $contrato->setQtyGestionEncuesta(intval($contrato->getQtyGestionEncuesta())+1);
            $entityManager->persist($contrato);
            $entityManager->flush();

            $encuestaPregunta=new EncuestaPreguntas();
            $encuestaPregunta->setEncuesta($encuesta);
            $encuestaPregunta->setPregunta("En una escala de 1 a 7 ¿que nota le pondria a nuestro ESTUDIO JURIDICO?... siendo 1 la mas baja y 7 la mas alta");
            $encuestaPregunta->setNota($request->request->get('cboSelector1'));
            $encuestaPregunta->setTipoPregunta(1);
            $entityManager->persist($encuestaPregunta);
            $entityManager->flush();


            if($request->request->get('cboSelector2')!=""){

                $encuestaPregunta=new EncuestaPreguntas();
                $encuestaPregunta->setEncuesta($encuesta);
                $encuestaPregunta->setPregunta("¿Cuando fue la ultima vez que hablo con su ABOGADO DE TRAMITACION?");
                $encuestaPregunta->setNota($request->request->get('cboSelector2'));
                $encuestaPregunta->setTipoPregunta(2);
                $entityManager->persist($encuestaPregunta);
                $entityManager->flush();
            }
            
            if($request->request->get('txtPregunta1')!=""){

                $encuestaPregunta=new EncuestaPreguntas();
                $encuestaPregunta->setEncuesta($encuesta);
                $encuestaPregunta->setPregunta("¿Ha tenido algun problema con el ABOGADO DE TRAMITACIÓN?");
                $encuestaPregunta->setRespuestaAbierta($request->request->get('txtPregunta1'));
                $encuestaPregunta->setTipoPregunta(3);
                $entityManager->persist($encuestaPregunta);
                $entityManager->flush();
            }
            if($request->request->get('txtPregunta2')!=""){

                $encuestaPregunta=new EncuestaPreguntas();
                $encuestaPregunta->setEncuesta($encuesta);
                $encuestaPregunta->setPregunta("¿Tiene alguna sugerencia para que podamos mejorar nuestro servicio legal?");
                $encuestaPregunta->setRespuestaAbierta($request->request->get('txtPregunta2'));
                $encuestaPregunta->setTipoPregunta(3);
                $entityManager->persist($encuestaPregunta);
                $entityManager->flush();
            }

            if($request->request->get('cboSelector3')!=""){

                $encuestaPregunta=new EncuestaPreguntas();
                $encuestaPregunta->setEncuesta($encuesta);
                $encuestaPregunta->setPregunta("¿Cuándo fue la ultima vez que hablo con alguna opreadora de NORMALIZACIÓN?");
                $encuestaPregunta->setNota($request->request->get('cboSelector3'));
                $encuestaPregunta->setTipoPregunta(2);
                $entityManager->persist($encuestaPregunta);
                $entityManager->flush();
            }

            if($request->request->get('cboSelector4')!=""){

                $encuestaPregunta=new EncuestaPreguntas();
                $encuestaPregunta->setEncuesta($encuesta);
                $encuestaPregunta->setPregunta("¿Conforme?");
                $encuestaPregunta->setNota($request->request->get('cboSelector4'));
                $encuestaPregunta->setTipoPregunta(4);
                $entityManager->persist($encuestaPregunta);
                $entityManager->flush();
                if($request->request->get('cboSelector4')=='NO'){
                    $crearTicket = true;
                }
            }

            if($crearTicket){
                return $this->redirectToRoute('app_ticket_new',['id'=>$contrato->getId()]);
            }
            return $this->redirectToRoute('encuesta_index');

        }


        return $this->render('encuesta/new.html.twig', [
            'contrato'=>$contrato,
            'encuesta'=>$encuesta,
            'pagina'=>"Calidad - Encuesta",
        ]);
    }

    

    /**
     * @Route("/{id}/edit", name="encuesta_edit", methods={"GET","POST"})
     */
    public function edit(Request $request,Contrato $contrato, 
                            EncuestaRepository $encuestaRepository, 
                            EncuestaPreguntasRepository $encuestaPreguntasRepository,
                            EstadoEncuestaRepository $estadoEncuestaRepository): Response
    {

        $this->denyAccessUnlessGranted('edit','Encuesta');
        $encuesta=$encuestaRepository->findOneBy(['contrato'=>$contrato,'estado'=>$estadoEncuestaRepository->find(1)]);

        if($request->request->get('hdEncuesta')!=""){
            $entityManager = $this->getDoctrine()->getManager();

            $encuestaRequest = $encuestaRepository->find($request->request->get('hdEncuesta'));
            $tipoSelector = $request->request->get('hdTipoSelector');
            $valor = $request->request->get('valor');
            $i=0;
            $crearTicket = false;
            foreach($request->request->get('hdIdPregunta') as $pregunta){
                $encuestaPregunta = $encuestaPreguntasRepository->find($pregunta);
                if($tipoSelector[$i]==1 || $tipoSelector[$i]==2 || $tipoSelector[$i]==4){
                    $encuestaPregunta->setNota($valor[$i]);
                
                }
                if($tipoSelector[$i]==3){
                    $encuestaPregunta->setRespuestaAbierta($valor[$i]);
                }
                $entityManager->persist($encuestaPregunta);
                $entityManager->flush();
                $i++;
            }

            $encuestaRequest->setObservacion($request->request->get('txtObservacion'));
            $contrato->setObservacionEncuesta($request->request->get('txtObservacion'));
            $contrato->setFechaEncuesta(new DateTime(date("Y-m-d H:i:s")));
            $contrato->setEstadoEncuesta($estadoEncuestaRepository->find($request->request->get("hdEstado")));
            $encuestaRequest->setEstado($estadoEncuestaRepository->find($request->request->get("hdEstado")));

            if($request->request->get("hdEstado")==1){
                $encuesta->setFechaPendiente(new DateTime(date("Y-m-d")));
            }
            if($request->request->get("hdEstado")==2){
                $encuesta->setFechaCierre(new DateTime(date("Y-m-d")));
            }
            $entityManager->persist($encuestaRequest);
            $entityManager->flush();
            $entityManager->persist($contrato);
            $entityManager->flush();

            
            return $this->redirectToRoute('encuesta_index');
        }



        return $this->render('encuesta/edit.html.twig', [
            'contrato'=>$contrato,
            'encuesta'=>$encuesta,
            'pagina'=>"Calidad - Encuesta",
        ]);

    }

    /**
     * @Route("/{id}/view", name="encuesta_view", methods={"GET","POST"})
     */
    public function view(Request $request,Encuesta $encuesta, 
                            EncuestaRepository $encuestaRepository, 
                            EncuestaPreguntasRepository $encuestaPreguntasRepository,
                            EstadoEncuestaRepository $estadoEncuestaRepository): Response
    {

        //$encuesta=$encuestaRepository->findOneBy(['contrato'=>$contrato,'estado'=>$estadoEncuestaRepository->find(1)]);
        $contrato = $encuesta->getContrato();
        return $this->render('encuesta/view.html.twig', [
            'contrato'=>$contrato,
            'encuesta'=>$encuesta,
            'pagina'=>"Calidad - Encuesta",
        ]);
    }
    

    /**
     * @Route("/{id}/{qty}/respuestas", name="encuesta_respuestas", methods={"GET","POST"})
     */
    public function respuestas(FuncionEncuesta $funcionEncuesta, int $qty, FuncionRespuestaRepository $funcionRespuestaRepository): Response
    {

        if($funcionEncuesta->getId()==2){
            $respuestas=$funcionRespuestaRepository->findBy(['funcionEncuesta'=>$funcionEncuesta]);
        }else{
            if($qty>0){
                $respuestas = $funcionRespuestaRepository->findBy(['id'=>[7,8]]);
            }else{
                $respuestas = $funcionRespuestaRepository->findBy(['id'=>[1,2,3]]);
            }
        }

        return $this->render('encuesta/_cboRespuestas.html.twig', [
            'respuestas'=>$respuestas,
            
        ]);
    }

}
