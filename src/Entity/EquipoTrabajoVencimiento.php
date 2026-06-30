<?php

namespace App\Entity;

use App\Repository\EquipoTrabajoVencimientoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EquipoTrabajoVencimientoRepository::class)
 */
class EquipoTrabajoVencimiento
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=EquipoTrabajo::class, inversedBy="equipoTrabajoVencimientos")
     */
    private $equipoTrabajo;

    /**
     * @ORM\ManyToOne(targetEntity=Vencimiento::class)
     */
    private $vencimiento;

   

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

    public function getVencimiento(): ?Vencimiento
    {
        return $this->vencimiento;
    }

    public function setVencimiento(?Vencimiento $vencimiento): self
    {
        $this->vencimiento = $vencimiento;

        return $this;
    }

    
}
