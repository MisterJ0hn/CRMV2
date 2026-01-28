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
    public $url = "https://api.haddacloud.com/gateway/api";
    public $apiKey= "";
    public $accountKey="04K_PH7RLR_XuhVZmojjE6q2Z4PBWiteq82GjGTe36g";
    public $opciones="";
    public $header="";
    public $user="extarnal-api@alfaromadariaga.cl";
    public $pass=".XA>7u£T]K]3ToW86>";
    public $token="";
    private $container;

    
    public function __construct(ContainerInterface $container){
        $this->container = $container;
        
    }
    public function login(){
        $entityManager=$this->container->get('doctrine')->getManager();
        
        //Agregamos log para movatec
        $log = new MovatecLog(); 
        $log->setFechaRegistro(new \DateTime(date("Y-m-d H:i")));

        $loginResponse = new ResponseLogin();
        $this->header=array(
            "Content-Type: application/json",
            "Accept: application/json");
        try{
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST',$this->url.'/v2/login/access-token-forms', [
            'body' =>'{"email":"'.$this->user.'","hashed_password":"'.$this->pass.'"}',
            'headers' =>[
                'Content-Type' => 'application/json'
                ],
            ]);
            if($response->getStatusCode()==200){
                $responseBody = json_decode($response->getBody(), true);
                if (isset($responseBody['access_token'])) {
                    
                    $loginResponse->setToken($responseBody['access_token']);
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

    public function enviarDatos(string $rit, string $competencia, string $corte, string $tribunal, string $tipoCausa, string $cliente, string $rut, string $caratulado, string $abogado, string $juzgado, string $folio){
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
        $body='{
                "rit": "'.$rit.'",
                "competencia": "'.$competencia.'",
                "corte": "'.$corte.'",
                "tribunal": "'.$tribunal.'",
                "tipoCausa": "'.$tipoCausa.'",
                "cliente": "'.$cliente.'",
                "rut": "'.$rut.'",
                "caratulado": "'.$caratulado.'",
                "abogado": "'.$abogado.'",
                "juzgado": "'.$juzgado.'",
                "folio": "'.$folio.'"
                }';
        $response = $client->request('POST',$this->url.'/v2/envio', [
            'body' =>$body,
            'headers' =>[
                'Content-Type' => 'application/json'
                ],
            ]);
            if($response->getStatusCode()==200){
                $responseBody = json_decode($response->getBody(), true);
                if (isset($responseBody['access_token'])) {
                    
                    $loginResponse->setToken($responseBody['access_token']);
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
    

}