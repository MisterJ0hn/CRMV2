<?php

namespace App\Entity;

use App\Repository\TicketTipoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TicketTipoRepository::class)
 */
class TicketTipo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $nombre;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $orden;
    /**
     * @ORM\Column(type="string", length=20)
     */
    private $colorBadge;


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

    public function getOrden(): ?int
    {
        return $this->orden;
    }

    public function setOrden(?int $orden): self
    {
        $this->orden = $orden;

        return $this;
    }

    public function getColorBadge(): ?string
    {
        return $this->colorBadge;
    }

    public function setColorBadge(string $colorBadge): self
    {
        $this->colorBadge = $colorBadge;

        return $this;
    }

}
