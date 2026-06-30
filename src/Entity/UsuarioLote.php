<?php

namespace App\Entity;

use App\Repository\UsuarioLoteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UsuarioLoteRepository::class)
 */
class UsuarioLote
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="usuarioLotes")
     */
    private $usuario;

    /**
     * @ORM\ManyToOne(targetEntity=Lotes::class, inversedBy="usuarioLotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $lote;

    /**
     * @ORM\ManyToOne(targetEntity=EquipoTrabajo::class)
     */
    private $equipoTrabajo;

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

    public function getLote(): ?Lotes
    {
        return $this->lote;
    }

    public function setLote(?Lotes $lote): self
    {
        $this->lote = $lote;

        return $this;
    }

    public function getEquipoTrabajo(): ?EquipoTrabajo
    {
        return $this->equipoTrabajo;
    }

    public function setEquipoTrabajo(?EquipoTrabajo $equipoTrabajo): self
    {
        $this->equipoTrabajo = $equipoTrabajo;

        return $this;
    }
}
