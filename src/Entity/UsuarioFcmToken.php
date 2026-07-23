<?php

namespace App\Entity;

use App\Repository\UsuarioFcmTokenRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UsuarioFcmTokenRepository::class)
 */
class UsuarioFcmToken
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuario;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $token;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $plataforma;

    /**
     * @ORM\Column(type="boolean")
     */
    private $activo = true;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaRegistro;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaActualizacion;

    public function getId(): ?int { return $this->id; }

    public function getUsuario(): ?Usuario { return $this->usuario; }
    public function setUsuario(?Usuario $usuario): self { $this->usuario = $usuario; return $this; }

    public function getToken(): ?string { return $this->token; }
    public function setToken(string $token): self { $this->token = $token; return $this; }

    public function getPlataforma(): ?string { return $this->plataforma; }
    public function setPlataforma(?string $plataforma): self { $this->plataforma = $plataforma; return $this; }

    public function getActivo(): bool { return $this->activo; }
    public function setActivo(bool $activo): self { $this->activo = $activo; return $this; }

    public function getFechaRegistro(): ?\DateTimeInterface { return $this->fechaRegistro; }
    public function setFechaRegistro(\DateTimeInterface $fechaRegistro): self { $this->fechaRegistro = $fechaRegistro; return $this; }

    public function getFechaActualizacion(): ?\DateTimeInterface { return $this->fechaActualizacion; }
    public function setFechaActualizacion(?\DateTimeInterface $fechaActualizacion): self { $this->fechaActualizacion = $fechaActualizacion; return $this; }
}
