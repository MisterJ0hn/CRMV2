<?php

namespace App\Entity;

use App\Repository\ApiLlamadoAdeResoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ApiLlamadoAdeResoRepository::class)
 */
class ApiLlamadoAdereso
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $telefono;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $jsonRequest;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $jsonResponse;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaRegistro;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $exito;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $mensajeError;

    public function getId(): ?int { return $this->id; }

    public function getTelefono(): ?string { return $this->telefono; }
    public function setTelefono(?string $telefono): self { $this->telefono = $telefono; return $this; }

    public function getJsonRequest(): ?string { return $this->jsonRequest; }
    public function setJsonRequest(?string $jsonRequest): self { $this->jsonRequest = $jsonRequest; return $this; }

    public function getJsonResponse(): ?string { return $this->jsonResponse; }
    public function setJsonResponse(?string $jsonResponse): self { $this->jsonResponse = $jsonResponse; return $this; }

    public function getFechaRegistro(): ?\DateTimeInterface { return $this->fechaRegistro; }
    public function setFechaRegistro(?\DateTimeInterface $fechaRegistro): self { $this->fechaRegistro = $fechaRegistro; return $this; }

    public function getExito(): ?bool { return $this->exito; }
    public function setExito(?bool $exito): self { $this->exito = $exito; return $this; }

    public function getMensajeError(): ?string { return $this->mensajeError; }
    public function setMensajeError(?string $mensajeError): self { $this->mensajeError = $mensajeError; return $this; }
}
