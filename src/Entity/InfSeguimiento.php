<?php

namespace App\Entity;

use App\Repository\InfSeguimientoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InfSeguimientoRepository::class)
 */
class InfSeguimiento
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $fechaCarga;

    /**
     * @ORM\Column(type="integer")
     */
    private $sinAtencion;

    /**
     * @ORM\Column(type="integer")
     */
    private $a24h;

    /**
     * @ORM\Column(type="integer")
     */
    private $a48h;

    /**
     * @ORM\Column(type="integer")
     */
    private $masDe48h;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="infSeguimientos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuario;

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

    public function getSinAtencion(): ?int
    {
        return $this->sinAtencion;
    }

    public function setSinAtencion(int $sinAtencion): self
    {
        $this->sinAtencion = $sinAtencion;

        return $this;
    }

    public function getA24h(): ?int
    {
        return $this->a24h;
    }

    public function setA24h(int $a24h): self
    {
        $this->a24h = $a24h;

        return $this;
    }

    public function getA48h(): ?int
    {
        return $this->a48h;
    }

    public function setA48h(int $a48h): self
    {
        $this->a48h = $a48h;

        return $this;
    }

    public function getMasDe48h(): ?int
    {
        return $this->masDe48h;
    }

    public function setMasDe48h(int $masDe48h): self
    {
        $this->masDe48h = $masDe48h;

        return $this;
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
}
