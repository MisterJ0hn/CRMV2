<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Agenda;
use App\Entity\Usuario;
use App\Entity\ContratoRol;
use App\Entity\AgendaObservacion;
use App\Entity\Causa;
use App\Entity\Contrato;
use App\Entity\ContratoMee;
use App\Entity\Cuenta;
use App\Entity\Cuota;
use App\Entity\Empresa;
use App\Form\ContratoType;
use App\Repository\AgendaRepository;
use App\Repository\JuzgadoRepository;
use App\Repository\ContratoRepository;
use App\Repository\ContratoRolRepository;
use App\Repository\UsuarioRepository;
use App\Repository\UsuarioTipoRepository;
use App\Repository\AgendaStatusRepository;
use App\Repository\CarteraRepository;
use App\Repository\CausaRepository;
use App\Repository\CiudadRepository;
use App\Repository\ComunaRepository;
use App\Repository\CuentaMateriaRepository;
use App\Repository\SucursalRepository;
use App\Repository\CuentaRepository;
use App\Repository\CuotaRepository;
use App\Repository\ModuloRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\DiasPagoRepository;
use App\Repository\GrupoRepository;
use App\Repository\LotesRepository;
use App\Repository\MateriaRepository;
use App\Repository\MeeRepository;
use App\Repository\ReunionRepository;
use App\Repository\RegionRepository;
use App\Repository\UsuarioCarteraRepository;
use App\Repository\UsuarioCuentaRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use PDOException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
     * @Route("/panel_abogado")
     */
class PanelAbogadoController extends AbstractController
{
    /**
     * @Route("/", name="panel_abogado_index", methods={"GET","POST"})
     */
    public function index(AgendaRepository $agendaRepository,
                        CuentaRepository $cuentaRepository,
                        PaginatorInterface $paginator,
                        UsuarioTipoRepository $usuarioTipoRepository,
                        UsuarioRepository $usuarioRepository,
                        Request $request,
                        ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','panel_abogado');

        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('panel_abogado',$user->getEmpresaActual());
        $filtro=null;
        $compania=null;
        $fecha=null;
        $statues='5';
        $statuesgroup='4,3,5,7,6,8,14,15,12,13';
        $status=null;
        $tipo_fecha=1;
        $abogado=null;
        $folio=null;
        if(null !== $request->query->get('bFolio') && trim($request->query->get('bFolio'))!=''){
            $folio=$request->query->get('bFolio');
            $fecha=" a.id=$folio ";
            $statues=$statuesgroup;
            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');
            
        }else{
            if(null !== $request->query->get('bFiltro') && trim($request->query->get('bFiltro'))!=''){
                $filtro=$request->query->get('bFiltro');
            }
            if(null !== $request->query->get('bCompania')&&$request->query->get('bCompania')!=0){
                $compania=$request->query->get('bCompania');
            }

            if(null !== $request->query->get('bFecha')){
                $aux_fecha=explode(" - ",$request->query->get('bFecha'));
                $dateInicio=$aux_fecha[0];
                $dateFin=$aux_fecha[1];
                $statues=$statuesgroup;
            }else{
            // $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
                $dateInicio=date('Y-m-d');
                $dateFin=date('Y-m-d');

            }
            if(null !== $request->query->get('bTipofecha') ){
                $tipo_fecha=$request->query->get('bTipofecha');
            }
            if(null !== $request->query->get('bAbogado')){
                if($request->query->get('bAbogado')==0){
                    $abogado=null;
                }else{
                    $abogado=$request->query->get('bAbogado');
                }

            }
        
        
            switch($tipo_fecha){
                case 0:
                    $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
                    break;
                case 1:
                    $fecha="a.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
                    break;
                case 2:
                    $fecha="a.fechaContrato between '$dateInicio' and '$dateFin 23:59:59'" ;
                    break;
                default:
                    $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
                    break;
            }

        // $fecha="a.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
            
            if(null !== $request->query->get('bStatus') && trim($request->query->get('bStatus')!='')){
                $status=$request->query->get('bStatus');
                $statues=$status;
                $statuesgroup=$status;
            }
        }
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 4:
            case 1:
                $abogados=$usuarioRepository->findBy(['usuarioTipo'=>$usuarioTipoRepository->find(6),'estado'=>1]);

                $query=$agendaRepository->findByPers($abogado,$user->getEmpresaActual(),$compania,$statues,$filtro,1,$fecha);
                $companias=$cuentaRepository->findByPers($abogado,$user->getEmpresaActual());
                $queryresumen=$agendaRepository->findByPersGroup($abogado,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,1,$fecha);
                
            break;
            default:
                $abogados=$usuarioRepository->findBy(['id'=>$user->getId()]);

                $query=$agendaRepository->findByPers($user->getId(),null,$compania,$statues,$filtro,1,$fecha);
                $companias=$cuentaRepository->findByPers($user->getId());
                $queryresumen=$agendaRepository->findByPersGroup($user->getId(),null,$compania,$statuesgroup,$filtro,1,$fecha);
            break;
        }

        
        $agendas=$paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /*page number*/
        20 /*limit per page*/,
        array('defaultSortFieldName' => 'fechaAsignado', 'defaultSortDirection' => 'asc'));

        return $this->render('panel_abogado/index.html.twig', [
            'agendas' => $agendas,
            'pagina'=>$pagina->getNombre(),
            'bFiltro'=>$filtro,
            'bFolio'=>$folio,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'resumenes'=>$queryresumen,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'status'=>$status,
            'statuesGroup'=>$statuesgroup,
            'tipoFecha'=>$tipo_fecha,
            'abogados'=>$abogados,
            'bAbogado'=>$abogado,
            'TipoFiltro'=>'Panel_abogado'
        ]);
    }
    /**
     * @Route("/new_rol", name="panel_abogado_new_rol", methods={"GET","POST"})
     */
    public function newRol(Request $request,
                            JuzgadoRepository $juzgadoRepository,
                            ContratoRolRepository $contratoRolRepository,
                            ModuloPerRepository $moduloPerRepository): Response
    {
        
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('panel_abogado',$user->getEmpresaActual());
        $contrato_rol = new ContratoRol();
        $abogado=$this->getDoctrine()->getRepository(Usuario::class)->find($user->getId());
        $contrato_rol->setAbogado($abogado);

        if(isset($_GET['nombre'])){

            $contrato_rol->setNombreRol($_GET['nombre']);
            $contrato_rol->setInstitucionAcreedora($_GET['institucion']);
            $contrato_rol->setJuzgado($juzgadoRepository->find($_GET['juzgado']));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contrato_rol);
            $entityManager->flush();

        }
        return $this->render('panel_abogado/contratoRoles.html.twig', [
            'pagina'=>$pagina->getNombre(),
            'contrato_rols' => $contratoRolRepository->findByTemporal($abogado->getId()),
           
        ]);
    }

    /**
     * @Route("/reasignar", name="panel_abogado_reasignar", methods={"GET","POST"})
     */
    public function reasignar(Request $request,UsuarioRepository $usuarioRepository,AgendaRepository $agendaRepository):Response
    {
        $user=$this->getUser();
        $agenda_id=$request->query->get('agenda');
        $agenda=$agendaRepository->find($agenda_id);
        $empresa=$this->getDoctrine()->getRepository(Empresa::class)->find($user->getEmpresaActual());
        return $this->render('panel_abogado/reasignar.html.twig', [
            'cuentas'=>$empresa->getCuentas(),
            'agenda'=> $agenda,     
        ]);
    }

    /**
     * @Route("/{id}/tramitadores", name="panel_abogado_tramitadores", methods={"GET","POST"})
     */
    public function tramitadores(Cuenta $cuenta, Request $request,UsuarioRepository $usuarioRepository): Response
    {
        
        return $this->render('panel_abogado/tramitadores.html.twig', [
            'tramitadores'=>$usuarioRepository->findByCuenta($cuenta->getId(),['usuarioTipo'=>7,'estado'=>1]),
        ]);
    }
    /**
     * @Route("/{id}/sucursales", name="panel_abogado_sucursales", methods={"GET","POST"})
     */
    public function sucursales(Cuenta $cuenta, Request $request,SucursalRepository $sucursalRepository): Response
    {
        
        return $this->render('panel_abogado/sucursales.html.twig', [
            'sucursales'=>$sucursalRepository->findBy(['cuenta'=>$cuenta->getId()]),
        ]);
    }
    /**
     * @Route("/{id}", name="panel_abogado_new", methods={"GET","POST"})
     */
    public function new(Agenda $agenda,
                        AgendaRepository $agendaRepository,
                        AgendaStatusRepository $agendaStatusRepository,
                        CuentaRepository $cuentaRepository,
                        UsuarioRepository $usuarioRepository,
                        ReunionRepository $reunionRepository,
                        Request $request,
                        ModuloPerRepository $moduloPerRepository,
                        MateriaRepository $materiaRepository): Response
    {
        $this->denyAccessUnlessGranted('create','panel_abogado');

        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('panel_abogado',$user->getEmpresaActual());
        $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());

        if(null != $request->request->get('chkStatus')){
            $agenda->setStatus($agendaStatusRepository->find($request->request->get('chkStatus')));
            if(null !== $request->request->get('cboAbogado')){
                $agenda->setAbogado($usuarioRepository->find($request->request->get('cboAbogado')));
            }
            if(null !== $request->request->get('txtCiudad')){
                $agenda->setCiudadCliente($request->request->get('txtCiudad'));
            }
            if(null !== $request->request->get('txtFechaAgendamiento')){
                $agenda->setFechaAsignado(new \DateTime($request->request->get('txtFechaAgendamiento')." ".$request->request->get('cboHoras').":00"));
            }
            if(null !== $request->request->get('txtMonto')){
                $agenda->setMonto($request->request->get('txtMonto'));
            }
            if(null !== $request->request->get('txtPagoActual')){
                $agenda->setPagoActual($request->request->get('txtPagoActual'));
            }
            if(null !== $request->request->get('cboReunion')){
                $agenda->setReunion($reunionRepository->find($request->request->get('cboReunion')));
            }
            $entityManager = $this->getDoctrine()->getManager();

            

            $observacion=new AgendaObservacion();
            $observacion->setAgenda($agenda);
            $observacion->setUsuarioRegistro($usuarioRepository->find($user->getId()));
            $observacion->setStatus($agendaStatusRepository->find($request->request->get('chkStatus')));
            $observacion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
            $observacion->setObservacion($request->request->get('txtObservacion'));
           
            $entityManager->persist($observacion);
            $entityManager->flush();
            $entityManager->persist($agenda);
            $entityManager->flush();
            return $this->redirectToRoute('panel_abogado_index');
        }

        
        $estados=$agenda->getAbogado()->getUsuarioTipo()->getStatues();

    
        return $this->render('panel_abogado/new.html.twig', [
            'agenda'=>$agenda,
            'pagina'=>$pagina->getNombre().' | Gestionar',
            'companias'=>$companias,
            'statues'=>$agendaStatusRepository->findBy(['id'=>$agenda->getAbogado()->getUsuarioTipo()->getStatues()],['orden'=>'asc']),
           
        ]);

    }
    /**
     * @Route("/{id}/del_rol", name="panel_abogado_del_rol",  methods={"DELETE"})
     */
    public function delRol(ContratoRol $contratoRol,Request $request,JuzgadoRepository $juzgadoRepository,ContratoRolRepository $contratoRolRepository): Response
    {
        
        $user=$this->getUser();

        
        $abogado=$this->getDoctrine()->getRepository(Usuario::class)->find($user);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($contratoRol);
            $entityManager->flush();

        
        return $this->render('panel_abogado/contratoRoles.html.twig', [
            'contrato_rols' => $contratoRolRepository->findByTemporal($abogado->getId()),
           
        ]);
    }
    /**
     * @Route("/{id}/elimina", name="panel_abogado_elimina", methods={"GET","POST"})
     */
    public function elimina(Agenda $agenda,Request $request,CausaRepository $causaRepository){
        $entityManager = $this->getDoctrine()->getManager();

        $causas = $causaRepository->findBy(['agenda'=>$agenda]);
        foreach ($causas as $causa) {
            $entityManager->remove($causa);
            $entityManager->flush();
        }

        
        return $this->redirectToRoute('panel_abogado_index');
    }
    /**
     * @Route("/{id}/contrata", name="panel_abogado_contrata", methods={"GET","POST"})
     */
    public function contrata(Agenda $agenda,Request $request,
                            AgendaStatusRepository  $agendaStatusRepository,
                            JuzgadoRepository $juzgadoRepository,
                            SucursalRepository $sucursalRepository,
                            DiasPagoRepository $diasPagoRepository,
                            UsuarioRepository $usuarioRepository,
                            ContratoRepository $contratoRepository,
                            RegionRepository $regionRepository,
                            ComunaRepository $comunaRepository,
                            CiudadRepository $ciudadRepository,
                            ReunionRepository $reunionRepository,
                            LotesRepository $lotesRepository,
                            CuentaMateriaRepository $cuentaMateriaRepository,
                            CarteraRepository $carteraRepository,
                            UsuarioCarteraRepository $usuarioCarteraRepository,
                            GrupoRepository $grupoRepository,
                            CuotaRepository $cuotaRepository
                            ):Response
    {
        $this->denyAccessUnlessGranted('create','panel_abogado');

        $user=$this->getUser();
        $juzgados=$juzgadoRepository->findAll();
        if(null != $request->request->get('chkStatus')){
            $agenda->setStatus($agendaStatusRepository->find($request->request->get('chkStatus')));
            if(null !== $request->request->get('cboAbogado')){
                $agenda->setAbogado($usuarioRepository->find($request->request->get('cboAbogado')));
            }
            if(null !== $request->request->get('txtCiudad')){
                $agenda->setCiudadCliente($request->request->get('txtCiudad'));
            }
            if(null !== $request->request->get('txtFechaAgendamiento')){
                $agenda->setFechaAsignado(new \DateTime($request->request->get('txtFechaAgendamiento')." ".$request->request->get('cboHoras').":00"));
            }
            if(null !== $request->request->get('txtMonto')){
                $agenda->setMonto($request->request->get('txtMonto'));
            }
            if(null !== $request->request->get('txtPagoActual')){
                $agenda->setPagoActual($request->request->get('txtPagoActual'));
            }
            if(null !== $request->request->get('cboReunion')){
                $agenda->setReunion($reunionRepository->find($request->request->get('cboReunion')));
            }
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($agenda);
            $entityManager->flush();

            
            return $this->redirectToRoute('panel_abogado_index');
        }

        $contrato=$contratoRepository->findOneBy(['agenda'=>$agenda->getId()]);
        if(null == $contrato){
            
            $contrato=new Contrato();
            $contrato->setAgenda($agenda);
            $contrato->setNombre($agenda->getNombreCliente());
            $contrato->setTelefono($agenda->getTelefonoCliente());
            $contrato->setEmail($agenda->getEmailCliente());
            $contrato->setRut($agenda->getRutCliente());
            $contrato->setTelefonoRecado($agenda->getTelefonoRecadoCliente());
            $contrato->setReunion($agenda->getReunion());
            $contrato->setCarteraOrden(0);
            if(is_null($agenda->getCiudadCliente())){
                $contrato->setCiudad(' ');
            }else{
                $contrato->setCiudad($agenda->getCiudadCliente());
            }
            $contrato->setCuotas(1);
            $contrato->setMontoNivelDeuda($agenda->getMonto()); 
            $contrato->setPagoActual($agenda->getPagoActual()); 
        }
        //$contrato->setCiudad($agenda->getCiudadCliente());
        $contrato->setFechaPrimeraCuota(new \DateTime(date('Y-m-d')));
        $contrato->setVigencia($agenda->getCuenta()->getVigenciaContratos());
        $form = $this->createForm(ContratoType::class, $contrato, [
            'action' =>$this->generateUrl('panel_abogado_contrata',['id'=>$agenda->getId()])
        ]);
        $form->add('fechaPrimeraCuota',DateType::class,array('widget'=>'single_text','html5'=>false));
        
        $form->add('vigencia');
        $form->add('pagoActual');
        $form->add('isIncorporacion');
        $form->add('cuotas', ChoiceType::class,[
            'choices'=>[
                0,
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                8,
                9,
                10,
                11,
                12,
                13,
                14,
                15,
                16,
                17,
                18,
                19,
                20,
                21,
                22,
                23,
                24,
            ]
            ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            //$this->getDoctrine()->getManager()->flush();
            $agenda->setStatus($agendaStatusRepository->find('7'));
            $contrato->setDiaPago($request->request->get('chkDiasPago'));
            $contrato->setFechaCreacion(new \DateTime(date("Y-m-d H:i:s")));
            $contrato->setSucursal($sucursalRepository->find($request->request->get('cboSucursal')));
            //$contrato->setTramitador($usuarioRepository->find($request->request->get('cboTramitador')));
            
            
            $contrato->setFechaPrimerPago(new \DateTime(date($request->request->get('txtFechaPago')."-1 00:00:00 ")));
            

            if($contrato->getIsIncorporacion()){
                $contrato->setFechaPrimeraCuota(new \DateTime($request->request->get('txtFechaIncorporacion')));
                //$contrato->setFechaPrimerPago(new \DateTime(date("Y-m-d",strtotime($request->request->get('txtFechaPago')."-1 00:00:00 +1 month"))));
           
            }


            $contrato->setCregion($regionRepository->find($request->request->get('cboRegion')));
            $contrato->setCciudad($ciudadRepository->find($request->request->get('cboCiudad')));
            $contrato->setCcomuna($comunaRepository->find($request->request->get('cboComuna')));
            $contrato->setSexo($request->request->get('cboSexo'));

            $entityManager = $this->getDoctrine()->getManager();

            
            $entityManager->persist($contrato);
            $entityManager->flush();

            $agenda->setNombreCliente($contrato->getNombre());
            $agenda->setTelefonoCliente($contrato->getTelefono());
            $agenda->setEmailCliente($contrato->getEmail());
            $agenda->setFechaContrato($contrato->getFechaCreacion());

            $entityManager->persist($agenda);
            $entityManager->flush();
           
            $observacion=new AgendaObservacion();
            $observacion->setAgenda($agenda);
            $observacion->setUsuarioRegistro($usuarioRepository->find($user->getId()));
            $observacion->setStatus($agendaStatusRepository->find(7));
            $observacion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
            $observacion->setObservacion('Contrato '.$agenda->getReunion()->getNombre());
           
            $entityManager->persist($observacion);
            $entityManager->flush();
            
            $contrato->setFolio($contrato->getId());

            $lote=$lotesRepository->findPrimerDisponible();

            if(null == $lote){
                //si no hay lotes para utilizar, se setean en false todos para poder utilizar...
                $lotes=$lotesRepository->findBy(['empresa'=>$user->getEmpresaActual(),'estado'=>true]);
                foreach($lotes as $lote){
                    $lote->setIsUtilizado(false);
                    $entityManager->persist($lote);
                    $entityManager->flush();
                }
                $lote=$lotesRepository->findPrimerDisponible();

                $lote->setIsUtilizado(true);
                $entityManager->persist($lote);
                $entityManager->flush();
            }else{
                $lote->setIsUtilizado(true);
                $entityManager->persist($lote);
                $entityManager->flush();
            }
            $contrato->setIdLote($lote);


            $grupo=$grupoRepository->findPrimerDisponible();

            if(null == $grupo){
                //si no hay lotes para utilizar, se setean en false todos para poder utilizar...
                $grupos=$grupoRepository->findBy(['estado'=>true]);
                foreach($grupos as $grupo){
                    $grupo->setUtilizado(false);
                    $entityManager->persist($grupo);
                    $entityManager->flush();
                }
                $grupo=$grupoRepository->findPrimerDisponible();

                $grupo->setUtilizado(true);
                $entityManager->persist($grupo);
                $entityManager->flush();
            }else{
                $grupo->setUtilizado(true);
                $entityManager->persist($grupo);
                $entityManager->flush();
            }
            $contrato->setGrupo($grupo);

            $materias = $contrato->getAgenda()->getCuenta()->getCuentaMaterias();
            $materia_id=0;
            foreach ($materias as $materia) {
                $materia_id=$materia->getMateria()->getId();
            }
            $cartera=$carteraRepository->findPrimerDisponible($materia_id,$agenda->getCuenta()->getId());

            if(null == $cartera){
                //si no hay carteras para utilizar, se setean en false todos para poder utilizar...
                //$lotes=$lotesRepository->findBy(['empresa'=>$user->getEmpresaActual(),'estado'=>true]);
                #$carteras=$carteraRepository->findBy(['materia'=>$materia_id,'estado'=>true]);
                $carteras=$carteraRepository->findCarterasDisponibles($materia_id,$agenda->getCuenta()->getId());
                foreach($carteras as $_cartera){
                    $_cartera->setUtilizado(false);
                    $entityManager->persist($_cartera);
                    $entityManager->flush();
                }
                //Buscamos nuevamente el primer disponible.
                $cartera=$carteraRepository->findPrimerDisponible($materia_id,$agenda->getCuenta()->getId());
                if($cartera  != null){
                    $cartera->setUtilizado(true);
                    $entityManager->persist($cartera);
                    $entityManager->flush();
                }
                
            }else{
                $cartera->setUtilizado(true);
                $entityManager->persist($cartera);
                $entityManager->flush();
            }

            if($cartera != null){
                $usuarioCarteras=$usuarioCarteraRepository->findBy(['cartera'=>$cartera]);
                foreach ($usuarioCarteras as $usuarioCartera) {
                    //$usuarioCuenta=$usuarioCuentaRepository->findOneBy(['usuario'=>$usuarioCartera->getUsuario(),'cuenta'=>$agenda->getCuenta()]);
                    //$contrato->setTramitador($usuarioCuenta->getUsuario());
                    $contrato->setTramitador($usuarioCartera->getUsuario());
                }
                $contrato->setCartera($cartera);
                $contrato->setCarteraOrden($cartera->getNombre());
                $entityManager->persist($contrato);
                $entityManager->flush();
            }

            $cuotasGrabadasPreviamente = $cuotaRepository->findBy(['contrato'=>$contrato]);
            if($cuotasGrabadasPreviamente==null){
                $countCuotas=$contrato->getCuotas();
                $numeroCuota=1;
                $fechaPrimerPago=$contrato->getFechaPrimerPago();
                $diaPago=$contrato->getDiaPago();
                $sumames=0;
                if($contrato->getIsAbono()==true || $contrato->getIsTotal()==true){
                    $cuota=new Cuota();

                    $cuota->setContrato($contrato);
                    $cuota->setNumero($numeroCuota);
                    $cuota->setFechaPago($contrato->getFechaPrimeraCuota());
                    $cuota->setMonto($contrato->getPrimeraCuota());

                    

                    $entityManager->persist($cuota);
                    $entityManager->flush();
                    $numeroCuota++;

                    $contrato->setProximoVencimiento($contrato->getFechaPrimeraCuota());
                    $entityManager->persist($contrato);
                    $entityManager->flush();
                }

                if($contrato->getIsIncorporacion()==true){

                    
                    $contrato->setFechaPrimeraCuota(new \DateTime($request->request->get('txtFechaIncorporacion')));
                
                    $cuota=new Cuota();

                    $cuota->setContrato($contrato);
                    $cuota->setNumero($numeroCuota);
                    $cuota->setFechaPago($contrato->getFechaPrimeraCuota());
                    $cuota->setMonto($contrato->getPrimeraCuota());
                    $entityManager->persist($cuota);
                    $entityManager->flush();
                    $numeroCuota++;
                    $contrato->setProximoVencimiento($contrato->getFechaPrimeraCuota());
                    $entityManager->persist($contrato);
                    $entityManager->flush();  
                }
                $primerPago=date("Y-m-".$diaPago,strtotime($fechaPrimerPago->format('Y-m-d')));
                if(date("n",strtotime($fechaPrimerPago->format('Y-m-d')))==2){
                    if($diaPago==30)
                        $primerPago=date("Y-m-28",strtotime($fechaPrimerPago->format('Y-m-d')));
                }
                $timePrimerPago=strtotime($primerPago);
                $timeFechaActual=strtotime(date("Y-m-d"));
                if($timeFechaActual>=$timePrimerPago){
                    $sumames=1;
                }else{
                    if($contrato->getIsIncorporacion()==true){
                        if(date("m",strtotime($fechaPrimerPago->format('Y-m-d')))==date("m")){
                            $sumames=1;
                        }
                    }
                }

                if($contrato->getValorCuota()!=0){
                    for($i=0;$i<$countCuotas;$i++){
                        $cuota=new Cuota();
                        $i_aux=$i;
                        $cuota->setContrato($contrato);
                        $cuota->setNumero($numeroCuota);
                        $ts = mktime(0, 0, 0, date('m',$timePrimerPago) + $sumames+$i_aux, 1,date('Y',$timePrimerPago));
                        $dia=$diaPago;
                        if(date("n",$ts)==2){
                            if($dia==30){
                                $dia=date("d",mktime(0,0,0,date('m',$timePrimerPago)+ $sumames+$i_aux+1,1,date('Y',$timePrimerPago))-24);
                            }
                        }
                        $fechaCuota=date("Y-m-d", mktime(0,0,0,date('m',$timePrimerPago) + $sumames+$i_aux,$dia,date('Y',$timePrimerPago)));
                        $cuota->setFechaPago(new \DateTime($fechaCuota));
                        $cuota->setMonto($contrato->getValorCuota());
                        if($numeroCuota==1 && $contrato->getIsIncorporacion()==false && $contrato->getIsAbono()==false && $contrato->getIsTotal()==false){
                            $contrato->setProximoVencimiento(new \DateTime($fechaCuota));
                            $entityManager->persist($contrato);
                            $entityManager->flush();
                        }
                        $entityManager->persist($cuota);
                        $entityManager->flush();
                        $numeroCuota++;
                    }
                }
            }
            return $this->redirectToRoute('contrato_pdf',['id'=>$contrato->getId()]);
           // return $this->redirectToRoute('contrato_finalizar',['id'=>$contrato->getId()]);
        }
        return $this->render('panel_abogado/contrata.html.twig',[
            'agenda'=>$agenda,
            'contrato'=>$contrato,
            'juzgados'=>$juzgados,
            'tramitadores'=>$usuarioRepository->findByCuenta($agenda->getCuenta()->getId(),['usuarioTipo'=>7,'estado'=>1]),
            'form'=>$form->createView(),
            'diasPagos'=>$diasPagoRepository->findAll(),
            'sucursales'=>$sucursalRepository->findBy(['cuenta'=>$agenda->getCuenta()->getId()]),
            'regiones'=>$regionRepository->findAll(),
            'cuenta_materias'=>$cuentaMateriaRepository->findBy(['cuenta'=>$agenda->getCuenta(),'estado'=>1]),
        ] );
    }
    /**
     * @Route("/{id}/no_contrata", name="panel_abogado_no_contrata", methods={"GET","POST"})
     */
    public function noContrata(Agenda $agenda,Request $request,
                    AgendaStatusRepository  $agendaStatusRepository,
                    JuzgadoRepository $juzgadoRepository,
                    ContratoRolRepository $contratoRolRepository,
                    UsuarioRepository $usuarioRepository,
                    SucursalRepository $sucursalRepository): Response
    {
        $this->denyAccessUnlessGranted('create','panel_abogado');

        $user=$this->getUser();
        
        if(null !== $request->request->get('status')){
            $agenda->setStatus($agendaStatusRepository->find($request->request->get('status')));
           
        }
        if(null !==$request->request->get('hdNoContrata')){
            $agenda->setStatus($agendaStatusRepository->find($request->request->get('hdNoContrata')));
           // $agenda->setObservacion($agenda->getObservacion()."<hr>".$request->request->get('txtObservacion'));
            $entityManager = $this->getDoctrine()->getManager();
            $observacion=new AgendaObservacion();
            $observacion->setAgenda($agenda);
            $observacion->setUsuarioRegistro($usuarioRepository->find($user->getId()));
            $observacion->setStatus($agendaStatusRepository->find($request->request->get('hdNoContrata')));
            $observacion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
            $observacion->setObservacion($request->request->get('txtObservacion'));
           // $agenda->setObservacion("");
            $entityManager->persist($observacion);
            $entityManager->flush();

            switch($request->request->get('hdNoContrata')){
                case 6:
                    switch($user->getUsuarioTipo()->getId()){
                        case 3:
                        case 1:
                        case 4:
                        case 8:
                            $agenda->setAbogado(null);
                            break;
                    }
                break;
            }

            
            $entityManager->persist($agenda);
            $entityManager->flush();
            return $this->redirectToRoute('panel_abogado_index');
        }

        return $this->render('panel_abogado/no_contrata.html.twig', [
            'agenda'=>$agenda,
            'status'=>$_GET['status']
        ]);
    }
    /**
     * @Route("/{id}/compania", name="panel_abogado_compania", methods={"GET","POST"})
     */
    public function compania(Agenda $agenda,Request $request,
                   CuentaRepository  $cuentaRepository)
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $compania=$request->query->get('compania');
        $agenda->setCuenta($cuentaRepository->find($compania));
        $entityManager->persist($agenda);
        $entityManager->flush();

        try{
            foreach($agenda->getCausas() as $causa){
                $entityManager->remove($causa);
                $entityManager->flush();
            }    
        }catch(PDOException $e){
            
        }
        
        return $this->render('panel_abogado/ok.html.twig');

    }

    public function validarCuota($idContrato,$numeroCuota){
        $entityManager = $this->getDoctrine()->getManager();

        $cuota = $entityManager->getRepository(Cuota::class)->findOneBy(['contrato'=>$idContrato,'numero'=>$numeroCuota]);

        if($cuota!=null){
            return true;
        }else{
            return false;
        }
    }
     
   

}
