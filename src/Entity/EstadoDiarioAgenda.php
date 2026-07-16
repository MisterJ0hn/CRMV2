<?php

namespace App\Entity;

use App\Repository\EstadoDiarioAgendaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EstadoDiarioAgendaRepository::class)
 */
class EstadoDiarioAgenda
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $detalle;

    /**
     * @ORM\ManyToOne(targetEntity=EstadoDiario::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $estadoDiario;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaHora;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $usuarioRegistro;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaHoraRegistro;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enviado = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaEnvio;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $mensajeError;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDetalle(): ?string
    {
        return $this->detalle;
    }

    public function setDetalle(string $detalle): self
    {
        $this->detalle = $detalle;

        return $this;
    }

    public function getEstadoDiario(): ?EstadoDiario
    {
        return $this->estadoDiario;
    }

    public function setEstadoDiario(?EstadoDiario $estadoDiario): self
    {
        $this->estadoDiario = $estadoDiario;

        return $this;
    }

    public function getFechaHora(): ?\DateTimeInterface
    {
        return $this->fechaHora;
    }

    public function setFechaHora(\DateTimeInterface $fechaHora): self
    {
        $this->fechaHora = $fechaHora;

        return $this;
    }

    public function getUsuarioRegistro(): ?Usuario
    {
        return $this->usuarioRegistro;
    }

    public function setUsuarioRegistro(?Usuario $usuarioRegistro): self
    {
        $this->usuarioRegistro = $usuarioRegistro;

        return $this;
    }

    public function getFechaHoraRegistro(): ?\DateTimeInterface
    {
        return $this->fechaHoraRegistro;
    }

    public function setFechaHoraRegistro(\DateTimeInterface $fechaHoraRegistro): self
    {
        $this->fechaHoraRegistro = $fechaHoraRegistro;

        return $this;
    }

    public function getEnviado(): bool
    {
        return $this->enviado;
    }

    public function setEnviado(bool $enviado): self
    {
        $this->enviado = $enviado;

        return $this;
    }

    public function getFechaEnvio(): ?\DateTimeInterface
    {
        return $this->fechaEnvio;
    }

    public function setFechaEnvio(?\DateTimeInterface $fechaEnvio): self
    {
        $this->fechaEnvio = $fechaEnvio;

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
