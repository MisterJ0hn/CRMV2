<?php

namespace App\Command;

use App\Entity\Configuracion;
use App\Entity\ContratoHistoricoSuscripcion;
use App\Entity\CuentaCorriente;
use App\Entity\Cuota;
use App\Entity\Pago;
use App\Entity\PagoCanal;
use App\Entity\PagoCuotas;
use App\Entity\PagoTipo;
use App\Entity\Usuario;
use App\Service\VirtualPos;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ActualizarPagoVirtualposCommand extends Command
{
    protected static $defaultName = 'app:actualizar-pago-virtualpos';
    protected static $defaultDescription = 'Verifica que los pagos realizados por virtualpos estén actualizados en el sistema';

    private $container;

    public function __construct(ContainerInterface $container){
        $this->container=$container;   
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('contratoId', null, InputOption::VALUE_OPTIONAL, 'id del contrato para actualizar pagos virtualpos')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $entityManager= $this->container->get('doctrine')->getManager();
        $em=$this->container->get('doctrine');

        $io->note(sprintf('obteniendo cuotas pendientes de pago virtualpos'));
        if ($input->getOption('contratoId')) {
            $contratoId = $input->getOption('contratoId');
            $cuotas = $em->getRepository(Cuota::class)->findCuotaPagoAutomaticoPendiente($contratoId);
        } else {
            $cuotas = $em->getRepository(Cuota::class)->findCuotaPagoAutomaticoPendiente();
        }
       
        $io->note(sprintf('obtenido cuotas pendientes de pago virtualpos'));
        $configuracion=$em->getRepository(Configuracion::class)->find(1);
        $Virtualpos = new VirtualPos(
                                    $configuracion->getVirtualPosApiKey(),
                                    $configuracion->getVirtualPosSecretKey(),
                                    $configuracion->getVirtualPosPlan(),
                                    $configuracion->getVirtualPosUrl());

        foreach ($cuotas as $cuota) {
            try{
                $io->note(sprintf('invoice id: '.$cuota->getInvoiceId().' - cuota id: '.$cuota->getId()));
                if($cuota->getInvoiceId()!=null){
                    
                    $response=$Virtualpos->recuperarCuota($cuota->getInvoiceId());
                    if($response["response"]["charge"]["status"]=="pagado"){
                        $charge = $response["response"]["charge"];
                        $io->note(sprintf('actualizando pago virtualpos cuota id: '.$cuota->getId()));
                        $contrato = $cuota->getContrato();
                        $pago = new Pago();
                        $pago->setBoleta("");
                        $pago->setComprobante( $charge["payment"]["order"]["uuid"]);
                        $pago->setMonto( $charge["payment"]["order"]["amount"]);
                        $pago->setFechaPago(new \DateTime( $charge["payment"]["order"]["authorized_at"]));
                        $pago->setPagoCanal($em->getRepository(PagoCanal::class)->find(6));
                        $pago->setPagoTipo($em->getRepository(PagoTipo::class)->find(2));
                        $pago->setContrato($contrato);
                        $pago->setUsuarioRegistro($em->getRepository(Usuario::class)->find(1));
                        $pago->setCuentaCorriente($em->getRepository(CuentaCorriente::class)->find(5));
                        $pago->setHoraPago(new \DateTime(date("H:i:s")));
                        $pago->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                        $entityManager->persist($pago);
                        $entityManager->flush();

                        $pagoCuota = new PagoCuotas();
                        $pagoCuota->setCuota($cuota);
                        $pagoCuota->setPago($pago);
                        $pagoCuota->setMonto($pago->getMonto());
                        $entityManager->persist($pagoCuota);
                        $entityManager->flush();

                         $cuota->setPagado($pago->getMonto());
                        $entityManager->persist($cuota);
                        $entityManager->flush();


                        $historicoSuscripcion = new ContratoHistoricoSuscripcion();
                        $historicoSuscripcion->setContrato($contrato);
                        $historicoSuscripcion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                        $historicoSuscripcion->setExito(true);
                        $historicoSuscripcion->setObservacion("<p>Pago realizado con exito desde Tarea Automática</p><p>detalle del pago:</p>
                                                                <ul>
                                                                    <li>numero cuota:".$cuota->getNumero()."</li>
                                                                    <li>Monto Pago: $".$pago->getMonto()."</li>
                                                                    <li>Fecha Pago: ".$pago->getFechaPago()->format("Y-m-d")."</li>
                                                                </ul>");
                        
                        $historicoSuscripcion->setSuscripcionId($contrato->getSuscripcionId());
                
                        $entityManager->persist($historicoSuscripcion);
                        $entityManager->flush();
                    }
                }
            }catch(\Exception $e){
                $io->note(sprintf("\n Error al actualizar pago virtualpos cuota id: ".$cuota->getId()." - ".$e->getMessage()));

            }
        }
        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return 0;
    }
}
