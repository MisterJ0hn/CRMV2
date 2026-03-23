<?php

namespace App\Command;

use App\Entity\Configuracion;
use App\Entity\Contrato;
use App\Entity\ContratoHistoricoSuscripcion;
use App\Entity\Cuota;
use App\Entity\VirtualPosLog;
use App\Service\VirtualPos;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ValidarSuscripcionesCommand extends Command
{
    protected static $defaultName = 'app:validar-suscripciones';
    protected static $defaultDescription = 'Revisión completa de cada contrato vs virtualPos, revisa si las cuotas se encuentran bien configuradas y el estado de estas';
    private $estadosValidos = ["pendiente", "pagado", "procesando", "rechazado"];
    private $container;
    private $entityManager;
    private $em;
    public function __construct(ContainerInterface $container){
        $this->container=$container;   
        $this->entityManager= $this->container->get('doctrine')->getManager();
        $this->em=$this->container->get('doctrine');
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('contratoId', null, InputOption::VALUE_OPTIONAL, 'id del contrato validar en virtualpos')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        

        $io->note(sprintf('obteniendo contratos pendientes de suscripcion'));
        if ($input->getOption('contratoId')) {
            $contratoId = $input->getOption('contratoId');        
            $contratos = $this->em->getRepository(Contrato::class)->findBy(["id"=>$contratoId]);            
        } else {            
            $contratos = $this->em->getRepository(Contrato::class)->obtenerContratosPendientesSuscripcion();
        }
        $configuracion=$this->em->getRepository(Configuracion::class)->find(1);
        $virtualpos = new VirtualPos(
                                    $configuracion->getVirtualPosApiKey(),
                                    $configuracion->getVirtualPosSecretKey(),
                                    $configuracion->getVirtualPosPlan(),
                                    $configuracion->getVirtualPosUrl());
       
        
        foreach ($contratos as $contrato) {
            $response = $virtualpos->recuperarSuscripcion($contrato->getSuscripcionId());
            if($response["suscription"]["status"]=="ACTIVA"){
                $cuotas = $this->em->getRepository(Cuota::class)->findBy(["contrato"=>$contrato, "anular"=>null],["fechaPago"=>"ASC"]);
                $charges = $response["suscription"]["charge_program"];
                //INiciamos el historico para este contrato 
                $historicoSuscripcion = new ContratoHistoricoSuscripcion();
                $historicoSuscripcion->setContrato($contrato);
                $historicoSuscripcion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                $historicoSuscripcion->setExito(true);
                $historicoSuscripcion->setTipo(1);
                $observacionHistorico="<h3>Re-validando Suscripción</h3>";
                foreach ($cuotas as $cuota) {                
                    //Iniciamos el log de virtualpos
                    $virtualPosLogCuota = new VirtualPosLog();
                    $virtualPosLogCuota->setExito(1);
                    $virtualPosLogCuota->setContrato($contrato);
                    $virtualPosLogCuota->setFechaRegistro(new \DateTime(date("Y-m-d")));
                    $observacionHistorico.="<p> Verificando cuota N° ".$cuota->getNumero()."</p> <ul>";
                    $virtualPosLogCuota->setResponse("-");                        
                            $virtualPosLogCuota->setRequest("-");
                    if($cuota->getInvoiceId()==null){
                        //Reviso primero las cuotas que se encuentren sin envoiceId.
                        $existeCuota=$this->existeCuotaEnVirtualPos($charges,$cuota);
                        
                        
                                                 
                        if($existeCuota["existeCuota"]==0){
                            $response_cuotas=$virtualpos->crearCuota($cuota,$contrato->getSuscripcionId());
                            $cuota->setInvoiceId($response_cuotas["response"]["charge"]["id"]);
                           
                            $virtualPosLogCuota->setResponse(json_encode($response_cuotas["response"]));
                            $virtualPosLogCuota->setRequest($response_cuotas["request"]);

                            $observacionHistorico.="<li>Encontramos cuota en VirtualPos, se actualiza su id</li>";
                        }else{
                            $cuota->setInvoiceId($existeCuota["invoiceId"]);    
                            $virtualPosLogCuota->setResponse("-");                        
                            $virtualPosLogCuota->setRequest("-");
                            $observacionHistorico.="<li>Cuota se encuentra correcta en VirtualPos, no se actualiza su id</li>";
                        } 
                        $this->entityManager->persist($cuota);
                        $this->entityManager->flush();
                       
                       
                       
                    }else{
                        //Ahora debemos verificar que las cuotas que si tienen invoiceID, se encuentren en Virtual pos 
                        //Luego si la condicion es valida, debemos comparar si charge se encuentra en pendientes, pagadas , procesando o rechazado
                        $existeCuotaEnVirtualPos=0;
                        try{
                            $response_cuota=$virtualpos->recuperarCuota($cuota->getInvoiceId());
                            $existeCuotaEnVirtualPos=1;

                        }catch(Exception $e){
                            //Si nos da un error, quiere decir que la cuota no se encuentra en VirtualPos, por lo que debemos ubicar cual es la cuota que la tiene.
                           $existeCuotaEnVirtualPos=0;
                        }      
                        if($existeCuotaEnVirtualPos==1){
                            
                            //Consultamos si el cargo se encuentra cancelado.
                            if(!in_array($response_cuota["response"]["charge"]["status"], $this->estadosValidos)){
                                //Si la cuota se encuentra en estado cancelada, verficiar que la cuota se encuentre en otro estado
                                $existeCuota=$this->existeCuotaEnVirtualPos($charges,$cuota);
                                if($existeCuota["existeCuota"]==0){
                                    $response_cuotas=$virtualpos->crearCuota($cuota,$contrato->getSuscripcionId());
                                    $cuota->setInvoiceId($response_cuotas["response"]["charge"]["id"]);
                                    $virtualPosLogCuota->setResponse(json_encode($response_cuotas["response"]));
                                    $virtualPosLogCuota->setRequest($response_cuotas["request"]);

                                    $observacionHistorico.="<li>La cuota figura como cancelada, pero <strong>no</strong> encontramos una cuota pendiente con la misma fecha de pago</li>";
                                }else{
                                    $virtualPosLogCuota->setResponse("-");
                                    $virtualPosLogCuota->setRequest("-");

                                    //si la cuota existe, asignar el id que corresponde a esa cuota en virtual pos.
                                    $cuota->setInvoiceId($existeCuota["invoiceId"]);
                                    $observacionHistorico.="<li>La cuota figura como cancelada, pero encontramos una cuota pendiente con la misma fecha de pago</li>";
                                }
                            }
                        }else{
                            $existeCuota=$this->existeCuotaEnVirtualPos($charges,$cuota);
                            if($existeCuota["existeCuota"]==0){
                                $response_cuotas=$virtualpos->crearCuota($cuota,$contrato->getSuscripcionId());
                                $virtualPosLogCuota->setResponse(json_encode($response_cuotas["response"]));
                                $virtualPosLogCuota->setRequest($response_cuotas["request"]);

                                $cuota->setInvoiceId($response_cuotas["response"]["charge"]["id"]);
                                $observacionHistorico.="<li>Esta Cuota no figura en VirtualPos, se crea una nueva cuota</li>";
                            }
                        }
                        $this->entityManager->persist($cuota);
                        $this->entityManager->flush();              
                    }

                    $observacionHistorico.="</ul>";
                    $this->entityManager->persist($virtualPosLogCuota);
                    $this->entityManager->flush();
                }
                 $contrato->setEstadoSuscripcion("ACTIVA");

                $observacionHistorico.="<h4> Revisión de cuotas pendientes en virtualPos</h4><ul>";
                foreach($charges as $charge){
                    if(in_array($charge["status"],$this->estadosValidos)){
                        $cuota = $this->em->getRepository(Cuota::class)->findOneBy(["contrato"=>$contrato,"invoiceId"=>$charge["id"], "anular"=>null],["fechaPago"=>"ASC"]);
                        if(null != $cuota){
                            $observacionHistorico.="<li><strong>La cuota N°: ".$charge["description"]." no existe en el sistema, favor informar al administrador.<strong></li>";
                            $contrato->setEstadoSuscripcion("ACTIVA_REVISAR");
                        }
                    }   
                    
                }
                $historicoSuscripcion->setObservacion($observacionHistorico);
                $historicoSuscripcion->setSuscripcionId($contrato->getSuscripcionId());
                $this->entityManager->persist($historicoSuscripcion);
                $this->entityManager->flush();

               
                $contrato->setSesionSuscripcionActiva(0);                

            }else{  
                $historicoSuscripcion = new ContratoHistoricoSuscripcion();
                $historicoSuscripcion->setContrato($contrato);
                $historicoSuscripcion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                $historicoSuscripcion->setExito(false);
                $historicoSuscripcion->setTipo(1);
                $estadosSuscripcionNoValidas=["SUSCRIPCION_FALLIDA","CANCELADA"];
                if(in_array($response["suscription"]["status"],$estadosSuscripcionNoValidas)){
                    
                    $historicoSuscripcion->setObservacion("VirtualPos indica que la suscripción esta: ".$response["suscription"]["status"]);
                    $historicoSuscripcion->setTipo(1);

                    $contrato->setEstadoSuscripcion($response["suscription"]["status"]);
                    $contrato->setSuscripcionId(null);
                    $contrato->setSesionSuscripcionActiva(0);
                }
                if($response["suscription"]["status"]=="SUSCRIBIENDO"){
                    $contrato->setEstadoSuscripcion(null);
                    $historicoSuscripcion->setObservacion("VirtualPos indica que la suscripción esta: SUSCRIBIENDO");
                }
                $this->entityManager->persist($historicoSuscripcion);
                $this->entityManager->flush();
            }
            
            $this->entityManager->persist($contrato);
            $this->entityManager->flush();
        }
        $io->success('Validacion realizada con exito!!!');
        return 0;
    }

    function existeCuotaEnVirtualPos($charges,$cuota){
        $existeCuota=0;
        $invoiceId=null;
        
        foreach ($charges as $charge) {
            if(in_array($charge["status"],$this->estadosValidos)){                               
                //Buscamos si la cuota ya estaba cargada en virtualpos y asignamos el InvoiceId
                $fechaCargo = new \DateTime($charge["charge_date"]);
                if($fechaCargo==$cuota->getFechaPago()){
                    
                    $invoiceId=$charge["id"];
                    $existeCuota=1;                                    
                }
            }
        }
        return ["existeCuota"=>$existeCuota,"invoiceId"=>$invoiceId];
    }
}
