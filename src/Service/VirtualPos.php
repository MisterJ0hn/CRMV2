<?php
namespace App\Service;

use App\Entity\Cuota;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Firebase\JWT\JWT;

class VirtualPos{
    public $url = "https://url.com";
    public $apiKey= "Wyb1WAGsomZ6alcoaG7m83er9Ejv86hOFzdyIRFxsZM";
    public $secretKey="04K_PH7RLR_XuhVZmojjE6q2Z4PBWiteq82GjGTe36g";
    public $plan = "";
    public $opciones="";
    public $header="";

    public function __construct(string $apiKey,string $secretKey,string $plan,string $url){
       
        $this->apiKey= $apiKey;
        $this->secretKey = $secretKey;
        $this->plan = $plan;
        $this->url = $url;
    }

    public function login(){

        $payload = array();
        $payload['api_key'] = $this->apiKey;

        try{    
            $jwt_signature = JWT::encode($payload, $this->secretKey);

            return $jwt_signature;
        }catch(Exception $ex){
            throw new Exception($ex->getMessage());
        }
       
    }

    public function crearPlan(){


        $signature = $this->login();

        
        try{
            $client = new Client();
            
            $response = $client->request( "post", $this->url."/plan",[
                "json"=>[
                "name"=>"plan_".date("d-m-Y"), 
                "description"=>"plan variable para todos los contratos",             
                "currency"=> "CLP",
                "automatic_renewal"=> "F",
                "show_in_terminal"=>"F",
                "type"=> "MONTO_VARIABLE",
                "amount"=> 0,
                "num_charges"=> 0,
                "trial_days"=>0,
                "return_url"=>"url",
                "frequency_type"=> "Mensual",
                "fixed_amount_day_charge"=> "05",
                ],
                "headers"=>[
                    'Authorization'=> $this->apiKey,
                    'Signature' => $signature
                    ]
                ]
            );  

           if($response->getStatusCode()==200){
            
                $responseBody = json_decode($response->getBody(), true);
                if($responseBody["code"]==200){
                    return $responseBody["plan"];
                }else{
                    throw new Exception("ErrorCode: ".$responseBody["error_code"]." - Message: ".$responseBody["Message"]." - doc_url: ".$responseBody["doc_url"]);
                }
           }else{
                throw new Exception("Ha ocurrido un error al crear el Plan - codigoError: ".$response->getStatusCode());
           }
            
        }catch(Exception $ex){
            throw new Exception($ex->getMessage());
        }
    }

    public function crearSuscripcion(string $emailCliente, 
                                    string $rutCliente,
                                    string $nombreCliente, 
                                    string $apellidoCliente,
                                    string $telefonoCliente,
                                    array $cuotas,
                                    string $url_return,
                                    int $contratoId = 0){
        $signature = $this->login();

        $jsonCuotas=[];
        foreach ($cuotas as $cuota) {
            $jsonCuotas[]=["amount"=>$cuota->getMonto(),
                        "charge_date"=>$cuota->getFechaPago()->format("Y-m-d"),
                        "description"=>"cuota N° ".$cuota->getNumero(),
                        "internal_code"=>"'".$cuota->getId()."'"];
        }
       
        $request=["automatic_renewal"=> "F",
                "card_on_file"=> "LOCAL_ISSUER",
                "email"=> $emailCliente,
                "social_id"=> $rutCliente,
                "first_name"=> $nombreCliente,
                "last_name"=> $apellidoCliente,
                "phone_number"=> $telefonoCliente,
                "plan_id"=> $this->plan,
                "service_id"=>$contratoId,
                "charges_program"=>base64_encode(json_encode($jsonCuotas)),
                "return_url"=>base64_encode($url_return."_bad"),
                "callback_url"=>base64_encode($url_return."_async_bad")];
        
        try{
            $client = new Client();
            
            $response = $client->request( "post", $this->url."/suscription",[
                "json"=>$request,
                "headers"=>[
                    'Authorization'=> $this->apiKey,
                    'Signature' => $signature
                    ]
                ]
            );                    

           if($response->getStatusCode()==200){
                $responseBody = json_decode($response->getBody(), true);
                
                return ["suscription"=>$responseBody["suscription"],"request"=>json_encode($request),"url_redirect"=>$responseBody["url_redirect"]];
           }else{
                throw new Exception("Ha ocurrido un error al crear la suscripción - codigoError: ".$response->getStatusCode());
           }
        }catch(Exception $ex){
            throw new Exception($ex->getMessage()." request:".json_encode($request));
        }
    }

    
    public function recuperarSuscripcion(string $uuid)
    {
        $signature = $this->login();

         try{
            $client = new Client();
            
            $response = $client->request( "get", $this->url."/suscription/".$uuid,[              
                "headers"=>[
                    'Authorization'=> $this->apiKey,
                    'Signature' => $signature
                    ]
                ]
            ); 
            if($response->getStatusCode()==200){
                $responseBody = json_decode($response->getBody(), true);
                return $responseBody;
            }else{
                throw new Exception("Ha ocurrido un error al crear la suscripción - codigoError: ".$response->getStatusCode());
            }   
         }catch(Exception $ex){
            throw new Exception($ex->getMessage());
        }
    }
    public function cancelarSuscripcion(string $uuid)
    {
        $signature = $this->login();

         try{
            $client = new Client();
            
            $response = $client->request( "delete", $this->url."/suscription/".$uuid,[              
                "headers"=>[
                    'Authorization'=> $this->apiKey,
                    'Signature' => $signature
                    ]
                ]
            ); 
            if($response->getStatusCode()==200){
                $responseBody = json_decode($response->getBody(), true);
                return $responseBody;
            }else{
                throw new Exception("Ha ocurrido un error al cancelar la suscripción - codigoError: ".$response->getStatusCode());
            }   
         }catch(Exception $ex){
            throw new Exception($ex->getMessage());
        }
    }

    public function crearCuota(Cuota $cuota, string $suscripcionId){
         $signature = $this->login();
        $client = new Client();
        try{   
            $request=["amount"=>$cuota->getMonto(),
                        "charge_date"=>$cuota->getFechaPago()->format("Y-m-d"),
                        "description"=>"cuota N° ".$cuota->getNumero(),
                        "internal_code"=>$cuota->getId(),
                        "suscription_id"=> $suscripcionId];
            $response = $client->request( "post", $this->url."/charge",[
                "json"=>$request,
                "headers"=>[
                    'Authorization'=> $this->apiKey,
                    'Signature' => $signature
                    ]
                ]
            );  
            if($response->getStatusCode()==200){
                $responseBody = json_decode($response->getBody(), true);
                if($responseBody["code"]==200){
                    return ["cuota id:"=>$cuota->getNumero(),"request"=>json_encode($request),"response"=>$responseBody];
                }else{
                    throw new Exception($responseBody["error"]["message"]);
                }
            }else{
                    throw new Exception("Ha ocurrido un error al crear la cuota ".$cuota->getNumero()."  - codigoError: ".$response->getStatusCode());
            }
        }catch(\Exception $e){
            throw new Exception($e->getMessage());
        }                  
    }

    public function cancelarCargosFuturos($suscripcionId){
         $signature = $this->login();
        $client = new Client();
        try{   
            
            $response = $client->request( "delete", $this->url."/charges/".$suscripcionId,[
               
                "headers"=>[
                    'Authorization'=> $this->apiKey,
                    'Signature' => $signature
                    ]
                ]
            );  
            if($response->getStatusCode()==200){
                $responseBody = json_decode($response->getBody(), true);
                if($responseBody["code"]==200){
                    return [$responseBody];
                }else{
                    throw new Exception($responseBody["error"]["message"]);
                }
            }else{
                    throw new Exception("Ha ocurrido un error al cancelar cuotas - codigoError: ".$response->getStatusCode());
            }
        }catch(\Exception $e){
            throw new Exception($e->getMessage());
        }         
    }
    function recuperarCuota(string $chargeId){
        $signature = $this->login();

         try{
            $client = new Client();
            
            $response = $client->request( "get", $this->url."/charge/".$chargeId,[              
                "headers"=>[
                    'Authorization'=> $this->apiKey,
                    'Signature' => $signature
                    ]
                ]
            ); 
            if($response->getStatusCode()==200){
                $responseBody = json_decode($response->getBody(), true);
                
                return ["status"=>200,"request"=>"","response"=>$responseBody];
            }else{
                throw new Exception("Ha ocurrido un error al recuperar la cuota - codigoError: ".$response->getStatusCode());
            }   
         }catch(Exception $ex){
            throw new Exception($ex->getMessage());
        }
    }

    public function recuperarCuotas(string $uuid)
    {
        $signature = $this->login();

         try{
            $client = new Client();
            
            $response = $client->request( "get", $this->url."/suscription/".$uuid."/charges",[              
                "headers"=>[
                    'Authorization'=> $this->apiKey,
                    'Signature' => $signature
                    ]
                ]
            ); 
            if($response->getStatusCode()==200){
                $responseBody = json_decode($response->getBody(), true);
                return $responseBody;
            }else{
                throw new Exception("Ha ocurrido un error al recuperar las cuotas - codigoError: ".$response->getStatusCode());
            }   
         }catch(Exception $ex){
            throw new Exception($ex->getMessage());
        }
    }
    
}