<?php

namespace App\Entity;

use App\Repository\MensajePrioridadRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MensajePrioridadRepository::class)
 */
class MensajePrioridad
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $color;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $icono;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $colorTexto;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getIcono(): ?string
    {
        return $this->icono;
    }

    public function setIcono(?string $icono): self
    {
        $this->icono = $icono;

        return $this;
    }

    public function getColorTexto(): ?string
    {
        return $this->colorTexto;
    }

    public function setColorTexto(string $colorTexto): self
    {
        $this->colorTexto = $colorTexto;

        return $this;
    }
}
