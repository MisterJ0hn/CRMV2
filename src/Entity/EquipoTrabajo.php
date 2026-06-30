<?php

namespace App\Entity;

use App\Repository\EquipoTrabajoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EquipoTrabajoRepository::class)
 */
class EquipoTrabajo
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
     * @ORM\OneToMany(targetEntity=EquipoTrabajoVencimiento::class, mappedBy="equipoTrabajo")
     */
    private $equipoTrabajoVencimientos;

    /**
     * @ORM\OneToMany(targetEntity=EquipoTrabajoUsuario::class, mappedBy="equipoTrabajo")
     */
    private $equipoTrabajoUsuarios;

    public function __construct()
    {
        $this->equipoTrabajoVencimientos = new ArrayCollection();
        $this->equipoTrabajoUsuarios = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, EquipoTrabajoVencimiento>
     */
    public function getEquipoTrabajoVencimientos(): Collection
    {
        return $this->equipoTrabajoVencimientos;
    }

    public function addEquipoTrabajoVencimiento(EquipoTrabajoVencimiento $equipoTrabajoVencimiento): self
    {
        if (!$this->equipoTrabajoVencimientos->contains($equipoTrabajoVencimiento)) {
            $this->equipoTrabajoVencimientos[] = $equipoTrabajoVencimiento;
            $equipoTrabajoVencimiento->setEquipoTrabajo($this);
        }

        return $this;
    }

    public function removeEquipoTrabajoVencimiento(EquipoTrabajoVencimiento $equipoTrabajoVencimiento): self
    {
        if ($this->equipoTrabajoVencimientos->removeElement($equipoTrabajoVencimiento)) {
            // set the owning side to null (unless already changed)
            if ($equipoTrabajoVencimiento->getEquipoTrabajo() === $this) {
                $equipoTrabajoVencimiento->setEquipoTrabajo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EquipoTrabajoUsuario>
     */
    public function getEquipoTrabajoUsuarios(): Collection
    {
        return $this->equipoTrabajoUsuarios;
    }

    public function addEquipoTrabajoUsuario(EquipoTrabajoUsuario $equipoTrabajoUsuario): self
    {
        if (!$this->equipoTrabajoUsuarios->contains($equipoTrabajoUsuario)) {
            $this->equipoTrabajoUsuarios[] = $equipoTrabajoUsuario;
            $equipoTrabajoUsuario->setEquipoTrabajo($this);
        }

        return $this;
    }

    public function removeEquipoTrabajoUsuario(EquipoTrabajoUsuario $equipoTrabajoUsuario): self
    {
        if ($this->equipoTrabajoUsuarios->removeElement($equipoTrabajoUsuario)) {
            // set the owning side to null (unless already changed)
            if ($equipoTrabajoUsuario->getEquipoTrabajo() === $this) {
                $equipoTrabajoUsuario->setEquipoTrabajo(null);
            }
        }

        return $this;
    }
}
