<?php

namespace App\Service;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Response;
use App\Entity\AderesoLog;
use App\Entity\Agenda;
use App\Entity\MovatecLog;
use App\Entity\ResponseEnvio;
use App\Entity\ResponseLogin;
use App\Repository\ConfiguracionRepository;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Adereso{
    public $url = "https://";
    public $apiKey= "";

    private $container;
    private $configuracionRepository;

    
    public function __construct(ContainerInterface $container, ConfiguracionRepository $configuracionRepository){
        $this->container = $container;
        $this->configuracionRepository = $configuracionRepository;
        
    }
    
    public function iniciar_conversacion($agendaId){
        $entityManager=$this->container->get('doctrine')->getManager();
       
        $configuracion = $this->configuracionRepository->find(1);

        $em=$this->container->get('doctrine');
        $agenda=$em->getRepository(Agenda::class)->find($agendaId);

        $log =new AderesoLog();
        $log->setFechaRegistro(new \DateTime(date("Y-m-d H:i")));
        if($agenda){
            $log->setAgenda($agenda);
        }
        $responseEnvio = new ResponseEnvio();
                
        $client = new \GuzzleHttp\Client(); 
        try {
            if($configuracion->getAderesoUrl() != null){
                $this->url=$configuracion->getAderesoUrl();
            }
            if($configuracion->getAderesoApiKey() != null){
                $this->apiKey=$configuracion->getAderesoApiKey();
            }
           
            $body='{
                "account": "56964609256",
                "phone": "'. str_replace("+","",$agenda->getTelefonoCliente()).'",
                "department_id": null,
                "agent_email": "'.$agenda->getAgendador()->getCorreo().'",
                "should_reply_bot": false,
                "second_delay_minutes": 2,
                "third_delay_minutes": 4,
                "first_hsm": {
                    "name": "plantilla_de_prueba",
                    "parameters": []
                },
                "second_hsm": {
                    "name": "plantilla_de_prueba",
                    "parameters": []
                },
                "third_hsm": {
                    "name": "plantilla_de_prueba",
                    "parameters": []
                }
            }';
            $log->setRequest($body);
            $response = $client->request('POST',$this->url.'/messages/send-triple-hsm', 
            [
            'body'=>$body,
            'headers' =>[
                'Content-Type' => 'application/json',
                'Authorization' => 'Key '.$this->apiKey,
                ],
            ]);
           
            if($response->getStatusCode()==200){                
                $log->setExito(1);                    
                $log->setResponse($response->getBody());                            
            }else{
                $log->setExito(0);                    
                $log->setResponse($response->getBody());                            
            }
            $entityManager->persist($log);
            $entityManager->flush();        
            return $response->getBody();
        } catch (Exception $e) {
            //throw $th;
            
            $responseEnvio->setExito(0);
            $responseEnvio->setMensaje( $e->getMessage());
            $log->setExito(0);                    
            $log->setResponse($e->getMessage());
            $entityManager->persist($log);
            $entityManager->flush();      
            return $e->getMessage();
        }        
    }

    public function reasingar_ticket($agendaId)
    {
        $entityManager=$this->container->get('doctrine')->getManager();       
        $configuracion = $this->configuracionRepository->find(1);
        $em=$this->container->get('doctrine');
        $agenda=$em->getRepository(Agenda::class)->find($agendaId);
        $log =new AderesoLog();
        $log->setFechaRegistro(new \DateTime(date("Y-m-d H:i")));
        if($agenda){
            $log->setAgenda($agenda);
        }
        $responseEnvio = new ResponseEnvio();
        $client = new \GuzzleHttp\Client(); 
        try{
            if($configuracion->getAderesoUrl() != null){
                    $this->url=$configuracion->getAderesoUrl();
            }
            if($configuracion->getAderesoApiKey() != null){
                $this->apiKey=$configuracion->getAderesoApiKey();
            }
            $body='{
                    "agent_email": "'.$agenda->getAbogado()->getCorreo().'",
                    "include_manual_assignment": true,
                    "department_id": null
                    }';
            $log->setRequest($body);
            $response = $client->request('POST',$this->url.'/v2/ticket/'.$agenda->getAderesoTicketId().'/reassign/', 
            [
            'body'=>$body,
            'headers' =>[
                'Content-Type' => 'application/json',
                'Authorization' => 'Key '.$this->apiKey,
                ],
            ]);
            if($response->getStatusCode()==200){                
                $log->setExito(1);                    
                $log->setResponse($response->getBody());                            
            }else{
                $log->setExito(0);                    
                $log->setResponse($response->getBody());                            
            }
            $entityManager->persist($log);
            $entityManager->flush();        
            return $response->getBody();
        }catch(\Exception $e){
            $responseEnvio->setExito(0);
            $responseEnvio->setMensaje( $e->getMessage());
            $log->setExito(0);                    
            $log->setResponse($e->getMessage());
            $entityManager->persist($log);
            $entityManager->flush();      
            return $e->getMessage();
        }
    }
    

}