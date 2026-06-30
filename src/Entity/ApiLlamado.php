<?php

namespace App\Entity;

use App\Repository\ApiLlamadoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ApiLlamadoRepository::class)
 */
class ApiLlamado
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Agenda::class)
     */
    private $agenda;

    /**
     * @ORM\ManyToOne(targetEntity=Causa::class)
     */
    private $causa;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $json_request;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaRegistro;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $exito;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mensajeError;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgenda(): ?Agenda
    {
        return $this->agenda;
    }

    public function setAgenda(?Agenda $agenda): self
    {
        $this->agenda = $agenda;

        return $this;
    }

    public function getCausa(): ?Causa
    {
        return $this->causa;
    }

    public function setCausa(?Causa $causa): self
    {
        $this->causa = $causa;

        return $this;
    }

    public function getJsonRequest(): ?string
    {
        return $this->json_request;
    }

    public function setJsonRequest(?string $json_request): self
    {
        $this->json_request = $json_request;

        return $this;
    }

    public function getFechaRegistro(): ?\DateTimeInterface
    {
        return $this->fechaRegistro;
    }

    public function setFechaRegistro(?\DateTimeInterface $fechaRegistro): self
    {
        $this->fechaRegistro = $fechaRegistro;

        return $this;
    }

    public function getExito(): ?bool
    {
        return $this->exito;
    }

    public function setExito(?bool $exito): self
    {
        $this->exito = $exito;

        return $this;
    }

    public function getMensajeError(): ?string
    {
        return $this->mensajeError;
    }

    public function setMensajeError(?string $mensajeError): self
    {
        $this->mensajeError = $mensajeError;

        return $this;
    }
}
