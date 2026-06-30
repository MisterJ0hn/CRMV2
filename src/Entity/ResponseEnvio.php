<?php
namespace App\Entity;

class ResponseEnvio {
    private $response;
    private $exito;
    private $mensaje;
    public function setExito( $exito)
    {
        $this->exito = $exito;
    } 

    

    public function getExito()
    {
        return $this->exito;
    }
    public function getMensaje()
    {
        return $this->mensaje;
    }

    public function setMensaje( $mensaje)
    {
        $this->mensaje = $mensaje;
    } 
    public function setResponse($response) {
        $this->response = $response;
    }

    public function getResponse() {
        return $this->response;
    }
}