<?php

namespace App\Controller;

use App\Entity\Configuracion;
use App\Entity\Contrato;
use App\Entity\ContratoHistoricoSuscripcion;
use App\Entity\Pago;
use App\Entity\PagoCuotas;
use App\Entity\VirtualPosLog;
use App\Form\PlanType;
use App\Form\VirtualPosCrearSuscripcionType;
use App\Repository\ConfiguracionRepository;
use App\Repository\ContratoRepository;
use App\Repository\CuentaCorrienteRepository;
use App\Repository\CuotaRepository;
use App\Repository\PagoCanalRepository;
use App\Repository\PagoTipoRepository;
use App\Repository\UsuarioRepository;
use App\Service\VirtualPos;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/suscripcion")
 */
class SuscripcionController extends AbstractController
{
    /**
     * @Route("/", name="suscripcion_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('suscripcion/index.html.twig', [
            'controller_name' => 'SuscripcionController',
        ]);
    }
    /**
     * @Route("/crear_plan",name="subscripcion_crear_plan",methods={"GET","POST"})
     */
    public function crearPlan(Request $request, ConfiguracionRepository $configuracionRepository): Response
    {
        $error="";
        $form = $this->createForm(PlanType::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $configuracion=$configuracionRepository->find(1);
            $virtualPos = new VirtualPos($configuracion->getVirtualPosApiKey(),
                                    $configuracion->getVirtualPosSecretKey(),
                                    $configuracion->getVirtualPosPlan(),
                                    $configuracion->getVirtualPosUrl());
            $virtualPosLog = new VirtualPosLog();
            $entityManager = $this->getDoctrine()->getManager();
            $virtualPosLog->setRequest("-");
            try{

                $plan = $virtualPos->crearPlan();

                $configuracion->setVirtualPosPlan($plan["id"]);

                $virtualPosLog->setExito(1);
                $virtualPosLog->setFechaRegistro(new \DateTime(date("Y-m-d")));
                $virtualPosLog->setResponse(json_encode($plan));
                
                $entityManager->persist($virtualPosLog);

                $entityManager->flush();

            }catch(Exception $ex){
                $virtualPosLog->setExito(0);
                $virtualPosLog->setFechaRegistro(new \DateTime(date("Y-m-d")));
                $virtualPosLog->setResponse($ex->getMessage());
                $entityManager->persist($virtualPosLog);
                $entityManager->flush();
                $error='<div class="alert alert-danger" role="alert">
                        <strong>Error</strong><br><p>'.$ex->getMessage().'</p>
                        </div>';
            }
        }
        return $this->render('suscripcion/crearPlan.html.twig', [
            'controller_name' => 'SuscripcionController',
            'error'=>$error,
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/registrar_pago",name="subscripcion_registrar_pago",methods={"POST"})
     */
    public function registrarPago(Request $request, 
                                PagoCanalRepository $pagoCanalRepository, 
                                PagoTipoRepository $pagoTipoRepository,
                                CuotaRepository $cuotaRepository,
                                UsuarioRepository $usuarioRepository,
                                CuentaCorrienteRepository $cuentaCorrienteRepository): JsonResponse
    {
        // Método HTTP

        file_put_contents(
            $this->getParameter('kernel.project_dir') . '/var/log/registrarPago.log',
            print_r([
                'method' => $request->getMethod(),
                'query' => $request->query->all(),
                'request' => $request->request->all(),
                'headers' => $request->headers->all(),
                'raw' => $request->getContent(),
            ], true),
            FILE_APPEND
        );


        $entityManager = $this->getDoctrine()->getManager();
        $response = json_decode($request->getContent(),true);
        
        try{
            $charge_id=$response['id'];
            $orden_id="1";//$response["payment"]["order"]["uuid"];
           
            $cuota=$cuotaRepository->findOneBy(["invoiceId"=>$charge_id]);
            $contrato = $cuota->getContrato();
            $pago = new Pago();
            $pago->setBoleta("");
            $pago->setComprobante($orden_id);
            $pago->setMonto($response["payment"]["order"]["amount"]);
            $pago->setFechaPago(new \DateTime($response["payment"]["order"]["authorized_at"]));
            $pago->setPagoCanal($pagoCanalRepository->find(6));
            $pago->setPagoTipo($pagoTipoRepository->find(2));
            $pago->setContrato($contrato);
            $pago->setUsuarioRegistro($usuarioRepository->find(1));
            $pago->setCuentaCorriente($cuentaCorrienteRepository->find(4));
            $pago->setHoraPago(new \DateTime(date("H:i:s")));
            $pago->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
            $entityManager->persist($pago);

            $pagoCuota = new PagoCuotas();
            $pagoCuota->setCuota($cuota);
            $pagoCuota->setPago($pago);
            $pagoCuota->setMonto($pago->getMonto());
            $entityManager->persist($pagoCuota);


            $cuota->setPagado($pago->getMonto());
            $entityManager->persist($cuota);
            $entityManager->flush();

            $historicoSuscripcion = new ContratoHistoricoSuscripcion();
            $historicoSuscripcion->setContrato($contrato);
            $historicoSuscripcion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
            $historicoSuscripcion->setExito(true);
            $historicoSuscripcion->setObservacion("<p>Pago realizado con éxito</p><p>detalle del pago:</p>
                                                    <ul>
                                                        <li>número cuota:".$cuota->getNumero()."</li>
                                                        <li>Monto Pago: $".$pago->getMonto()."</li>
                                                        <li>Fecha Pago: ".$pago->getFechaPago()->format("Y-m-d")."</li>
                                                    </ul>");
            
            
            $historicoSuscripcion->setSuscripcionId($contrato->getSuscripcionId());
    
            $entityManager->persist($historicoSuscripcion);
            $entityManager->flush();

            $primeraCuotaVigente=$cuotaRepository->findOneByPrimeraVigente($contrato->getId());

            if($primeraCuotaVigente != null ){
                $contrato->setProximoVencimiento($primeraCuotaVigente->getFechaPago());
                $entityManager->persist($contrato);
                $entityManager->flush();
            }

            return $this->json(["status"=>"OK"],200);
        }catch(\Exception $e){
            return $this->json(["status"=>"NoOK","message"=>$e->getMessage()],400);
        }
    }

    /**
     * @Route("/registrar_pago_fallido",name="subscripcion_registrar_pago_fallido",methods={"GET","POST"})
     */
    public function registrarPagoFallido(Request $request,LoggerInterface $logger): JsonResponse
    {
        
         file_put_contents(
            $this->getParameter('kernel.project_dir') . '/registrarPagoFallido.log',
            print_r([
                'method' => $request->getMethod(),
                'query' => $request->query->all(),
                'request' => $request->request->all(),
                'headers' => $request->headers->all(),
                'raw' => $request->getContent(),
            ], true),
            FILE_APPEND
        );

        return $this->json(["status"=>"OK"],200);
    }
    /**
     * @Route("/{uuid}", name="suscripcion_new",methods={"GET","POST"})
     */
    public function new(Request $request, 
                        $uuid, ContratoRepository $contratoRepository,
                        ConfiguracionRepository $configuracionRepository,
                        CuotaRepository $cuotaRepository): Response
    {

        $contrato = $contratoRepository->findOneBy(["sesionSuscripcion"=>$uuid]);
        $entityManager = $this->getDoctrine()->getManager();
        
        $error="";
        $redirect_js="";
        $form=$this->createForm(VirtualPosCrearSuscripcionType::class);
        $form->add("nombre",null,["required"=>false
        ]);
        $form->handleRequest($request);
        if(null !== $contrato and $contrato->getSesionSuscripcionActiva()==1){
            
            if($form->isSubmitted() && $form->isValid()){
                $configuracion = $configuracionRepository->find(1);
            
                $cuotas = $cuotaRepository->findBy(["contrato"=>$contrato, "anular"=>null],["fechaPago"=>"ASC"]);

                $url_return = $this->getParameter("url_web")."/suscripcion/".$contrato->getSesionSuscripcion()."/validar";

                try{
                    if($contrato->getSuscripcionUrl()==null){
                        $url_suscripcion=$this->inscribir($contrato,$configuracion,$cuotas,$url_return);
                        //redireccionamos a la pagina de virtual pos
                        return new RedirectResponse($url_suscripcion);
                        /*$redirect_js='setTimeout(() => {
                                    <!--window.location.href="'.$response["url_redirect"].'";-->
                                    }, "1000");';*/
                    }else{
                        //si existe url de suscripcion, es po que ya se intento realizar la inscripcion.
                        $estado = $this->consultarSuscripcion($contrato->getSuscripcionId(),$configuracion);
                       
                        switch($estado){
                            case "ACTIVA":
                                throw new Exception("La suscripción se encuentra activa");
                                break;
                            case "SUSCRIBIENDO":
                                $url_suscripcion=$contrato->getSuscripcionUrl();
                                break;
                            case "SUSCRIPCION_FALLIDA":
                            case "CANCELADA":
                                $url_suscripcion=$this->inscribir($contrato,$configuracion,$cuotas,$url_return);
                                break;
                            default:
                                 throw new Exception("Existe un error interno, comunicarse con adminitración");
                             break;
                        }
                        return new RedirectResponse($url_suscripcion);
                    }
                }catch(\Exception $e){
                    $virtualPosLog = new VirtualPosLog();
                    $virtualPosLog->setRequest("suscripcion_new");
                    $virtualPosLog->setExito(0);
                    $virtualPosLog->setContrato($contrato);
                    $virtualPosLog->setFechaRegistro(new \DateTime(date("Y-m-d")));
                    $virtualPosLog->setResponse($e->getMessage());
                    $entityManager->persist($virtualPosLog);
                    $entityManager->flush();
                    $error='<div class="alert alert-danger" role="alert">
                        <strong>Error</strong><br><p>'.$e->getMessage().'</p>
                        </div>';
                }
            }
        }else{
            $error='<div class="alert alert-danger" role="alert">
                        <strong>Error</strong><br><p>La sesión no es válida</p>
                        </div>';
        }
        return $this->render('suscripcion/new.html.twig', [
            'controller_name' => 'SuscripcionController',
            'error'=>$error,
            'form' => $form->createView(),
            'redirect_js'=>$redirect_js
        ]);
    }

    /**
     * @Route("/{uuid}/validar", name="suscripcion_validar",methods={"GET","POST"})
     */
    public function validar(Request $request, 
                        $uuid, ContratoRepository $contratoRepository,
                        ConfiguracionRepository $configuracionRepository,
                        CuotaRepository $cuotaRepository
                       ):Response
    {
        $contrato = $contratoRepository->findOneBy(["sesionSuscripcion"=>$uuid]);
        if($contrato->getSesionSuscripcionActiva()==1){
            $error="aceptado";
            $entityManager = $this->getDoctrine()->getManager();
            $virtualPosUuid = $_POST["uuid"];
            $configuracion = $configuracionRepository->find(1);
            try{
                $virtualPos =new VirtualPos($configuracion->getVirtualPosApiKey(),
                                            $configuracion->getVirtualPosSecretKey(),
                                            $configuracion->getVirtualPosPlan(),
                                            $configuracion->getVirtualPosUrl());
                $response = $virtualPos->recuperarSuscripcion($virtualPosUuid);
                if($response["suscription"]["status"]=="ACTIVA"){
                    $cuotas = $cuotaRepository->findBy(["contrato"=>$contrato, "anular"=>null],["fechaPago"=>"ASC"]);
                    foreach ($cuotas as $cuota) {
                        if($cuota->getInvoiceId()==null){
                            $response_cuotas=$virtualPos->crearCuota($cuota,$contrato->getSuscripcionId());
                            $cuota->setInvoiceId($response_cuotas["response"]["charge"]["id"]);
                            $entityManager->persist($cuota);
                            $entityManager->flush();
                            $virtualPosLogCuota = new VirtualPosLog();
                            $virtualPosLogCuota->setExito(1);
                            $virtualPosLogCuota->setContrato($contrato);
                            $virtualPosLogCuota->setFechaRegistro(new \DateTime(date("Y-m-d")));
                            $virtualPosLogCuota->setResponse(json_encode($response_cuotas["response"]));
                            $virtualPosLogCuota->setRequest($response_cuotas["request"]);
                            $entityManager->persist($virtualPosLogCuota);
                            $entityManager->flush();
                        }
                    }

                    $historicoSuscripcion = new ContratoHistoricoSuscripcion();
                    $historicoSuscripcion->setContrato($contrato);
                    $historicoSuscripcion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                    $historicoSuscripcion->setExito(true);
                    $historicoSuscripcion->setObservacion("La validación de la suscripción fue exitosa");
                    $historicoSuscripcion->setSuscripcionId($contrato->getSuscripcionId());

                    $contrato->setSesionSuscripcionActiva(0);
                    $contrato->setEstadoSuscripcion($response["suscription"]["status"]);

                    $entityManager->persist($historicoSuscripcion);
                    $entityManager->flush();
                    
                }else{
                    $error='<div class="alert alert-danger" role="alert">
                                <strong>Error</strong><br><p>Existe un error al activar suscripción, intentelo nuevamente.</p>
                                </div>';
                    $historicoSuscripcion = new ContratoHistoricoSuscripcion();
                    $historicoSuscripcion->setContrato($contrato);
                    $historicoSuscripcion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                    $historicoSuscripcion->setExito(false);
                    $historicoSuscripcion->setObservacion("La validación de la suscripción ha fallado. El servicio devolvió: ".$response["suscription"]["status"]);
                    $historicoSuscripcion->setSuscripcionId($contrato->getSuscripcionId());
                    $contrato->setEstadoSuscripcion("Error");
                    $entityManager->persist($historicoSuscripcion);
                    $entityManager->persist($contrato);
                    $entityManager->flush();
                }
                $entityManager->persist($contrato);
                $entityManager->flush();
            }catch(Exception $e){

                $historicoSuscripcion = new ContratoHistoricoSuscripcion();
                $historicoSuscripcion->setContrato($contrato);
                $historicoSuscripcion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                $historicoSuscripcion->setExito(false);
                $historicoSuscripcion->setObservacion("ha ocurrido un error al suscribir, intentelo nuevamente");
                $historicoSuscripcion->setSuscripcionId($contrato->getSuscripcionId());
                $entityManager->persist($historicoSuscripcion);
                $entityManager->flush();
                // si algo fallo, anular la suscripcion y crear un rollback de las cuotas
                $virtualPos->cancelarSuscripcion($contrato->getSuscripcionId());
                $cuotas = $cuotaRepository->findBy(["contrato"=>$contrato, "anular"=>null],["fechaPago"=>"ASC"]);
                foreach ($cuotas as $cuota) {
                    $cuota->setInvoiceId(null);
                    $entityManager->persist($cuota);
                    $entityManager->flush();
                }
                $contrato->setEstadoSuscripcion("Error");
                $entityManager->persist($contrato);
                $entityManager->flush();
                $error='<div class="alert alert-danger" role="alert"><strong>Error</strong><br><p>Ha ocurrido un error al momento de la carga de cuotas, la suscripcion ha sido cancelada, vuelva a intentarlo.</p>
                        </div>';
            }
        }else{
            $error='<div class="alert alert-danger" role="alert">
                            <strong>Error</strong><br><p>La sesión de suscripción ya no se encuentra activa.</p>
                            </div>';
        }
      
        return $this->render('suscripcion/validar.html.twig', [
            'controller_name' => 'SuscripcionController',
            'error'=>$error
        ]);
    }
    /**
     * @Route("/{uuid}/validar_async", name="suscripcion_validar_async",methods={"GET","POST"})
     */
    public function validarAsync(Request $request, 
                        $uuid, ContratoRepository $contratoRepository,
                        ConfiguracionRepository $configuracionRepository,
                        CuotaRepository $cuotaRepository
                       ):JsonResponse
    {

        $contrato = $contratoRepository->findOneBy(["sesionSuscripcion"=>$uuid]);

        if($contrato->getSesionSuscripcionActiva()==1){
            $error="aceptado";
            $entityManager = $this->getDoctrine()->getManager();
            $virtualPosUuid = $_POST["uuid"];
            $configuracion = $configuracionRepository->find(1);
            $virtualPos =new VirtualPos($configuracion->getVirtualPosApiKey(),
                                        $configuracion->getVirtualPosSecretKey(),
                                        $configuracion->getVirtualPosPlan(),
                                        $configuracion->getVirtualPosUrl());
            $response = $virtualPos->recuperarSuscripcion($virtualPosUuid);
            
            if($response["suscription"]["status"]=="ACTIVA"){
                try{     
                    $cuotas = $cuotaRepository->findBy(["contrato"=>$contrato, "anular"=>null],["fechaPago"=>"ASC"]);
                    foreach ($cuotas as $cuota) {
                            if($cuota->getInvoiceId()==null){
                                $response_cuotas=$virtualPos->crearCuota($cuota,$contrato->getSuscripcionId());
                                $cuota->setInvoiceId($response_cuotas["response"]["charge"]["id"]);

                                $entityManager->persist($cuota);
                                $entityManager->flush();
                                $virtualPosLogCuota = new VirtualPosLog();
                                $virtualPosLogCuota->setExito(1);
                                $virtualPosLogCuota->setContrato($contrato);
                                $virtualPosLogCuota->setFechaRegistro(new \DateTime(date("Y-m-d")));
                                $virtualPosLogCuota->setResponse(json_encode($response_cuotas["response"]));
                                $virtualPosLogCuota->setRequest($response_cuotas["request"]);
                                $entityManager->persist($virtualPosLogCuota);
                                $entityManager->flush();
                            }                  
                    }

                    $historicoSuscripcion = new ContratoHistoricoSuscripcion();
                    $historicoSuscripcion->setContrato($contrato);
                    $historicoSuscripcion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                    $historicoSuscripcion->setExito(true);
                    $historicoSuscripcion->setObservacion("La validación de la suscripción fue exitosa");
                    $historicoSuscripcion->setSuscripcionId($contrato->getSuscripcionId());

                    $contrato->setSesionSuscripcionActiva(0);
                    $contrato->setEstadoSuscripcion($response["suscription"]["status"]);

                    $entityManager->persist($historicoSuscripcion);
                    $entityManager->flush();
                }catch(Exception $e){

                    $historicoSuscripcion = new ContratoHistoricoSuscripcion();
                    $historicoSuscripcion->setContrato($contrato);
                    $historicoSuscripcion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                    $historicoSuscripcion->setExito(false);
                    $historicoSuscripcion->setObservacion("ha ocurrido un error al suscribir, intentelo nuevamente");
                    $historicoSuscripcion->setSuscripcionId($contrato->getSuscripcionId());
                    $entityManager->persist($historicoSuscripcion);
                    $entityManager->flush();
                    // si algo fallo, anular la suscripcion y crear un rollback de las cuotas
                    $virtualPos->cancelarSuscripcion($contrato->getSuscripcionId());
                    $cuotas = $cuotaRepository->findBy(["contrato"=>$contrato, "anular"=>null],["fechaPago"=>"ASC"]);
                    foreach ($cuotas as $cuota) {
                        $cuota->setInvoiceId(null);
                        $entityManager->persist($cuota);
                        $entityManager->flush();
                    }
                    $contrato->setEstadoSuscripcion("Error");
                    $entityManager->persist($contrato);
                    $entityManager->flush();
                    return $this->json(["exito"=>0,"mensaje"=>$e->getMessage()],400);
                }
            }else{

                $historicoSuscripcion = new ContratoHistoricoSuscripcion();
                $historicoSuscripcion->setContrato($contrato);
                $historicoSuscripcion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                $historicoSuscripcion->setExito(false);
                $historicoSuscripcion->setObservacion("La validación Async de la suscripción ha fallado. El servicio devolvió: ".$response["suscription"]["status"]);
                $historicoSuscripcion->setSuscripcionId($contrato->getSuscripcionId());
                $contrato->setEstadoSuscripcion("Error");
                $entityManager->persist($historicoSuscripcion);
                $entityManager->persist($contrato);
                $entityManager->flush();
                return $this->json(["exito"=>0,"mensaje"=>$historicoSuscripcion->getObservacion()],400);
            
            }
            $entityManager->persist($contrato);
            $entityManager->flush();
            
        }else{
            return $this->json(["exito"=>0],400);
        }
      
        return $this->json(["exito"=>1],200);
    }

    /**
     * @Route("/{uuid}/re_validar", name="suscripcion_re_validar",methods={"GET","POST"})
     */
    public function reValidar(Request $request, 
                        $uuid, ContratoRepository $contratoRepository,
                        ConfiguracionRepository $configuracionRepository,
                        CuotaRepository $cuotaRepository
                       ):JsonResponse
    {

        $contrato = $contratoRepository->findOneBy(["sesionSuscripcion"=>$uuid]);

        if($contrato->getSesionSuscripcionActiva()==1){
            $error="aceptado";
            $entityManager = $this->getDoctrine()->getManager();
            $virtualPosUuid = $contrato->getSuscripcionId();
            $configuracion = $configuracionRepository->find(1);
            $virtualPos =new VirtualPos($configuracion->getVirtualPosApiKey(),
                                        $configuracion->getVirtualPosSecretKey(),
                                        $configuracion->getVirtualPosPlan(),
                                        $configuracion->getVirtualPosUrl());
            $response = $virtualPos->recuperarSuscripcion($virtualPosUuid);
            
            if($response["suscription"]["status"]=="ACTIVA"){
                try{
                    
                    $cuotas = $cuotaRepository->findBy(["contrato"=>$contrato, "anular"=>null],["fechaPago"=>"ASC"]);
                    foreach ($cuotas as $cuota) {
                            if($cuota->getInvoiceId()==null){
                                $response_cuotas=$virtualPos->crearCuota($cuota,$contrato->getSuscripcionId());
                                $cuota->setInvoiceId($response_cuotas["response"]["charge"]["id"]);

                                $entityManager->persist($cuota);
                                $entityManager->flush();
                                $virtualPosLogCuota = new VirtualPosLog();
                                $virtualPosLogCuota->setExito(1);
                                $virtualPosLogCuota->setContrato($contrato);
                                $virtualPosLogCuota->setFechaRegistro(new \DateTime(date("Y-m-d")));
                                $virtualPosLogCuota->setResponse(json_encode($response_cuotas["response"]));
                                $virtualPosLogCuota->setRequest($response_cuotas["request"]);
                                $entityManager->persist($virtualPosLogCuota);
                                $entityManager->flush();
                            }                    
                    }

                    $historicoSuscripcion = new ContratoHistoricoSuscripcion();
                    $historicoSuscripcion->setContrato($contrato);
                    $historicoSuscripcion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                    $historicoSuscripcion->setExito(true);
                    $historicoSuscripcion->setObservacion("La Re-validación de la suscripción fue exitosa");
                    $historicoSuscripcion->setSuscripcionId($contrato->getSuscripcionId());
                    $contrato->setSesionSuscripcionActiva(0);
                    $contrato->setEstadoSuscripcion($response["suscription"]["status"]);
                    $entityManager->persist($historicoSuscripcion);
                    $entityManager->flush();
                }catch(Exception $e){
                    $historicoSuscripcion = new ContratoHistoricoSuscripcion();
                    $historicoSuscripcion->setContrato($contrato);
                    $historicoSuscripcion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                    $historicoSuscripcion->setExito(false);
                    $historicoSuscripcion->setObservacion("ha ocurrido un error al suscribir, intentelo nuevamente: Error:".$e->getMessage());
                    $historicoSuscripcion->setSuscripcionId($contrato->getSuscripcionId());
                    $entityManager->persist($historicoSuscripcion);
                    $entityManager->flush();
                    // si algo fallo, anular la suscripcion y crear un rollback de las cuotas
                    $virtualPos->cancelarSuscripcion($contrato->getSuscripcionId());
                    $cuotas = $cuotaRepository->findBy(["contrato"=>$contrato, "anular"=>null],["fechaPago"=>"ASC"]);
                    foreach ($cuotas as $cuota) {
                        $cuota->setInvoiceId(null);
                        $entityManager->persist($cuota);
                        $entityManager->flush();
                    }
                    $contrato->setEstadoSuscripcion("Error");
                    $entityManager->persist($contrato);
                    $entityManager->flush();
                    return $this->json(["exito"=>0,"mensaje"=>$historicoSuscripcion->getObservacion()],200);
                }
            }else{

                $historicoSuscripcion = new ContratoHistoricoSuscripcion();
                $historicoSuscripcion->setContrato($contrato);
                $historicoSuscripcion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                $historicoSuscripcion->setExito(false);
                $historicoSuscripcion->setObservacion("La validación de la suscripción ha fallado. El servicio devolvió: ".$response["suscription"]["status"]);
                $historicoSuscripcion->setSuscripcionId($contrato->getSuscripcionId());
                $contrato->setEstadoSuscripcion("Error");
                $entityManager->persist($historicoSuscripcion);
                $entityManager->persist($contrato);
                $entityManager->flush();
                return $this->json(["exito"=>0,"mensaje"=>$historicoSuscripcion->getObservacion()],200);
            }
            $entityManager->persist($contrato);
            $entityManager->flush();
        }else{
            return $this->json(["exito"=>0,"mensaje"=>"La sesión de suscripción ya no se encuentra activa."],200);
        }
      
        return $this->json(["exito"=>1],200);
    }
    
    public function inscribir(Contrato $contrato,Configuracion $configuracion,$cuotas,$url_return){
        
        $entityManager = $this->getDoctrine()->getManager();
        $virtualPos = new VirtualPos($configuracion->getVirtualPosApiKey(),
                            $configuracion->getVirtualPosSecretKey(),
                            $configuracion->getVirtualPosPlan(),
                            $configuracion->getVirtualPosUrl());
        
        
        $nombre="";
        $apellido="";
        $nombreSeparado=$this->separarNombreApellido($contrato->getNombre());
        $nombre=$nombreSeparado["nombres"];
        $apellido=$nombreSeparado["apellidos"];
       
        try{
            $telefono = str_replace("+","",$contrato->getTelefono());
            $response = $virtualPos->crearSuscripcion($contrato->getEmail(),
                                                    $contrato->getRut(),
                                                    $nombre,
                                                    $apellido,
                                                    $telefono,
                                                    $cuotas,
                                                    $url_return,
                                                    $contrato->getId());

            $contrato->setSuscripcionId($response["suscription"]["id"]);
            $contrato->setSuscripcionUrl($response["url_redirect"]);
            $contrato->setEstadoSuscripcion(null);
            $entityManager->persist($contrato);
            $entityManager->flush();

            $virtualPosLog = new VirtualPosLog();
            $virtualPosLog->setRequest("suscripcion_new");
            $virtualPosLog->setExito(1);
            $virtualPosLog->setContrato($contrato);
        
            $virtualPosLog->setFechaRegistro(new \DateTime(date("Y-m-d")));
            $virtualPosLog->setResponse(json_encode($response));
            $virtualPosLog->setRequest($response["request"]);
            $entityManager->persist($virtualPosLog);


            $historicoSuscripcion = new ContratoHistoricoSuscripcion();
            $historicoSuscripcion->setContrato($contrato);
            $historicoSuscripcion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
            
            $historicoSuscripcion->setObservacion("Cliente presiona en boton suscribir");
            $historicoSuscripcion->setSuscripcionId($contrato->getSuscripcionId());
            $historicoSuscripcion->setExito(true);
            $entityManager->persist($historicoSuscripcion);
            $entityManager->flush();
            //redireccionamos a la pagina de virtual pos
            return $response["url_redirect"];
        }catch(Exception $e)
        {
            throw new Exception($e->getMessage());
        }
    
    }
    public function consultarSuscripcion(String $uuid, Configuracion $configuracion)
    {
        $virtualPos = new VirtualPos($configuracion->getVirtualPosApiKey(),
                            $configuracion->getVirtualPosSecretKey(),
                            $configuracion->getVirtualPosPlan(),
                            $configuracion->getVirtualPosUrl());

        $response = $virtualPos->recuperarSuscripcion($uuid);

        return $response["suscription"]["status"];

    }
    function separarNombreApellido($nombreCompleto)
    {
        // Limpiar espacios extra
        $nombreCompleto = trim($nombreCompleto);
        $nombreCompleto = preg_replace('/\s+/', ' ', $nombreCompleto);
        // Si queda vacío
        if (empty($nombreCompleto)) {
            return [
                'nombres' => ' - ',
                'apellidos' => ' - '
            ];
        }
        // Separar palabras
        $partes = explode(' ', $nombreCompleto);
        $cantidad = count($partes);
        if ($cantidad === 1) {
            return [
                'nombres' => ucfirst(strtolower($partes[0])),
                'apellidos' => ' - '
            ];
        }
        if ($cantidad === 2) {
            return [
                'nombres' => ucfirst(strtolower($partes[0])),
                'apellidos' => ucfirst(strtolower($partes[1]))
            ];
        }
        // 3 o más palabras
        $apellidos = array_pop($partes);
        $nombres = implode(' ', $partes);
        return [
            'nombres' => ucwords(strtolower($nombres)),
            'apellidos' => ucfirst(strtolower($apellidos))
        ];
    }
}
