<?php

namespace App\Entity;

use App\Repository\EstadoDiarioRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EstadoDiarioRepository::class)
 */
class EstadoDiario
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=EstadoDiarioOrigen::class, inversedBy="estadoDiarios")
     * @ORM\JoinColumn(nullable=false)
     */
    private $estadoDiarioOrigen;

    /**
     * @ORM\ManyToOne(targetEntity=Jurisdiccion::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $jurisdiccion;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $rol;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $rolUnico;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fechaIngreso;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $caratulado;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tribunal;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $estado;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $tipoCausa;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ubicacion;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fechaUbicacion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $corte;

    /**
     * @ORM\Column(type="boolean")
     */
    private $leido = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaLeido;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $usuarioLeido;

    /**
     * @ORM\Column(type="boolean")
     */
    private $pendiente = false;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $nivelPendiente;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaPendiente;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $usuarioPendiente;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEstadoDiarioOrigen(): ?EstadoDiarioOrigen
    {
        return $this->estadoDiarioOrigen;
    }

    public function setEstadoDiarioOrigen(?EstadoDiarioOrigen $estadoDiarioOrigen): self
    {
        $this->estadoDiarioOrigen = $estadoDiarioOrigen;

        return $this;
    }

    public function getJurisdiccion(): ?Jurisdiccion
    {
        return $this->jurisdiccion;
    }

    public function setJurisdiccion(?Jurisdiccion $jurisdiccion): self
    {
        $this->jurisdiccion = $jurisdiccion;

        return $this;
    }

    public function getRol(): ?string
    {
        return $this->rol;
    }

    public function setRol(?string $rol): self
    {
        $this->rol = $rol;

        return $this;
    }

    public function getRolUnico(): ?string
    {
        return $this->rolUnico;
    }

    public function setRolUnico(?string $rolUnico): self
    {
        $this->rolUnico = $rolUnico;

        return $this;
    }

    public function getFechaIngreso(): ?\DateTimeInterface
    {
        return $this->fechaIngreso;
    }

    public function setFechaIngreso(?\DateTimeInterface $fechaIngreso): self
    {
        $this->fechaIngreso = $fechaIngreso;

        return $this;
    }

    public function getCaratulado(): ?string
    {
        return $this->caratulado;
    }

    public function setCaratulado(?string $caratulado): self
    {
        $this->caratulado = $caratulado;

        return $this;
    }

    public function getTribunal(): ?string
    {
        return $this->tribunal;
    }

    public function setTribunal(?string $tribunal): self
    {
        $this->tribunal = $tribunal;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(?string $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getTipoCausa(): ?string
    {
        return $this->tipoCausa;
    }

    public function setTipoCausa(?string $tipoCausa): self
    {
        $this->tipoCausa = $tipoCausa;

        return $this;
    }

    public function getUbicacion(): ?string
    {
        return $this->ubicacion;
    }

    public function setUbicacion(?string $ubicacion): self
    {
        $this->ubicacion = $ubicacion;

        return $this;
    }

    public function getFechaUbicacion(): ?\DateTimeInterface
    {
        return $this->fechaUbicacion;
    }

    public function setFechaUbicacion(?\DateTimeInterface $fechaUbicacion): self
    {
        $this->fechaUbicacion = $fechaUbicacion;

        return $this;
    }

    public function getCorte(): ?string
    {
        return $this->corte;
    }

    public function setCorte(?string $corte): self
    {
        $this->corte = $corte;

        return $this;
    }

    public function getLeido(): bool
    {
        return $this->leido;
    }

    public function setLeido(bool $leido): self
    {
        $this->leido = $leido;

        return $this;
    }

    public function getFechaLeido(): ?\DateTimeInterface
    {
        return $this->fechaLeido;
    }

    public function setFechaLeido(?\DateTimeInterface $fechaLeido): self
    {
        $this->fechaLeido = $fechaLeido;

        return $this;
    }

    public function getUsuarioLeido(): ?Usuario
    {
        return $this->usuarioLeido;
    }

    public function setUsuarioLeido(?Usuario $usuarioLeido): self
    {
        $this->usuarioLeido = $usuarioLeido;

        return $this;
    }

    public function getPendiente(): bool
    {
        return $this->pendiente;
    }

    public function setPendiente(bool $pendiente): self
    {
        $this->pendiente = $pendiente;

        return $this;
    }

    public function getNivelPendiente(): ?string
    {
        return $this->nivelPendiente;
    }

    public function setNivelPendiente(?string $nivelPendiente): self
    {
        $this->nivelPendiente = $nivelPendiente;

        return $this;
    }

    public function getFechaPendiente(): ?\DateTimeInterface
    {
        return $this->fechaPendiente;
    }

    public function setFechaPendiente(?\DateTimeInterface $fechaPendiente): self
    {
        $this->fechaPendiente = $fechaPendiente;

        return $this;
    }

    public function getUsuarioPendiente(): ?Usuario
    {
        return $this->usuarioPendiente;
    }

    public function setUsuarioPendiente(?Usuario $usuarioPendiente): self
    {
        $this->usuarioPendiente = $usuarioPendiente;

        return $this;
    }
}
