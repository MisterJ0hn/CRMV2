<?php

namespace App\Service;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Response;
use App\Entity\MovatecLog;
use App\Entity\PjudScrapingLog;
use App\Entity\ResponseLogin;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PjudScraping{
    public $url = "http://138.117.149.186:3000/api";
    public $apiKey= "";
    public $accountKey="04K_PH7RLR_XuhVZmojjE6q2Z4PBWiteq82GjGTe36g";
    public $opciones="";
    public $header="";
    public $user="adminti";
    public $pass="$$1212";
    public $token="";
    private $container;

    
    public function __construct(ContainerInterface $container){
        $this->container = $container;
        
    }
    public function login(){
        $entityManager=$this->container->get('doctrine')->getManager();
        
        //Agregamos log para movatec
        $log = new PjudScrapingLog(); 
        $log->setFechaRegistro(new \DateTime(date("Y-m-d H:i")));

        $loginResponse = new ResponseLogin();
        $this->header=array(
            "Content-Type: application/json",
            "Accept: application/json");
        try{
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST',$this->url.'/auth', [
            'body' =>'{"usuario":"'.$this->user.'","password":"'.$this->pass.'"}',
            'headers' =>[
                'Content-Type' => 'application/json'
                ],
            ]);
            if($response->getStatusCode()==200){
                $responseBody = json_decode($response->getBody(), true);
                if (isset($responseBody['token'])) {
                    
                    $loginResponse->setToken($responseBody['token']);
                    $loginResponse->setTokenType($responseBody['token_type'] ?? null);

                    $log->setExito(1);                    
                    $log->setResponse($response->getBody());
                    
                    $entityManager->persist($log);
                    $entityManager->flush();

                    return $loginResponse->getToken();
                }else{
                    throw new Exception($response->getBody());
                }
            }else{

                throw new Exception($response->getBody());
            }
        } catch (Exception $e) {
           
            $log->setExito(0);
                    
            $log->setResponse($e->getMessage());
            $entityManager->persist($log);
            $entityManager->flush();
            return $e->getMessage();           
        }
    }

    public function enviarDatos(string $token, string $numero,string $letra,int $anio, string $competencia, string $corte, string $tribunal,int $causaId,int $estado){
        $entityManager=$this->container->get('doctrine')->getManager();
        
        //Agregamos log para scraping
        $log = new PjudScrapingLog(); 
        $log->setFechaRegistro(new \DateTime(date("Y-m-d H:i")));
        $body='{
                        "numero":'.$numero.',
                        "letra":"'.$letra.'",
                        "anio":'.$anio.',
                        "competencia":'.$competencia.',
                        "corte":'.$corte.',
                        "tribunal":'.$tribunal.',
                        "crm_causa_id":'.$causaId.',
                        "estado_contrato":'.$estado.',
                        "skip_pdfs":false
                        }';
        $log->setRequest($body."(Bearer ".$token.")");
        try{
            if( $token){
                $client = new \GuzzleHttp\Client();
                
                $response = $client->request('POST',$this->url.'/generar_scrapping', [
                    'body' =>$body,
                    'headers' =>[
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer '.$token
                        ],
                    'timeout' => 240,
                     'connect_timeout' => 240
                    ]);
                if($response->getStatusCode()==200){
                                       
                    $log->setExito(1);                    
                    $log->setResponse($response->getBody());
                    
                    $entityManager->persist($log);
                    $entityManager->flush();

                    
                }else{

                    throw new Exception($response->getBody());
                }
            }
        } catch (Exception $e) {
           
            $log->setExito(0);
                    
            $log->setResponse($e->getMessage());
            $entityManager->persist($log);
            $entityManager->flush();
            return $e->getMessage();           
        }
        
    }
    

}