<?php

namespace App\Entity;

use App\Repository\UserActivityLogRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserActivityLogRepository::class)
 * @ORM\Table(name="user_activity_log")
 */
class UserActivityLog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $usuario;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $metodo;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $ruta;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $controlador;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $ip;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $statusCode;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=7, nullable=true)
     */
    private $latitud;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=7, nullable=true)
     */
    private $longitud;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $ciudad;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $pais;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaRegistro;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function getMetodo(): ?string
    {
        return $this->metodo;
    }

    public function setMetodo(string $metodo): self
    {
        $this->metodo = $metodo;
        return $this;
    }

    public function getRuta(): ?string
    {
        return $this->ruta;
    }

    public function setRuta(string $ruta): self
    {
        $this->ruta = $ruta;
        return $this;
    }

    public function getControlador(): ?string
    {
        return $this->controlador;
    }

    public function setControlador(?string $controlador): self
    {
        $this->controlador = $controlador;
        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;
        return $this;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(?int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getLatitud(): ?string
    {
        return $this->latitud;
    }

    public function setLatitud(?string $latitud): self
    {
        $this->latitud = $latitud;
        return $this;
    }

    public function getLongitud(): ?string
    {
        return $this->longitud;
    }

    public function setLongitud(?string $longitud): self
    {
        $this->longitud = $longitud;
        return $this;
    }

    public function getCiudad(): ?string
    {
        return $this->ciudad;
    }

    public function setCiudad(?string $ciudad): self
    {
        $this->ciudad = $ciudad;
        return $this;
    }

    public function getPais(): ?string
    {
        return $this->pais;
    }

    public function setPais(?string $pais): self
    {
        $this->pais = $pais;
        return $this;
    }

    public function getFechaRegistro(): ?\DateTimeInterface
    {
        return $this->fechaRegistro;
    }

    public function setFechaRegistro(\DateTimeInterface $fechaRegistro): self
    {
        $this->fechaRegistro = $fechaRegistro;
        return $this;
    }
}
