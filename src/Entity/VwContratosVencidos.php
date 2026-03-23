<?php

namespace App\Entity;

use App\Repository\VwContratosVencidosRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VwContratosVencidosRepository::class)
 */
class VwContratosVencidos
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
     * @ORM\Column(type="integer")
     */
    private $pagado;

    /**
     * @ORM\Column(type="integer")
     */
    private $vip;

    /**
     * @ORM\Column(type="integer")
     */
    private $diasUltObservacion;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $vigenciaContrato;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $vigenciaAnexo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $folio;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaCreacion;

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

    public function getPagado(): ?int
    {
        return $this->pagado;
    }

    public function setPagado(int $pagado): self
    {
        $this->pagado = $pagado;

        return $this;
    }

    public function getVip(): ?int
    {
        return $this->vip;
    }

    public function setVip(int $vip): self
    {
        $this->vip = $vip;

        return $this;
    }

    public function getDiasUltObservacion(): ?int
    {
        return $this->diasUltObservacion;
    }

    public function setDiasUltObservacion(int $diasUltObservacion): self
    {
        $this->diasUltObservacion = $diasUltObservacion;

        return $this;
    }

    public function getVigenciaContrato(): ?int
    {
        return $this->vigenciaContrato;
    }

    public function setVigenciaContrato(?int $vigenciaContrato): self
    {
        $this->vigenciaContrato = $vigenciaContrato;

        return $this;
    }

    public function getVigenciaAnexo(): ?int
    {
        return $this->vigenciaAnexo;
    }

    public function setVigenciaAnexo(?int $vigenciaAnexo): self
    {
        $this->vigenciaAnexo = $vigenciaAnexo;

        return $this;
    }

    public function getFolio(): ?string
    {
        return $this->folio;
    }

    public function setFolio(?string $folio): self
    {
        $this->folio = $folio;

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
}
