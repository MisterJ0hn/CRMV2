<?php

namespace App\Controller;

use App\Entity\Contrato;
use App\Entity\Importancia;
use App\Entity\ModuloPer;
use App\Entity\Ticket;
use App\Entity\TicketEstado;
use App\Entity\TicketHistorial;
use App\Entity\TicketTipo;
use App\Entity\UsuarioTipo;
use App\Form\TicketType;
use App\Repository\ContratoRepository;
use App\Repository\CuentaRepository;
use App\Repository\EmpresaRepository;
use App\Repository\TicketEstadoRepository;
use App\Repository\TicketHistorialRepository;
use App\Repository\TicketRepository;
use App\Repository\TicketTipoRepository;
use App\Repository\UsuarioCuentaRepository;
use App\Repository\UsuarioRepository;
use App\Repository\UsuarioTipoRepository;
use Doctrine\ORM\EntityRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ticket")
 */
class TicketController extends AbstractController
{
    /**
     * @Route("/", name="app_ticket_index", methods={"GET"})
     */
    public function index(TicketRepository $ticketRepository,
                        PaginatorInterface $paginator,
                        Request $request,
                        UsuarioTipoRepository $usuarioTipoRepository,
                        CuentaRepository $cuentaRepository,
                        UsuarioCuentaRepository $usuarioCuentaRepository                        
                        ): Response
    {
        $this->denyAccessUnlessGranted('view','ticket');
        $user=$this->getUser();

        $pagina=$this->getDoctrine()->getRepository(ModuloPer::class)->findOneByName('ticket',$user->getEmpresaActual());
        
        $perfiles = $usuarioTipoRepository->findBy([],['nombre'=>'Asc']);
        $statues='1';
        $statuesgroup="1,2,3,4";
        $status=null;
        $folio='';
        $compania="";
        $usuarioCuentas = $usuarioCuentaRepository->findBy(['usuario'=>$user->getId()]);
        $i=0;
        foreach($usuarioCuentas as $usuarioCuenta){

            $compania.=$usuarioCuenta->getCuenta()->getId();
            if($i<(count($usuarioCuentas)-1)){
                $compania.=",";
            }
            $i++;
        }
        $companias=null;
        $tipo_fecha=1;
        $origen=0;
        $perfil=0;
        if(null !== $request->query->get('bFolio') && trim($request->query->get('bFolio'))!=''){
            $folio=$request->query->get('bFolio');
            $otros=" (c.folio= $folio or c.agenda=$folio)";

            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');
            $origen=3;
        }else{
           
            if(null !== $request->query->get('bStatus') && trim($request->query->get('bStatus')!='')){
                $status=$request->query->get('bStatus');
                $statues=$status;
                $statuesgroup=$status;
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
                $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
                $dateFin=date('Y-m-d');
            }
            if(null !== $request->query->get('bTipofecha') ){
                $tipo_fecha=$request->query->get('bTipofecha');
            }
            switch($tipo_fecha){
                case 0:
                    $fecha="t.fechaNuevo between '$dateInicio' and '$dateFin 23:59:59'" ;
                    break;
                case 1:
                    $fecha="t.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
                    break;
                case 2:
                    $fecha="t.fechaRespuesta between '$dateInicio' and '$dateFin 23:59:59'" ;
                    break;
                case 3:
                    $fecha="t.fechaCierre between '$dateInicio' and '$dateFin 23:59:59'" ;
                    break;
                default:
                    $fecha="t.fechaNuevo between '$dateInicio' and '$dateFin 23:59:59'" ;
                    break;
            }
            $otros=$fecha;

            if(null !== $request->query->get('bPerfil') && trim($request->query->get('bPerfil')!=0)){
                $perfil=$request->query->get('bPerfil');
                $otros.=" and t.destino = $perfil ";
            }
        }

        switch($user->getUsuarioTipo()->getId()){
            case 3:
                $query=$ticketRepository->findByPers($user->getId(),$user->getEmpresaActual(),$statuesgroup,3,$otros);
                $queryresumen=$ticketRepository->findByPersGroup($user->getId(),$user->getEmpresaActual(),$statuesgroup,null,3,$otros);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;                
            case 1:
            case 8:
                $origen=2;
                $query=$ticketRepository->findByPers($user->getId(),$user->getEmpresaActual(),$statuesgroup,2,$otros);
                $queryresumen=$ticketRepository->findByPersGroup(null,$user->getEmpresaActual(),$statuesgroup,null,2,$otros);
                $companias=$cuentaRepository->findByPers($user->getId(),$user->getEmpresaActual());
                break;
            default:
                
                $query=$ticketRepository->findByPers($user->getId(),$user->getEmpresaActual(),$statuesgroup,$origen,$otros);
                $queryresumen=$ticketRepository->findByPersGroup($user->getId(),$user->getEmpresaActual(),$statuesgroup,null,$origen,$otros);
                $companias=$cuentaRepository->findByPers($user->getId(),$user->getEmpresaActual());
                break;
        }

        $error_toast='';
        if(null !== $request->query->get('error_toast')){
            $error_toast=$request->query->get('error_toast');
        }
        $tickets=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/,
            array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));
        return $this->render('ticket/index.html.twig', [
            'pagina'=>$pagina->getNombre(),
            'tickets' => $tickets,
            'error_toast'=>$error_toast,
            'bFolio'=>$folio,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'status'=>$status,
            'statuesGroup'=>$statuesgroup,
            'resumenes'=>$queryresumen,
            'tipoFecha'=>$tipo_fecha,
            'origen'=>$origen,
            'perfiles'=>$perfiles,
            'bPerfil'=>$perfil,
            'TipoFiltro'=>'Tickets',
        ]);
    }

    /**
     * @Route("/search", name="app_ticket_search", methods={"GET", "POST"})
     */
    public function search(Request $request, ContratoRepository $contratoRepository, TicketRepository $ticketRepository): Response
    {

        $this->denyAccessUnlessGranted('create','ticket');
        $user=$this->getUser();
        $ticket=null;
        $contratos=null;
        $contrato=null;
        $pagina=$this->getDoctrine()->getRepository(ModuloPer::class)->findOneByName('ticket_new',$user->getEmpresaActual());
        

        $folio = $request->request->get('txtFolio');
        $nombrerut = $request->request->get('txtNombreRut');
        
        if($folio!=''){
            $contrato=$contratoRepository->findOneBy(['folio'=>$folio]);
            $ticket=$ticketRepository->findAbierto($folio);

            if($ticket){
                
            }else{
                

                //if($contrato)
                    //return $this->redirectToRoute('app_ticket_new',['id'=>$contrato->getId()]);

            }
        }else{

            if($nombrerut!=''){

                $contratos = $contratoRepository->findNombreRut($nombrerut);
            }
        }
        

        return $this->render('ticket/search.html.twig', [
            'pagina'=>$pagina->getNombre(),
            'tickets'=>$ticket,
            'contratos'=>$contratos,
            'contrato'=>$contrato
           
        ]);
    }


    
    /**
     * @Route("/resumen", name="app_ticket_resumen", methods={"GET","POST"})
     */
    public function resumen(Request $request,$ticketEstado,String $fechainicio, String $fechafin,$compania,$filtro,$totalStatus,$tipoFecha,$origen,$folio,$perfil, TicketRepository $ticketRepository): Response
    {
        $user=$this->getUser();
        $otros="";  
        switch($tipoFecha){
            case 0:
                $otros="t.fechaNuevo between '$fechainicio' and '$fechafin 23:59:59'" ;
                break;
            case 1:
                $otros="t.fechaAsignado between '$fechainicio' and '$fechafin 23:59:59'" ;
                break;
            case 2:
                $otros="t.fechaRespuesta between '$fechainicio' and '$fechafin 23:59:59'" ;
                break;
            case 3:
                $otros="t.fechaCierre between '$fechainicio' and '$fechafin 23:59:59'" ;
                break;
            default:
                $otros="t.fechaNuevo between '$fechainicio' and '$fechafin 23:59:59'" ;
                break;
        }
        //$fecha="a.fechaCarga between '$fechainicio' and '$fechafin 23:59:59'" ;
        $nombre_status="";
        if(null != $ticketEstado){
            $status=$this->getDoctrine()->getRepository(TicketEstado::class)->find($ticketEstado);
            $nombre_status=$status->getNombre();
        }else{
            $ticketEstado=null;
            $nombre_status="Todos";
        }
        if(null !== $perfil && trim($perfil)!=0){
                
                $otros.=" and t.destino = $perfil ";
            }
        //$queryresumen=$agendaRepository->findByAgendGroup(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,null,$fecha);   
        switch($user->getUsuarioTipo()->getId()){
            case 3:
                if ($user->getId()==25 or $user->getId()==24){
                    $queryresumen=$ticketRepository->findByTicketGroup(null,$user->getEmpresaActual(),$ticketEstado,2,$otros,$folio);   
                }else{
                    $queryresumen=$ticketRepository->findByTicketGroup($user->getId(),$user->getEmpresaActual(),$ticketEstado,3,$otros,$folio);   
                }
            break;
            case 1:
            case 8:
            case 13:
                $queryresumen=$ticketRepository->findByTicketGroup($user->getId(),$user->getEmpresaActual(),$ticketEstado,$origen,$otros,$folio);   
            break;
            default:
                $queryresumen=$ticketRepository->findByTicketGroup($user->getId(),$user->getEmpresaActual(),$ticketEstado,$origen,$otros,$folio);   
            break;
        }

        return $this->render('ticket/_resumentickets.html.twig',[
            'tickets'=>$queryresumen,
            'total'=>$totalStatus,
            'nombre_status'=>$nombre_status,
        ]);
    }

    /**
     * @Route("/{id}", name="app_ticket_show", methods={"GET"})
     */
    public function show(Ticket $ticket): Response
    {
        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }

   
    

    /**
     * @Route("/{id}/gestionar", name="app_ticket_gestionar", methods={"GET", "POST"})
     */
    public function gestionar(Request $request, 
                            Ticket $ticket, 
                            TicketRepository $ticketRepository, 
                            TicketEstadoRepository $ticketEstadoRepository,
                            EmpresaRepository $empresaRepository,
                            UsuarioTipoRepository $usuarioTipoRepository,
                            UsuarioRepository $usuarioRepository,
                            TicketHistorialRepository $ticketHistorialRepository): Response
    {
        $user=$this->getUser();
        $rechazado = false;
        $pagina="Folio SAC ".$ticket->getFolioSac();
        $usuarioTipos=$usuarioTipoRepository->findBy([],['nombre'=>'Asc']);
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($ticket->getEstado()->getId()==1){
                $destino= $usuarioTipoRepository->find($request->request->get('cboDestino'));
                $encargado = $usuarioRepository->find($request->request->get('cboEncargado'));
                $estado=$ticketEstadoRepository->find(2);
                $ticket->setDestino($destino);
                $ticket->setEncargado($encargado);
                $ticket->setEstado($estado);
                $ticket->setFechaAsignado(new \DateTime(date('Y-m-d H:i:s')));
                $ticketRepository->add($ticket);

                $ticketHistoria = new TicketHistorial();
                $ticketHistoria->setTicket($ticket);
                $ticketHistoria->setObservacion($request->request->get('txtObservacion'));
                $ticketHistoria->setUsuarioRegistro($encargado);
                $ticketHistoria->setFecha(new \DateTime(date('Y-m-d H:i:s')));
                $ticketHistoria->setEstado($estado);

                $ticketHistorialRepository->add($ticketHistoria);
                $error_toast="Toast.fire({
                    icon: 'success',
                    title: 'Registro Grabado con exito!!'
                  })";
                return $this->redirectToRoute('app_ticket_index', ['error_toast'=>$error_toast], Response::HTTP_SEE_OTHER);
            }
            if($ticket->getEstado()->getId()==2){

                $observacion=$request->request->get('txtObservacion');
                $soloObservacion = $request->request->get("hdSoloObservacion");
                $rechazada = $request->request->get("hdRechazada");
                $ticket->setRespuesta($observacion);
                if($rechazada){
                    $estado=$ticketEstadoRepository->find(2);
                    $ticket->setFechaUltimaGestion(new \DateTime(date('Y-m-d H:i:s')));
                    $ticket->setDestino($ticket->getOrigen()->getUsuarioTipo());
                    $ticket->setEncargado($ticket->getOrigen());
                    $observacion="Rechazado. ".$observacion;
                }else{                
                    if($soloObservacion){
                        $estado=$ticketEstadoRepository->find(2);
                        $ticket->setFechaUltimaGestion(new \DateTime(date('Y-m-d H:i:s')));
                    }else{
                        $estado=$ticketEstadoRepository->find(3);
                        $ticket->setFechaRespuesta(new \DateTime(date('Y-m-d H:i:s')));
                    }
                }
                $ticket->setEstado($estado);
                
                $ticketRepository->add($ticket);

                $ticketHistoria = new TicketHistorial();
                $ticketHistoria->setTicket($ticket);
                $ticketHistoria->setFecha(new \DateTime(date('Y-m-d H:i:s')));
                $ticketHistoria->setObservacion($observacion);
                $ticketHistoria->setUsuarioRegistro($user);
                
                $ticketHistoria->setEstado($estado);

                $ticketHistorialRepository->add($ticketHistoria);
                $error_toast="Toast.fire({
                    icon: 'success',
                    title: 'Registro Grabado con exito!!'
                  })";
                return $this->redirectToRoute('app_ticket_index', ['error_toast'=>$error_toast], Response::HTTP_SEE_OTHER);
            }
            if($ticket->getEstado()->getId()==3){

                $observacion=$request->request->get('txtObservacion');
                $estado=$ticketEstadoRepository->find(4);
                $ticket->setRespuesta($observacion);
                $ticket->setEstado($estado);
                $ticket->setFechaCierre(new \DateTime(date('Y-m-d H:i:s')));
                $ticketRepository->add($ticket);

                $ticketHistoria = new TicketHistorial();
                $ticketHistoria->setTicket($ticket);
                $ticketHistoria->setFecha(new \DateTime(date('Y-m-d H:i:s')));
                $ticketHistoria->setObservacion($observacion);
                $ticketHistoria->setUsuarioRegistro($user);
                
                $ticketHistoria->setEstado($estado);

                $ticketHistorialRepository->add($ticketHistoria);
                $error_toast="Toast.fire({
                    icon: 'success',
                    title: 'Registro Grabado con exito!!'
                  })";
                return $this->redirectToRoute('app_ticket_index', ['error_toast'=>$error_toast], Response::HTTP_SEE_OTHER);
            }
           

        }
        
        if($ticket->getOrigen()->getId()==$ticket->getEncargado()->getId()){
        
            $rechazado = true;
        }
         

        return $this->render('ticket/gestion.html.twig', [
            'ticket' => $ticket,
            'ticketTipo'=>$ticket->getTicketTipo(),
            'contrato'=>$ticket->getContrato(),
            'usuarioTipos'=>$usuarioTipos,
            'pagina'=>$pagina,
            'form' => $form->createView(),
            'rechazado'=>$rechazado
            
        ]);
    }

    /**
     * @Route("/{id}/reasignar", name="app_ticket_reasignar", methods={"GET", "POST"})
     */
    public function reasignar(Request $request, 
                            Ticket $ticket, 
                            TicketRepository $ticketRepository, 
                            TicketEstadoRepository $ticketEstadoRepository,
                            EmpresaRepository $empresaRepository,
                            UsuarioTipoRepository $usuarioTipoRepository,
                            UsuarioRepository $usuarioRepository,
                            TicketHistorialRepository $ticketHistorialRepository): Response
    {
        $user=$this->getUser();

        $this->denyAccessUnlessGranted('create','ticket_reasignar');
        $destino= $usuarioTipoRepository->find($request->request->get('cboDestino'));
        $encargado = $usuarioRepository->find($request->request->get('cboEncargado'));
        $entityManager = $this->getDoctrine()->getManager();
           
        
        $usuarioTipo=$usuarioTipoRepository->find($destino);
        $usuario=$usuarioRepository->find($encargado);
        $ticket->setDestino($usuarioTipo);
        $ticket->setEncargado($usuario);

        $entityManager->persist($ticket);
        $entityManager->flush();

        $ticketHistoria = new TicketHistorial();
        $ticketHistoria->setTicket($ticket);
        $ticketHistoria->setObservacion("Reasignado a ".$encargado->getNombre());
        $ticketHistoria->setUsuarioRegistro($user);
        $ticketHistoria->setFecha(new \DateTime(date('Y-m-d H:i:s')));
        $ticketHistoria->setEstado($ticket->getEstado());

        $entityManager->persist($ticketHistoria);
        $entityManager->flush();

        
        return $this->redirectToRoute('app_ticket_gestionar', ['id'=>$ticket->getId()], Response::HTTP_SEE_OTHER);
    }
    
    /**
     * @Route("/{id}/edit", name="app_ticket_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Ticket $ticket, TicketRepository $ticketRepository): Response
    {
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticketRepository->add($ticket);
            return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ticket/edit.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="app_ticket_delete", methods={"POST"})
     */
    public function delete(Request $request, Ticket $ticket, TicketRepository $ticketRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ticket->getId(), $request->request->get('_token'))) {
            $ticketRepository->remove($ticket);
        }

        return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
    }
    /**
     * @Route("/{id}/new", name="app_ticket_new", methods={"GET", "POST"})
     */
    public function new(Contrato $contrato,Request $request, 
                        TicketRepository $ticketRepository,
                        EmpresaRepository $empresaRepository,
                        TicketEstadoRepository $ticketEstadoRepository,
                        UsuarioTipoRepository $usuarioTipoRepository,
                        UsuarioRepository $usuarioRepository,
                        TicketHistorialRepository $ticketHistorialRepository): Response
    {
        $this->denyAccessUnlessGranted('create','ticket');
        $usuarioTipos=$usuarioTipoRepository->findBy([],['nombre'=>'Asc']);

        $user=$this->getUser();

        $pagina=$this->getDoctrine()->getRepository(ModuloPer::class)->findOneByName('ticket_new',$user->getEmpresaActual());
        if($request->query->get('tipo')==null){
            return $this->redirectToRoute('app_ticket_search', [], Response::HTTP_SEE_OTHER);
        }
        $ticketTipo=$this->getDoctrine()->getRepository(TicketTipo::class)->find($request->query->get('tipo'));
        $ticket = new Ticket();
        $ticket->setEmpresa($empresaRepository->find($user->getEmpresaActual()));
        $ticket->setContrato($contrato);
        $ticket->setEstado($ticketEstadoRepository->find(1));
        $ticket->setFolio(0);
        $ticket->setOrigen($user);
        $ticket->setTicketTipo($ticketTipo);
        $ticket->setFechaNuevo(new \DateTime(date('Y-m-d H:i:s')));
        $form = $this->createForm(TicketType::class, $ticket);
        $form->add("importancia",EntityType::class,[
            'class' => Importancia::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('i');
            },
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ultimoTicket=$ticketRepository->ultimoTicket();
            $folio=0;
            if($ultimoTicket!=null){
                $folio=$ultimoTicket->getFolio()+1;
            }
            $ticket->setFolio($folio);
            $ticket->setFolioSac("S".$ticket->getFolio()."-".$ticket->getContrato()->getFolio());



            $destino= $usuarioTipoRepository->find($request->request->get('cboDestino'));
            $encargado = $usuarioRepository->find($request->request->get('cboEncargado'));
            $estado=$ticketEstadoRepository->find(2);
            $ticket->setDestino($destino);
            $ticket->setEncargado($encargado);
            $ticket->setEstado($estado);
            $ticket->setFechaAsignado(new \DateTime(date('Y-m-d H:i:s')));


            $ticketRepository->add($ticket);

            $ticketHistoria = new TicketHistorial();
            $ticketHistoria->setTicket($ticket);
            $ticketHistoria->setObservacion($ticket->getMotivo());
            $ticketHistoria->setUsuarioRegistro($user);

            $ticketHistorialRepository->add($ticketHistoria);
            return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ticket/new.html.twig', [
            'pagina'=>$pagina->getNombre(),
            'ticket' => $ticket,
            'ticketTipo'=>$ticketTipo,
            'form' => $form->createView(),
            'usuarioTipos'=>$usuarioTipos,
            'contrato'=>$contrato,
        ]);
    }

     /**
     * @Route("/{id}/usuarios", name="app_ticket_show", methods={"GET"})
     */
    public function usuarios(Request $request, Contrato $contrato, UsuarioRepository $usuarioRepository, UsuarioTipoRepository $usuarioTipoRepository ): Response
    {
        $usuario_id=0;

        

        if($request->query->get('perfil_id')==0){
            $usuarios=$usuarioRepository->findBy(['usuarioTipo'=>[2,3,4,5,6,7,10,12,13],'estado'=>1]);
        }else{
            $usuarioTipo=$usuarioTipoRepository->find($request->query->get('perfil_id'));
            $usuarios=$usuarioRepository->findBy(['usuarioTipo'=>$usuarioTipo->getId(),'estado'=>1]);
            switch($usuarioTipo->getId()){
                case 5://agendador
                    $usuario_id=$contrato->getAgenda()->getAgendador()->getId();
                    break;
                case 6://Abogado
                    $usuario_id=$contrato->getAgenda()->getAbogado()->getId();
                    break;
                case 12://cobrador
                    foreach ($contrato->getIdLote()->getUsuarioLotes() as $usuario) {
                        $usuario_id = $usuario->getUsuario()->getId();
                     
                    }
                    break;
                case 7:
                    $usuario_id=$contrato->getTramitador()->getId();
                    break;
            }
        }
        
        return $this->render('ticket/_usuarios.html.twig', [
            'usuarios' => $usuarios,
            "usuario_id"=>$usuario_id 
            
        ]);
    }
}
