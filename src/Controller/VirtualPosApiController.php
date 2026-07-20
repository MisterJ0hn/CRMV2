<?php

namespace App\Controller;

use App\Entity\ContratoHistoricoSuscripcion;
use App\Entity\CuentaCorriente;
use App\Entity\Cuota;
use App\Entity\Pago;
use App\Entity\PagoCanal;
use App\Entity\PagoCuotas;
use App\Entity\PagoTipo;
use App\Entity\Usuario;
use App\Entity\VirtualPosLog;
use App\Repository\ConfiguracionRepository;
use App\Repository\ContratoRepository;
use App\Repository\CuotaRepository;
use App\Repository\PagoCuotasRepository;
use App\Service\VirtualPos;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class VirtualPosApiController extends AbstractController
{
    private function validateAuth(Request $request, string $apiKey): bool
    {
        $header = $request->headers->get('Authentication') ?? $request->headers->get('Authorization') ?? '';
        return $header === $apiKey;
    }

    private function verifyJwt(string $jwt, string $secretKey): ?array
    {
        $parts = explode('.', $jwt); 
        if (count($parts) !== 3) 
        { 
            return null; 
        } 
        list($h, $p, $s) = $parts; 
        
        $pad = function (string $b64) 
        { 
            return $b64 . str_repeat('=', (4 - strlen($b64) % 4) % 4); 
        }; 
        $sigExpected = hash_hmac('sha256', "$h.$p", $secretKey, true); 
        $sigProvided = base64_decode(strtr($pad($s), '-_', '+/')); 
        if (!hash_equals($sigExpected, $sigProvided)) 
        { 
            return null; 
        } 
        
        return json_decode(base64_decode(strtr($pad($p), '-_', '+/')), true);
    }

    /**
     * @Route("/api/virtual-pos/consultar", methods={"POST"})
     */
    public function consultar(
        Request $request,
        ConfiguracionRepository $confRepo,
        ContratoRepository $contratoRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $log = new VirtualPosLog();
        $log->setFechaRegistro(new \DateTime());
        $log->setRequest($request->getContent() ?: '{}');

        try {
            $conf = $confRepo->find(1);
            
            if (!$this->validateAuth($request, (string) $conf->getCrmApiKey())) {
                return $this->logAndReturn($em, $log, false, ['error' => 'No autorizado'], 401);
            }

            $signature = $request->headers->get('Signature') ?? '';
    
            if (!$signature || !$this->verifyJwt($signature, (string) $conf->getCrmSecretKey())) {
                return $this->logAndReturn($em, $log, false, ['error' => 'Firma inválida'], 401);
            }

            $data = json_decode($request->getContent(), true);
            $rut  = trim($data['rut'] ?? '');

            if ($rut === '') {
                return $this->logAndReturn($em, $log, false, ['error' => 'RUT requerido'], 400);
            }

            $contratos = $contratoRepo->findActiveByRut($rut);

            if (empty($contratos)) {
                return $this->logAndReturn($em, $log, false, ['error' => 'Cliente no encontrado'], 404);
            }

            $primero    = $contratos[0];
            $partes     = explode(' ', trim((string) $primero->getCliente()->getNombre()), 2);
            $firstName  = $partes[0] ?? '';
            $lastName   = $partes[1] ?? '';

            $debits = [];
            $contatosIds = array_map(fn($c) => $c->getId(), $contratos);
            $cuotas = $em->getRepository(Cuota::class)->createQueryBuilder('c')
                ->where('c.contrato IN (:contratos)')
                ->andWhere('c.monto > c.pagado OR c.pagado IS NULL')
                ->andWhere('c.anular IS NULL OR c.anular = false')
                ->setParameter('contratos', $contatosIds)
                ->orderBy('c.fechaPago', 'ASC')
                ->getQuery()
                ->getResult();

            foreach ($cuotas as $cuota) {
                
                if($this->esPagoPendiente($cuota, $em, $confRepo)){
                    if($cuota->getPagado()!=null){
                    $monto = $cuota->getMonto()-$cuota->getPagado();                
                    }else{
                        $monto = $cuota->getMonto();
                    }

                    $debits[] = [
                        'invoice'     => $cuota->getId(),
                        //'description' => 'Folio '.$cuota->getContrato()->getFolio(),
                        'description' => 'Folio '.$cuota->getContrato()->getFolio().' - Cuota '.$cuota->getNumero(),
                        'issueDate'   => $cuota->getContrato()->getFechaCreacion()
                            ? $cuota->getContrato()->getFechaCreacion()->format('Y-m-d')
                            : null,
                        'amount'      => $monto,
                        'dueDate'     => $cuota->getFechaPago()
                            ? $cuota->getFechaPago()->format('Y-m-d')
                            : null,
                        'lateFee'     => 0,
                    ];
                }
            }
            

            $response = [
                'client' => [
                    'firstName' => $firstName,
                    'lastName'  => $lastName,
                    'phone'     => $primero->getCliente()->getTelefono(),
                    'email'     => $primero->getCliente()->getCorreo(),
                    'debits'    => $debits,
                ],
            ];

            return $this->logAndReturn($em, $log, true, $response, 200);

        } catch (\Exception $e) {
            return $this->logAndReturn($em, $log, false, ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/api/virtual-pos/informar-pago", methods={"POST"})
     */
    public function informarPago(
        Request $request,
        ConfiguracionRepository $confRepo,
        EntityManagerInterface $em,
        CuotaRepository $cuotaRepository,
        PagoCuotasRepository $pagoCuotasRepository
    ): JsonResponse {
        $log = new VirtualPosLog();
        $log->setFechaRegistro(new \DateTime());
        $log->setRequest($request->getContent() ?: '{}');

        try {
            $conf = $confRepo->find(1);

            if (!$this->validateAuth($request, (string) $conf->getCrmApiKey())) {
                return $this->logAndReturn($em, $log, false, ['error' => 'No autorizado'], 401);
            }

            $data     = json_decode($request->getContent(), true);
            $datetime = $data['datetime'] ?? null;
            $payment  = $data['payment'] ?? null;

            if (!$datetime || !is_array($payment)) {
                return $this->logAndReturn($em, $log, false, ['error' => 'Payload inválido'], 400);
            }

            // Verify JWT Signature: payload must contain datetime and invoices matching the body
            $signature = $request->headers->get('Signature') ?? '';
            if (!$signature) {
                return $this->logAndReturn($em, $log, false, ['error' => 'Firma requerida'], 401);
            }

            $jwtPayload = $this->verifyJwt($signature, (string) $conf->getCrmSecretKey());
            if (!$jwtPayload) {
                return $this->logAndReturn($em, $log, false, ['error' => 'Firma inválida'], 401);
            }

            $invoicesFromBody = (string) ($payment['invoices'] ?? '');
            if (($jwtPayload['datetime'] ?? null) !== $datetime ||
                ($jwtPayload['invoices'] ?? null) !== $invoicesFromBody) {
                return $this->logAndReturn($em, $log, false, ['error' => 'Firma no coincide con el payload'], 401);
            }

            $status = $payment['status'] ?? '';
            if ($status !== 'pagado') {
                return $this->logAndReturn($em, $log, true, ['success' => true, 'message' => 'Estado recibido: ' . $status], 200);
            }

            $uuid        = (string) ($payment['uuid'] ?? '');
            $amount      = (int)   ($payment['amount'] ?? 0);
            $invoicesStr = (string) ($payment['invoices'] ?? '');
            $invoiceIds  = array_values(array_filter(array_map('trim', explode(',', $invoicesStr))));

            if (empty($invoiceIds)) {
                return $this->logAndReturn($em, $log, false, ['error' => 'Sin invoices'], 400);
            }

            // Idempotencia: evitar duplicar pagos con el mismo UUID
            $existingPago = $em->getRepository(Pago::class)->findOneBy(['comprobante' => $uuid]);
            if ($existingPago) {
                return $this->logAndReturn($em, $log, true, ['success' => true, 'message' => 'Pago ya registrado', 'pago_id' => $existingPago->getId()], 200);
            }

            $cuotas   = [];
            $contrato = null;
            $i=0;
            foreach ($invoiceIds as $invoiceId) {
                $cuota = $em->getRepository(Cuota::class)->find((int) $invoiceId);
                if (!$cuota) {
                    return $this->logAndReturn($em, $log, false, ['error' => 'Cuota no encontrada: ' . $invoiceId], 404);
                }
                $cuotas[] = $cuota;
                $i++;
            }

            $createdAt = $payment['created_at'] ?? $datetime;
            $fechaPago = new \DateTime($createdAt);

            $observacion = "";
            if($i>1){
                $observacion = "pago de múltiples cuotas";
            }
           
            $monto_total_pendiente = $amount;
            foreach ($cuotas as $cuota) {
                $contrato = $cuota->getContrato();

                //Primero: obtendremos el valor de la cuota que se debe pagar:
                if($cuota->getPagado() != null ){
                    $monto_pago = $cuota->getMonto()-$cuota->getPagado();
                }else{
                    $monto_pago = $cuota->getMonto();
                }

                $monto_total_pendiente -= $monto_pago;

                $pago = new Pago();
                $pago->setNcomprobante($uuid);            
                $pago->setComprobante('nodisponible.png');
                $pago->setBoleta('');
                $pago->setMonto($monto_pago);
                $pago->setFechaPago($fechaPago);
                $pago->setHoraPago(new \DateTime($fechaPago->format('H:i:s')));
                $pago->setFechaRegistro(new \DateTime());
                $pago->setContrato($contrato);
                $pago->setPagoCanal($em->getRepository(PagoCanal::class)->find(7));
                $pago->setPagoTipo($em->getRepository(PagoTipo::class)->find(10));
                $pago->setUsuarioRegistro($em->getRepository(Usuario::class)->find(4));
                $pago->setCuentaCorriente($em->getRepository(CuentaCorriente::class)->find(4));
                $pago->setObservacion($observacion);
                $em->persist($pago);
                $em->flush();
                //Segundo: preguntemos si la cuota fue pagada total o parcial:
                if($cuota->getPagado() != null ){
                    $pagoCuotasRepository->asociarPagos($contrato,$cuotaRepository,$pagoCuotasRepository,$pago);
                }else{                      

                    $pagoCuota = new PagoCuotas();
                    $pagoCuota->setPago($pago);
                    $pagoCuota->setCuota($cuota);
                    $pagoCuota->setMonto($cuota->getMonto());
                    $em->persist($pagoCuota);

                    $cuota->setPagado($cuota->getMonto());
                    $em->persist($cuota);
                    $em->flush();
                }
                
                $log1 = new VirtualPosLog();
                $log1->setFechaRegistro(new \DateTime());
                $log1->setRequest($request->getContent() ?: '{}');

                $this->log($em, $log1, true, ['success' => true, 'message' => 'Pago registrado', 'pago_id' => $pago->getId()], 200);
                            
                if($cuota->getContrato()->getContratoEstadoSuscripcion()!=null && $cuota->getContrato()->getContratoEstadoSuscripcion()->getId() == 2){
                    $this->cancelarPagoVirtualPos($cuota, $em, $confRepo);
                    $historicoSuscripcion = new ContratoHistoricoSuscripcion();
                    $historicoSuscripcion->setContrato($contrato);
                    $historicoSuscripcion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                    $historicoSuscripcion->setExito(true);
                    $historicoSuscripcion->setObservacion("<p>Pago realizado con éxito desde Portal de Pago</p><p>detalle del pago:</p>
                                                            <ul>
                                                                <li>número cuota:".$cuota->getNumero()."</li>
                                                                <li>Monto Pago: $".$pago->getMonto()."</li>
                                                                <li>Fecha Pago: ".$pago->getFechaPago()->format("Y-m-d")."</li>
                                                            </ul>");
            
            
                    $historicoSuscripcion->setSuscripcionId($contrato->getSuscripcionId());
            
                    $em->persist($historicoSuscripcion);
                    $em->flush();
                }

                $primeraCuotaVigente=$cuotaRepository->findOneByPrimeraVigente($contrato->getId());
                if($primeraCuotaVigente){
                    $contrato->setProximoVencimiento($primeraCuotaVigente->getFechaPago());
                    $em->persist($contrato);
                }
                 $log2 = new VirtualPosLog();
                $log2->setFechaRegistro(new \DateTime());
                $log2->setRequest($request->getContent() ?: '{}');

                $this->log($em, $log2, true, ['success' => true, 'message' => 'Pago asociado a cuota '.$cuota->getId(), 'pago_id' => $pago->getId()], 200);

            }
            if($monto_total_pendiente>0){
                $pago = new Pago();
                $pago->setNcomprobante($uuid);            
                $pago->setComprobante('nodisponible.png');
                $pago->setBoleta('');
                $pago->setMonto($monto_total_pendiente);
                $pago->setFechaPago($fechaPago);
                $pago->setHoraPago(new \DateTime($fechaPago->format('H:i:s')));
                $pago->setFechaRegistro(new \DateTime());
                $pago->setContrato($contrato);
                $pago->setPagoCanal($em->getRepository(PagoCanal::class)->find(7));
                $pago->setPagoTipo($em->getRepository(PagoTipo::class)->find(10));
                $pago->setUsuarioRegistro($em->getRepository(Usuario::class)->find(4));
                $pago->setCuentaCorriente($em->getRepository(CuentaCorriente::class)->find(4));
                $pago->setObservacion($observacion);
                $em->persist($pago);
                $em->flush();
                
                $pagoCuotasRepository->asociarPagos($contrato,$cuotaRepository,$pagoCuotasRepository,$pago);

                
            }

            if ($contrato) {
                $log->setContrato($contrato);
            }

            $responseData = ['success' => true, 'pago_id' => $pago->getId()];
            $em->flush();

            return $this->logAndReturn($em, $log, true, $responseData, 200);

        } catch (\Exception $e) {
            $log->setExito(false);
            $log->setResponse(json_encode(['error' => $e->getMessage()]));
            try {
                $em->persist($log);
                $em->flush();
            } catch (\Exception $e2) {
            }
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    private function logAndReturn(
        EntityManagerInterface $em,
        VirtualPosLog $log,
        bool $exito,
        array $data,
        int $statusCode
    ): JsonResponse {
        $log->setExito($exito);
        $log->setResponse(json_encode($data));
        $em->persist($log);
        $em->flush();

        return $this->json($data, $statusCode);
    }

    private function log(
        EntityManagerInterface $em,
        VirtualPosLog $log,
        bool $exito,
        array $data,
        int $statusCode
    ) 
    {
        $log->setExito($exito);
        $log->setResponse(json_encode($data));
        $em->persist($log);
        $em->flush();
    }

    private function cancelarPagoVirtualPos(Cuota $cuota, EntityManagerInterface $em, ConfiguracionRepository $confRepo): void
    {
        if($cuota->getInvoiceId() != null){
            $log = new VirtualPosLog();
            $log->setFechaRegistro(new \DateTime());
            $log->setContrato($cuota->getContrato());
           

            try{
                $configuracion = $confRepo->find(1);
                $virtualpos = new VirtualPos($configuracion->getVirtualPosApiKey(), 
                                            $configuracion->getVirtualPosSecretKey(),
                                            $configuracion->getVirtualPosPlan(),
                                            $configuracion->getVirtualPosUrl());
                $response=$virtualpos->cancelarCargo($cuota->getInvoiceId());

                $log->setExito(true);
                $log->setRequest("Cancelar Cargo - Invoice ID: ".$cuota->getInvoiceId());
                $log->setResponse(json_encode($response));
                $em = $this->getDoctrine()->getManager();
                $em->persist($log);
                $em->flush();
            }catch(\Exception $e){
                $log->setExito(false);
                $log->setRequest("Cancelar Cargo - Invoice ID: ".$cuota->getInvoiceId());
                $log->setResponse(json_encode(['error' => $e->getMessage()]));
                $em = $this->getDoctrine()->getManager();
                $em->persist($log);
                $em->flush();
                //loguear error para revisión posterior, pero no interrumpir el proceso de cancelación
                //ejemplo: $this->logger->error('Error al cancelar pago en Virtual POS: ' . $e->getMessage());
            }
        }
    }

    private function esPagoPendiente(Cuota $cuota, EntityManagerInterface $em, ConfiguracionRepository $confRepo): bool
    {
        if($cuota->getInvoiceId() != null){
            $log = new VirtualPosLog();
            $log->setFechaRegistro(new \DateTime());
            $log->setContrato($cuota->getContrato());
           

            try{
                $configuracion = $confRepo->find(1);
                $virtualpos = new VirtualPos($configuracion->getVirtualPosApiKey(), 
                                            $configuracion->getVirtualPosSecretKey(),
                                            $configuracion->getVirtualPosPlan(),
                                            $configuracion->getVirtualPosUrl());
                $response=$virtualpos->recuperarCuota($cuota->getInvoiceId());

                $log->setExito(true);
                $log->setRequest("Consultar Cargo pendiente - Invoice ID: ".$cuota->getInvoiceId());
                $log->setResponse(json_encode($response));
                $em = $this->getDoctrine()->getManager();
                $em->persist($log);
                $em->flush();

                if($response['response']['charge']['status']=='procesando')
                {
                    return false;
                }
                   
            }catch(\Exception $e){
                $log->setExito(false);
                $log->setRequest("Consultar Cargo - Invoice ID: ".$cuota->getInvoiceId());
                $log->setResponse(json_encode(['error' => $e->getMessage()]));
                $em = $this->getDoctrine()->getManager();
                $em->persist($log);
                $em->flush();
                //loguear error para revisión posterior, pero no interrumpir el proceso de cancelación
                //ejemplo: $this->logger->error('Error al cancelar pago en Virtual POS: ' . $e->getMessage());
                return false;
            }
           
        }
        return true;
    }
}
