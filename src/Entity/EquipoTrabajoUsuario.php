<?php

namespace App\Entity;

use App\Repository\EquipoTrabajoUsuarioRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EquipoTrabajoUsuarioRepository::class)
 */
class EquipoTrabajoUsuario
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=EquipoTrabajo::class, inversedBy="equipoTrabajoUsuarios")
     */
    private $equipoTrabajo;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     */
    private $usuario;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUsuario(): ?usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?usuario $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }
}
