<?php

namespace App\Controller;

use App\Entity\CuentaCorriente;
use App\Entity\Pago;
use App\Entity\PagoCuotas;
use App\Entity\Usuario;
use App\Repository\CuentaCorrienteRepository;
use App\Repository\CuotaRepository;
use App\Repository\PagoCanalRepository;
use App\Repository\PagoCuotasRepository;
use App\Repository\PagoRepository;
use App\Repository\PagoTipoRepository;
use App\Repository\UsuarioRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/toku_webhook")
 */
class TokuWebhookController extends AbstractController
{
    /**
     * @Route("/", name="toku_webhook_index", methods={"POST"})
     */
    public function index(CuotaRepository $cuotaRepository, 
                            UsuarioRepository $usuarioRepository,
                            PagoTipoRepository $pagoTipoRepository, 
                            PagoCanalRepository $pagoCanalRepository,
                            CuentaCorrienteRepository $cuentaCorrienteRepository,
                            PagoRepository $pagoRepository,
                            PagoCuotasRepository $pagoCuotasRepository): Response
    {
        error_log("input",3,"/home/micrm.cl/test/TokuWebhook_log");
       
        $input = json_decode(file_get_contents('php://input'), true);

        error_log(print_r($input,true),3,"/home/micrm.cl/test/TokuWebhook_log");


        error_log("<br><br> event_type: ".$input['event_type'],3,"/home/micrm.cl/test/TokuWebhook_log");
        /*if($input['event_type']=='payment_intent.succeeded'){
            $entityManager = $this->getDoctrine()->getManager();
            $pagoToku=$input["payment_intent"];
  
            
                $cuota=$cuotaRepository->findOneBy(['invoiceId'=>$pagoToku['invoice']]);
                $user=$usuarioRepository->find(2067);
                if($cuota == null){

            
                    error_log("<br><br> error : Cuota no encontrada",3,"/home/micrm.cl/test/TokuWebhook_log");
                    return $this->json(print_r("{'message': 'Cuota no encontrada','code':400}"));
                }

                error_log("<br><br> invoice: ".$pagoToku['invoice'],3,"/home/micrm.cl/test/TokuWebhook_log");
                error_log("<br><br> Cuota : ".$cuota->getNumero(),3,"/home/micrm.cl/test/TokuWebhook_log");
                error_log("<br><br> Usuario : ".$user->getNombre(),3,"/home/micrm.cl/test/TokuWebhook_log");
           
                
            try{
                /*$estaPago=$pagoRepository->findOneBy(['ncomprobante'=>$pagoToku['buy_order'],'pagoCanal'=>$pagoCanalRepository->findOneBy(['nombre'=>"Toku"])]);
                
                if($estaPago){
                    error_log("<br><br> El pago ".$pagoToku['buy_order']." ya existe",3,"/home/micrm.cl/test/TokuWebhook_log");

                }else{

          
                $pago=new Pago();
                $pago->setNcomprobante($pagoToku['buy_order']);
                $pago->setMonto(intval($pagoToku['amount']));
                $tipo=$pagoTipoRepository->findOneBy(['nombre'=>'Transferencia']);

                $pago->setPagoTipo($tipo);
                $pago->setComprobante("nodisponible.png");
                $pago->setFechaRegistro(new \DateTime(date('Y-m-d H:i:s')));
                $pago->setFechaIngreso(new \DateTime(date("Y-m-d H:i")));
                $pago->setUsuarioRegistro($user);
                $pago->setFechaPago(new \DateTime(date('Y-m-d H:i',strtotime($pagoToku['transaction_date']))));
                $pago->setHoraPago(new \DateTime(date('H:i',strtotime($pagoToku['transaction_date']))));
                $pago->setPagoCanal($pagoCanalRepository->findOneBy(['nombre'=>"Toku"]));
                $pago->setCuentaCorriente($cuentaCorrienteRepository->find(4));
                


                //$entityManager->persist($pago);
                //$entityManager->flush();
                error_log("<br><br> Pago Creado",3,"/home/micrm.cl/test/TokuWebhook_log");
                //}
            }
            catch(Exception $e){
                error_log("<br><br> Error al crear pago ".$e->getMessage(),3,"/home/micrm.cl/test/TokuWebhook_log");
            }

            error_log("<br><br> cuota ".$cuota->getId(),3,"/home/micrm.cl/test/TokuWebhook_log");

            //$pagoCuotas=$pagoCuotasRepository->findByPago($pago->getId());

            if(null == $pagoCuotas["total"]){
                $total=0;
            }else{
                $total=$pagoCuotas["total"];
            }

            $pagoCuota=new PagoCuotas();
            $pagoCuota->setCuota($cuota);
            $pagoCuota->setPago($pago);
            $pagoCuota->setMonto($pago->getMonto()-$total);
            $entityManager->persist($pagoCuota);
            $entityManager->flush();

            error_log("<br><br> PagoCuota Creado",3,"/home/micrm.cl/test/TokuWebhook_log");

            $cuota->setPagado($cuota->getMonto());
            $entityManager->persist($cuota);
            $entityManager->flush();


            $contrato=$cuota->getContrato();
            //tomamos las cuotas y reseteamos el q_mov de las cobranzas
            $cobranzas = $contrato->getCobranzas();
            $qMov=0;
            foreach($cobranzas as $cobranza){
                $qMov++;
            }
            if($contrato->getQMov()-$qMov>0){
                $contrato->setQMov($contrato->getQMov()-$qMov);
                $entityManager->persist($contrato);
                $entityManager->flush();
            }

            $primeraCuotaVigente=$cuotaRepository->findOneByPrimeraVigente($contrato->getId());

            $contrato->setProximoVencimiento($primeraCuotaVigente->getFechaPago());
            $entityManager->persist($contrato);
            $entityManager->flush();
            
            error_log("<br><br> Cobranzas Reseteado",3,"/home/micrm.cl/test/TokuWebhook_log");

        }
        //error_log("ERROR",3,"/home/micrm.cl/test/TokuWebhook_log");

        return $this->json(print_r($input),200);
        */
        return null;
       
    }
    /**
     * @Route("/hello", name="toku_webhook_hello", methods={"GET","POST"})
     */
    public function heloWolrd(): Response
    {

        return $this->json(print_r("{id:1}"),200);

    }

}
