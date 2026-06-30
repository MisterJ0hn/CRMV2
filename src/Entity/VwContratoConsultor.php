<?php

namespace App\Entity;

use App\Repository\VwContratoConsultorRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VwContratoConsultorRepository::class)
 */
class VwContratoConsultor
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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $prime;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $preferente;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaCreacion;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     */
    private $consultor;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaUltimaObservacion;

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

    public function getPrime(): ?int
    {
        return $this->prime;
    }

    public function setPrime(?int $prime): self
    {
        $this->prime = $prime;

        return $this;
    }

    public function getPreferente(): ?int
    {
        return $this->preferente;
    }

    public function setPreferente(?int $preferente): self
    {
        $this->preferente = $preferente;

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

    public function getConsultor(): ?Usuario
    {
        return $this->consultor;
    }

    public function setConsultor(?Usuario $consultor): self
    {
        $this->consultor = $consultor;

        return $this;
    }

    public function getFechaUltimaObservacion(): ?\DateTimeInterface
    {
        return $this->fechaUltimaObservacion;
    }

    public function setFechaUltimaObservacion(?\DateTimeInterface $fechaUltimaObservacion): self
    {
        $this->fechaUltimaObservacion = $fechaUltimaObservacion;

        return $this;
    }
}
