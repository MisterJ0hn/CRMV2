<?php

namespace App\Entity;

use App\Repository\UsuarioCarteraMovimientoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UsuarioCarteraMovimientoRepository::class)
 */
class UsuarioCarteraMovimiento
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
     * @ORM\ManyToOne(targetEntity=Cartera::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $cartera;

     /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuarioRegistro;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaRegistro;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $accion;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getCartera(): ?Cartera
    {
        return $this->cartera;
    }

    public function setCartera(?Cartera $cartera): self
    {
        $this->cartera = $cartera;

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

    public function getFechaRegistro(): ?\DateTimeInterface
    {
        return $this->fechaRegistro;
    }

    public function setFechaRegistro(\DateTimeInterface $fechaRegistro): self
    {
        $this->fechaRegistro = $fechaRegistro;

        return $this;
    }

    public function getAccion(): ?string
    {
        return $this->accion;
    }

    public function setAccion(string $accion): self
    {
        $this->accion = $accion;

        return $this;
    }
}
