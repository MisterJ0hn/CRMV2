<?php

namespace App\Entity;

use App\Repository\VwContratoPagoAutomaticoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VwContratoPagoAutomaticoRepository::class)
 */
class VwContratoPagoAutomatico
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Contrato::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $contrato;

    /**
     * @ORM\Column(type="string", length=9)
     */
    private $estado;

    /**
     * @ORM\Column(type="date")
     */
    private $fechaVencimiento;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaPagado;

    /**
     * @ORM\Column(type="integer")
     */
    private $montoCuota;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $montoPagado;

    /**
     * @ORM\Column(type="bigint")
     */
    private $saldo;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaCreacion;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $folio;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $observacionVp;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaRegistroObservacionVp;

    /**
     * @ORM\Column(type="integer")
     */
    private $numeroCuota;

    /**
     * @ORM\Column(type="string", length=9)
     */
    private $estadoSuscripcion;

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

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getFechaVencimiento(): ?\DateTimeInterface
    {
        return $this->fechaVencimiento;
    }

    public function setFechaVencimiento(\DateTimeInterface $fechaVencimiento): self
    {
        $this->fechaVencimiento = $fechaVencimiento;

        return $this;
    }

    public function getFechaPagado(): ?\DateTimeInterface
    {
        return $this->fechaPagado;
    }

    public function setFechaPagado(?\DateTimeInterface $fechaPagado): self
    {
        $this->fechaPagado = $fechaPagado;

        return $this;
    }

    public function getMontoCuota(): ?int
    {
        return $this->montoCuota;
    }

    public function setMontoCuota(int $montoCuota): self
    {
        $this->montoCuota = $montoCuota;

        return $this;
    }

    public function getMontoPagado(): ?int
    {
        return $this->montoPagado;
    }

    public function setMontoPagado(?int $montoPagado): self
    {
        $this->montoPagado = $montoPagado;

        return $this;
    }

    public function getSaldo(): ?string
    {
        return $this->saldo;
    }

    public function setSaldo(string $saldo): self
    {
        $this->saldo = $saldo;

        return $this;
    }

    public function getFechaCreacion(): ?\DateTimeInterface
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(\DateTimeInterface $fechaCreacion): self
    {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    public function getFolio(): ?string
    {
        return $this->folio;
    }

    public function setFolio(string $folio): self
    {
        $this->folio = $folio;

        return $this;
    }

    public function getObservacionVp(): ?string
    {
        return $this->observacionVp;
    }

    public function setObservacionVp(?string $observacionVp): self
    {
        $this->observacionVp = $observacionVp;

        return $this;
    }

    public function getFechaRegistroObservacionVp(): ?\DateTimeInterface
    {
        return $this->fechaRegistroObservacionVp;
    }

    public function setFechaRegistroObservacionVp(?\DateTimeInterface $fechaRegistroObservacionVp): self
    {
        $this->fechaRegistroObservacionVp = $fechaRegistroObservacionVp;

        return $this;
    }

    public function getNumeroCuota(): ?int
    {
        return $this->numeroCuota;
    }

    public function setNumeroCuota(int $numeroCuota): self
    {
        $this->numeroCuota = $numeroCuota;

        return $this;
    }

    public function getEstadoSuscripcion(): ?string
    {
        return $this->estadoSuscripcion;
    }

    public function setEstadoSuscripcion(string $estadoSuscripcion): self
    {
        $this->estadoSuscripcion = $estadoSuscripcion;

        return $this;
    }
}
