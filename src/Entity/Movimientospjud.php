<?php

namespace App\Entity;

use App\Repository\MovimientospjudRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MovimientospjudRepository::class)
 */
class Movimientospjud
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaCarga;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $archivo;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="movimientospjuds")
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuarioRegistro;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fecha_pjud;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getArchivo(): ?string
    {
        return $this->archivo;
    }

    public function setArchivo(string $archivo): self
    {
        $this->archivo = $archivo;

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

    public function getFechaPjud(): ?\DateTimeInterface
    {
        return $this->fecha_pjud;
    }

    public function setFechaPjud(?\DateTimeInterface $fecha_pjud): self
    {
        $this->fecha_pjud = $fecha_pjud;

        return $this;
    }
}
