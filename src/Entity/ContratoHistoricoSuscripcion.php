<?php

namespace App\Entity;

use App\Repository\ContratoHistoricoSuscripcionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ContratoHistoricoSuscripcionRepository::class)
 */
class ContratoHistoricoSuscripcion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Contrato::class, inversedBy="contratoHistoricoSuscripcions")
     */
    private $contrato;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaRegistro;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $suscripcionId;

    /**
     * @ORM\Column(type="boolean")
     */
    private $exito;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $mensajeError;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $planId;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $planName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $observacion;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tipo;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContrato(): ?Contrato
    {
        return $this->contrato;
    }

    public function setContrato(?Contrato $contrato): self
    {
        $this->contrato = $contrato;

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

    public function getSuscripcionId(): ?string
    {
        return $this->suscripcionId;
    }

    public function setSuscripcionId(?string $suscripcionId): self
    {
        $this->suscripcionId = $suscripcionId;

        return $this;
    }

    public function getExito(): ?bool
    {
        return $this->exito;
    }

    public function setExito(bool $exito): self
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

    public function getPlanId(): ?string
    {
        return $this->planId;
    }

    public function setPlanId(?string $planId): self
    {
        $this->planId = $planId;

        return $this;
    }

    public function getPlanName(): ?string
    {
        return $this->planName;
    }

    public function setPlanName(?string $planName): self
    {
        $this->planName = $planName;

        return $this;
    }

    public function getObservacion(): ?string
    {
        return $this->observacion;
    }

    public function setObservacion(?string $observacion): self
    {
        $this->observacion = $observacion;

        return $this;
    }

    public function getTipo(): ?int
    {
        return $this->tipo;
    }

    public function setTipo(?int $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }
}
