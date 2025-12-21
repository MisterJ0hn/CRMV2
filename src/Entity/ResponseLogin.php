<?php
namespace App\Entity;

class ResponseLogin {
    private $token;
    private $tokenType;
    private $exito;
    private $mensaje;
    public function setExito( $exito)
    {
        $this->exito = $exito;
    } 

    public function getMensaje()
    {
        return $this->mensaje;
    }

    public function setMensaje( $mensaje)
    {
        $this->mensaje = $mensaje;
    } 

    public function getExito()
    {
        return $this->exito;
    }

    public function setToken($token) {
        $this->token = $token;
    }

    public function getToken() {
        return $this->token;
    }

    public function setTokenType($tokenType) {
        $this->tokenType = $tokenType;
    }

    public function getTokenType() {
        return $this->tokenType;
    }
}