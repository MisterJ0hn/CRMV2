<?php

namespace App\Service;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Response;
use App\Entity\Agenda;
use App\Entity\MovatecLog;
use App\Entity\ResponseEnvio;
use App\Entity\ResponseLogin;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Movatec{
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
    public function create_leads(string $token, String $customerId, String $folio, String $fecha ,String $telefono,String $agendaId){
        $entityManager=$this->container->get('doctrine')->getManager();

        $em=$this->container->get('doctrine');
        $agenda=$em->getRepository(Agenda::class)->find($agendaId);
        $log = new MovatecLog(); 
        $log->setFechaRegistro(new \DateTime(date("Y-m-d H:i")));
        if($agenda){
            $log->setAgenda($agenda);
        }

        $responseEnvio = new ResponseEnvio();
        if ($token) {
            
            $client = new \GuzzleHttp\Client(); 
            try {
                error_log('Envío datos a movatec: '.$folio.' - '.$customerId.' - '.$telefono,3,"/var/log/Moovatec_log"); 
                
                
                $telefonoarray=$this->procesarTelefono($telefono);
                $codigoArea=$telefonoarray['codigo'];
                $numero=$telefonoarray['numero'];

                $log->setRequest($this->getBody($customerId, $folio, $fecha ,$numero, $codigoArea));
                $response = $client->request('POST',$this->url.'/leads-capture/create-leads?group_campaign_id=316', 
                [
                'body' =>$this->getBody($customerId, $folio, $fecha ,$numero, $codigoArea),
                'headers' =>[
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token,
                    ],
                ]);
                
                error_log($response->getBody(),3,"/var/log/Moovatec_log"); 
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
                error_log('Error customer: '.$e->getMessage(),3,"/var/log/Moovatec_log"); 
                $responseEnvio->setExito(0);
                $responseEnvio->setMensaje( $e->getMessage());
                $log->setExito(0);                    
                    $log->setResponse($e->getMessage());
                 $entityManager->persist($log);
                $entityManager->flush();      
                return $e->getMessage();
            }
        } else {
            // Manejar el error de inicio de sesión
           
            error_log('Login erroneo ',3,"/var/log/Moovatec_log"); 
            
            $log->setExito(0);                    
                    $log->setResponse("Login erroneo");
                     $entityManager->persist($log);
                $entityManager->flush();      
            return "Login erroneo";
        }
    
        
    }

    public function getBody(String $customerId, String $folio, String $fecha ,String $telefono, String $codigoArea){
        $body = '{
            "client": [
                {
                    "customer_id": "'.$customerId.'",
                    "nombre": "",
                    "direccion": "",
                    "genero": "",
                    "fecha_nacimiento": "",
                    "actividad_profesion": "",
                    "comuna_part": "",
                    "direccion_part": "",
                    "region_part": "",
                    "comuna_com": "",
                    "direccion_com": "",
                    "region_com": "",
                    "codigo_postal": ""
                }
            ],
            "phone": [
                {
                    "customer_id": "'.$customerId.'",
                    "phone": "'.$telefono.'",
                    "cod_area": "'.$codigoArea.'",
                    "phone_type": "TT",
                    "rank": "",
                    "owner": ""
                }
            ],
            "emails": [
                {
                    "customer_id": "'.$customerId.'",
                    "email": "",
                    "email_type": "",
                    "category": "",
                    "priority": 0
                }
            ],
            "debts": [
                    {
                        "customer_id": "'.$customerId.'",
                        "lists_id": 0,
                        "cuenta": "",
                        "n_doc": "",
                        "tipo_cuenta": "",
                        "fecha_vcto": "",
                        "dias_mora": "",
                        "ano_castigo": "",
                        "n_cuota": "",
                        "interes_mora": "",
                        "interes_virtual": "",
                        "gastos_cobranza": "",
                        "deuda_mora": "",
                        "deuda_facturada": "",
                        "deuda_total": "",
                        "saldo_insoluto": "",
                        "valor_cuota": "",
                        "pago_minimo": "",
                        "pago_menor": "",
                        "monto_oferta": "",
                        "tramo_mora": "",
                        "avance_efectivo": "",
                        "cupo_autorizado": "",
                        "cupo_utilizado": "",
                        "campaign_id": ""
                    }
                ]
            }';
        return $body;
    }
    function procesarTelefono(string $telefono): array
    {
        // 1️⃣ Eliminar espacios y caracteres no numéricos excepto el +
        $telefono = preg_replace('/\s+/', '', $telefono);

        $codigo = null;
        $numero = $telefono;

        // 2️⃣ Caso A: si comienza con +56
        if (strpos($telefono, '+56') === 0) {
            $codigo = '56';
            $numero = substr($telefono, 3); // quitar +56
        }
        // 3️⃣ Caso B: si comienza con 56 y mide 11 dígitos
        elseif (strpos($telefono, '56') === 0 && strlen($telefono) === 11) {
            $codigo = '56';
            $numero = substr($telefono, 2); // quitar 56
        }
        // 4️⃣ Caso C: si mide 9 dígitos → asumir código 56 (Chile)
        elseif (strlen($telefono) === 9) {
            $codigo = '56';
            $numero = $telefono;
        }

        return [
            'codigo' => $codigo,
            'numero' => $numero
        ];
    }

}