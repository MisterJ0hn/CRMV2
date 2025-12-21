<?php


namespace App\Service;

class Tramitacion{
    public $url = "https://services.leadconnectorhq.com/hooks/1KQQabuZkRa8eqxSNRt0/webhook-trigger/18343850-e8d3-472b-9e04-f39de297560a";
   
    public function __construct(){

        
    }

    public function agendarTramitacion(string $nombre,string $telefono,string $email,string $correoTramitador,string $id_ghl,string $materia,int $estado_prospecto){
        
        try{
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST',$this->url, [
            'body' =>'{"nombre":"'.$nombre.'","phone":"'.$telefono.'","email":"'.$email.'","correoTramitador":"'.$correoTramitador.'","id_ghl":"'.$id_ghl.'","materia":"'.$materia.'","estadoProspecto":"'.$estado_prospecto.'"}',
            'headers' =>[
                'Content-Type' => 'application/json'
                ],
            ]);
            if($response->getStatusCode()==200){
                return true;
                 error_log('Tramitación agendada - Id_GHL'.$id_ghl,3,"/var/log/Tramitacion_log"); 
            }else{
                error_log('Error al agendar tramitación - Id_GHL'.$id_ghl,3,"/var/log/Tramitacion_log"); 
                return false;
            }
        }catch(\Exception $e){
            error_log('Error al agendar tramitación - Id_GHL'.$id_ghl,3,"/var/log/Tramitacion_log"); 
            return false;
        }
    }
}   