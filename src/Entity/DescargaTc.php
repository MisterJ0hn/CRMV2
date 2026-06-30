<?php

namespace App\Entity;

use App\Repository\DescargaTcRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DescargaTcRepository::class)
 * @ORM\Table(name="temp_descarga_tc")
 */
class DescargaTc
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $folio;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaContratoAnexo;

    /**
     * @ORM\Column(type="integer")
     */
    private $anioPagado;

    /**
     * @ORM\Column(type="integer")
     */
    private $mesPagado;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaPago;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalCuota;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalMesPago;

    /**
     * @ORM\Column(type="integer", name="suma_cuot_futur_nopagadas")
     */
    private $sumaCuotFuturNopagadas;

    /**
     * @ORM\Column(type="integer")
     */
    private $montoContrato;

    /**
     * @ORM\Column(type="string", length=250)
     */
    private $tramitador;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $materia;

    /**
     * @ORM\Column(type="datetime", nullable=true, name="vencimiento_ult_cuota_no_pagada")
     */
    private $vencimientoUltCuotaNopagada;

    /**
     * @ORM\ManyToOne(targetEntity=Contrato::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $contrato;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFechaContratoAnexo(): ?\DateTimeInterface
    {
        return $this->fechaContratoAnexo;
    }

    public function setFechaContratoAnexo(\DateTimeInterface $fechaContratoAnexo): self
    {
        $this->fechaContratoAnexo = $fechaContratoAnexo;
        return $this;
    }

    public function getAnioPagado(): ?int
    {
        return $this->anioPagado;
    }

    public function setAnioPagado(int $anioPagado): self
    {
        $this->anioPagado = $anioPagado;
        return $this;
    }

    public function getMesPagado(): ?int
    {
        return $this->mesPagado;
    }

    public function setMesPagado(int $mesPagado): self
    {
        $this->mesPagado = $mesPagado;
        return $this;
    }

    public function getFechaPago(): ?\DateTimeInterface
    {
        return $this->fechaPago;
    }

    public function setFechaPago(\DateTimeInterface $fechaPago): self
    {
        $this->fechaPago = $fechaPago;
        return $this;
    }

    public function getTotalCuota(): ?int
    {
        return $this->totalCuota;
    }

    public function setTotalCuota(int $totalCuota): self
    {
        $this->totalCuota = $totalCuota;
        return $this;
    }

    public function getTotalMesPago(): ?int
    {
        return $this->totalMesPago;
    }

    public function setTotalMesPago(int $totalMesPago): self
    {
        $this->totalMesPago = $totalMesPago;
        return $this;
    }

    public function getSumaCuotFuturNopagadas(): ?int
    {
        return $this->sumaCuotFuturNopagadas;
    }

    public function setSumaCuotFuturNopagadas(int $sumaCuotFuturNopagadas): self
    {
        $this->sumaCuotFuturNopagadas = $sumaCuotFuturNopagadas;
        return $this;
    }

    public function getMontoContrato(): ?int
    {
        return $this->montoContrato;
    }

    public function setMontoContrato(int $montoContrato): self
    {
        $this->montoContrato = $montoContrato;
        return $this;
    }

    public function getTramitador(): ?string
    {
        return $this->tramitador;
    }

    public function setTramitador(string $tramitador): self
    {
        $this->tramitador = $tramitador;
        return $this;
    }

    public function getMateria(): ?string
    {
        return $this->materia;
    }

    public function setMateria(string $materia): self
    {
        $this->materia = $materia;
        return $this;
    }

    public function getVencimientoUltCuotaNopagada(): ?\DateTimeInterface
    {
        return $this->vencimientoUltCuotaNopagada;
    }

    public function setVencimientoUltCuotaNopagada(\DateTimeInterface $vencimientoUltCuotaNopagada): self
    {
        $this->vencimientoUltCuotaNopagada = $vencimientoUltCuotaNopagada;
        return $this;
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
}
