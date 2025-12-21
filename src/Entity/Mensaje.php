<?php

namespace App\Entity;

use App\Repository\MensajeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MensajeRepository::class)
 */
class Mensaje
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=MensajeTipo::class, inversedBy="mensajes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mensajeTipo;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="mensajes")
     */
    private $usuarioRegistro;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="mensajes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuarioDestino;

    /**
     * @ORM\Column(type="datetime")
     */
    private $FechaCreacion;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaAviso;

    /**
     * @ORM\Column(type="boolean")
     */
    private $leido;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $observacion;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMensajeTipo(): ?MensajeTipo
    {
        return $this->mensajeTipo;
    }

    public function setMensajeTipo(?MensajeTipo $mensajeTipo): self
    {
        $this->mensajeTipo = $mensajeTipo;

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

    public function getUsuarioDestino(): ?Usuario
    {
        return $this->usuarioDestino;
    }

    public function setUsuarioDestino(?Usuario $usuarioDestino): self
    {
        $this->usuarioDestino = $usuarioDestino;

        return $this;
    }

    public function getFechaCreacion(): ?\DateTimeInterface
    {
        return $this->FechaCreacion;
    }

    public function setFechaCreacion(\DateTimeInterface $FechaCreacion): self
    {
        $this->FechaCreacion = $FechaCreacion;

        return $this;
    }

    public function getFechaAviso(): ?\DateTimeInterface
    {
        return $this->fechaAviso;
    }

    public function setFechaAviso(\DateTimeInterface $fechaAviso): self
    {
        $this->fechaAviso = $fechaAviso;

        return $this;
    }

    public function getLeido(): ?bool
    {
        return $this->leido;
    }

    public function setLeido(bool $leido): self
    {
        $this->leido = $leido;

        return $this;
    }

    public function getObservacion(): ?string
    {
        return $this->observacion;
    }

    public function setObservacion(string $observacion): self
    {
        $this->observacion = $observacion;

        return $this;
    }
}
