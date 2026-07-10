<?php

namespace App\Entity;

use App\Repository\EstadoDiarioOrigenRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EstadoDiarioOrigenRepository::class)
 */
class EstadoDiarioOrigen
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
    private $usuarioCarga;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $rut;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fecha;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $guid;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nombreArchivo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaCarga;

    /**
     * @ORM\OneToMany(targetEntity=EstadoDiario::class, mappedBy="estadoDiarioOrigen", orphanRemoval=true)
     */
    private $estadoDiarios;

    public function __construct()
    {
        $this->estadoDiarios = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuarioCarga(): ?Usuario
    {
        return $this->usuarioCarga;
    }

    public function setUsuarioCarga(?Usuario $usuarioCarga): self
    {
        $this->usuarioCarga = $usuarioCarga;

        return $this;
    }

    public function getRut(): ?string
    {
        return $this->rut;
    }

    public function setRut(?string $rut): self
    {
        $this->rut = $rut;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(?\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getGuid(): ?string
    {
        return $this->guid;
    }

    public function setGuid(?string $guid): self
    {
        $this->guid = $guid;

        return $this;
    }

    public function getNombreArchivo(): ?string
    {
        return $this->nombreArchivo;
    }

    public function setNombreArchivo(?string $nombreArchivo): self
    {
        $this->nombreArchivo = $nombreArchivo;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getFechaCarga(): ?\DateTimeInterface
    {
        return $this->fechaCarga;
    }

    public function setFechaCarga(\DateTimeInterface $fechaCarga): self
    {
        $this->fechaCarga = $fechaCarga;

        return $this;
    }

    /**
     * @return Collection<int, EstadoDiario>
     */
    public function getEstadoDiarios(): Collection
    {
        return $this->estadoDiarios;
    }

    public function addEstadoDiario(EstadoDiario $estadoDiario): self
    {
        if (!$this->estadoDiarios->contains($estadoDiario)) {
            $this->estadoDiarios[] = $estadoDiario;
            $estadoDiario->setEstadoDiarioOrigen($this);
        }

        return $this;
    }

    public function removeEstadoDiario(EstadoDiario $estadoDiario): self
    {
        if ($this->estadoDiarios->removeElement($estadoDiario)) {
            if ($estadoDiario->getEstadoDiarioOrigen() === $this) {
                $estadoDiario->setEstadoDiarioOrigen(null);
            }
        }

        return $this;
    }
}
