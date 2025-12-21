<?php

namespace App\Controller;


use App\Entity\Agenda;
use App\Entity\AgendaObservacion;
use App\Repository\AgendaRepository;
use App\Repository\AgendaStatusRepository;
use App\Repository\ConfiguracionRepository;
use App\Repository\CuentaRepository;
use App\Repository\ReunionRepository;
use App\Repository\UsuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse; 
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/agendar")
 */
class AgendarController extends AbstractController
{
    /**
     * @Route("/hora", name="api_agendar_hora", methods={"POST"})
     */
    public function agendar(Request $request, 
                        AgendaRepository $agendaRepository,
                        CuentaRepository $cuentaRepository, 
                        UsuarioRepository $usuarioRepository,
                        ReunionRepository $reunionRepository,
                        AgendaStatusRepository $agendaStatusRepository,
                        ConfiguracionRepository $configuracionRepository): JsonResponse
    {
        $persona = json_decode($request->getContent());

        $token="";
        $nombre="";
        $correo ="";
        $telefono=""; 
        $monto_deuda=0;
        $pago_mensual_deuda=0;
        $reunion = "";
        $ciudad="";
        $fecha_agenda="";
        $hora_agenda="";
        $observacion_str ="";
        $canal="WCTC260825 WhatsApp";   
        
        $correoCerrador="";
        $camposextra="";
        
        error_log($request->getContent(),3,"/home/ejam.cl/crm/API_log"); 
        if (is_object($persona)) {
            foreach ($persona as $clave => $valor) {
                switch($clave){
                    case "token":
                        $token = $valor;
                        break;
                    case "nombre":
                        $nombre = $valor;
                        break;
                    case "email":
                        $correo = $valor;
                        break;
                    case "telefono":
                        $telefono = $valor;                
                        break;
                    case "compania":
                        $compania = $valor;                
                        break;
                    case "correoCerrador":
                        $correoCerrador = $valor;
                        break;
                    case "monto_deuda":
                        $monto_deuda = $valor;
                        break;
                    case "pago_mensual_deuda":
                        $pago_mensual_deuda = $valor;   
                        break;
                    case "reunion":
                        $reunion = $valor;
                        break;
                    case "ciudad":  
                        $ciudad = $valor;
                        break;
                    case "fecha_agenda":
                        $fecha_agenda = $valor;
                        break;  
                    case "hora_agenda":
                        $hora_agenda = $valor;
                        break;
                    case "observacion":
                        $observacion_str = $valor;
                        break;
                    case "canal":
                        $canal = $valor;
                        break;
                    default:
                        $camposextra.= "<strong>".$clave."</strong>:";
                        $camposextra .= $valor."<br>";
                    break;
                }
            }
        }


        $configuraciones = $configuracionRepository->find(1);
        
        if($configuraciones->getAccessToken()!=$token){
            return new JsonResponse(['status' => false, 'message' => 'Token invalido '], 400);
        }   
        if (!$nombre || !$correo || !$telefono || !$hora_agenda || !$fecha_agenda || !$compania || !$correoCerrador  || !$reunion || !$ciudad || !$observacion_str) {
            return new JsonResponse(['status' => false, 'message' => 'Faltan datos obligatorios'], 400);
        }

        try {
            
            /*$agenda = new Agenda();
            $agenda->setNombre($nombre);
            $agenda->setEmail($email);
            $agenda->setFecha(new \DateTime($fecha));
            $agenda->setHora(new \DateTime($hora));

            $agendaRepository->save($agenda, true);*/
            $cerrador = $usuarioRepository->findOneBy(['correo'=>$correoCerrador,'estado'=>1]);
            if(null === $cerrador)
                return new JsonResponse(['status' => false, 'message' => 'No existe el cerrador'], 400);



            $cuenta=$cuentaRepository->findOneBy(['nombre'=>$compania]);

            if(null === $cuenta)
                return new JsonResponse(['status' => false, 'message' => 'No existe la compañia'], 400);

            $reunionObj=$reunionRepository->findOneBy(['nombre'=>$reunion]);
            if(null === $reunionObj)
                return new JsonResponse(['status' => false, 'message' => 'No existe el tipo de reunion'], 400);


            
            $agenda = new Agenda();
            $agenda->setStatus($agendaStatusRepository->find(5)); //JRM: 5 = Agendado
            $agenda->setNombreCliente($nombre);
            $agenda->setTelefonoCliente($telefono);
            $agenda->setEmailCliente($correo);
            $agenda->setFechaCarga(new \DateTime());
            $agenda->setCampania($canal);
            $agenda->setCuenta($cuenta);
            $agenda->setAbogado($cerrador);
            $agenda->setCiudadCliente($ciudad);
            $agenda->setFechaAsignado( new \DateTime($fecha_agenda." ".$hora_agenda));
            $agenda->setReunion($reunionObj);
            $agenda->setMonto($monto_deuda);
            $agenda->setPagoActual($pago_mensual_deuda);
            $agenda->setObsFormulario($camposextra);
            $agenda->setAgendador($usuarioRepository->find(23920)); //JRM: Usuario por defecto 1 = Sistema

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($agenda);
            $entityManager->flush();
            $observacion= new AgendaObservacion();
            $observacion->setAgenda($agenda);
            $observacion->setUsuarioRegistro($usuarioRepository->find(23920));
            $observacion->setStatus($agendaStatusRepository->find(5));
            $observacion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
            $observacion->setObservacion($observacion_str);
            $observacion->setSubStatus(null);
            $observacion->setAbogadoDestino($cerrador);
            $entityManager->persist($observacion);
            $entityManager->flush();
            return new JsonResponse(['status' => true, 'message' => 'Agendamiento creado correctamente'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => false, 'message' => 'Error al crear el agendamiento: '.$e->getMessage()], 500);
        }
    }

    /**
     * @Route("/agendador", name="api_agendar_agendador", methods={"POST"})
     */
    public function agendarAgendador(Request $request, 
                        AgendaRepository $agendaRepository,
                        CuentaRepository $cuentaRepository, 
                        UsuarioRepository $usuarioRepository,
                        ReunionRepository $reunionRepository,
                        AgendaStatusRepository $agendaStatusRepository,
                        ConfiguracionRepository $configuracionRepository): JsonResponse
    {
        $persona = json_decode($request->getContent());

        $token="";
        $nombre="";
        $correo ="";
        $telefono=""; 
        $fecha_carga="";
        $compania="";
        $canal="WCTC260825 WhatsApp";   
        $id_ghl="";
        $correoAgendador="";
        $camposextra="";
        $configuraciones = $configuracionRepository->find(1);


        error_log($request->getContent(),3,$this->getParameter('url_root')."/API_log"); 
        if (is_object($persona)) {
            foreach ($persona as $clave => $valor) {
                switch($clave){
                    case "token":
                        $token = $valor;
                        break;
                    case "nombre":
                        $nombre = $valor;
                        break;
                    case "email":
                        $correo = $valor;
                        break;
                    case "telefono":
                        $telefono = $valor;                
                        break;
                    case "compania":
                        $compania = $valor;                
                        break;
                    case "canal":
                        $canal = $valor;
                        break;
                    case "fecha_carga":
                        $fecha_carga = $valor;
                        break; 
                    case "correoAgendador":
                        $correoAgendador = $valor;
                        break;
                    case "id_ghl":
                    case "id":
                        $id_ghl = $valor;
                        break;                   
                    default:
                        $camposextra.= "<strong>".$clave."</strong>:";
                        $camposextra .= $valor."<br>";
                    break;
                }
            }
        }


        
        
        if($configuraciones->getAccessToken()!=$token){
            error_log("\nTokent invalido",3,$this->getParameter('url_root')."/API_log"); 
            return new JsonResponse(['status' => false, 'message' => 'Token invalido '], 400);
        }   
        if (!$nombre || !$correo || !$telefono || !$fecha_carga || !$compania || !$correoAgendador || !$compania ) {
            error_log("\nFaltan datos obligatorios",3,$this->getParameter('url_root')."/API_log"); 
            return new JsonResponse(['status' => false, 'message' => 'Faltan datos obligatorios'], 400);
        }

        try {
            
            /*$agenda = new Agenda();
            $agenda->setNombre($nombre);
            $agenda->setEmail($email);
            $agenda->setFecha(new \DateTime($fecha));
            $agenda->setHora(new \DateTime($hora));

            $agendaRepository->save($agenda, true);*/
            $agendador = $usuarioRepository->findOneBy(['correo'=>$correoAgendador,'estado'=>1]);
            if(null === $agendador)
                return new JsonResponse(['status' => false, 'message' => 'No existe el agendador'], 400);



            $cuenta=$cuentaRepository->findOneBy(['nombre'=>$compania]);

            if(null === $cuenta)
                return new JsonResponse(['status' => false, 'message' => 'No existe la compañia'], 400);

            


            
            $agenda = new Agenda();
            $agenda->setStatus($agendaStatusRepository->find(1)); //JRM: 5 = Agendado
            $agenda->setNombreCliente($nombre);
            $agenda->setTelefonoCliente($telefono);
            $agenda->setEmailCliente($correo);
            $agenda->setFechaCarga(new \DateTime());
            $agenda->setCampania($canal);
            $agenda->setCuenta($cuenta);
            $agenda->setAgendador($agendador);            
            $agenda->setObsFormulario($camposextra);
            $agenda->setIdGhl($id_ghl);
            
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($agenda);
            $entityManager->flush();
            
            return new JsonResponse(['status' => true, 'message' => 'Registro creado correctamente'], 200);
        } catch (\Exception $e) {
            error_log('\nError al crear el Registro: '.$e->getMessage(),3,$this->getParameter('url_root')."/API_log"); 
            return new JsonResponse(['status' => false, 'message' => 'Error al crear el Registro: '.$e->getMessage()], 500);
        }
    }


}   
