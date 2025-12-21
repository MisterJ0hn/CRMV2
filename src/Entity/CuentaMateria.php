<?php

namespace App\Entity;

use App\Repository\CuentaMateriaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CuentaMateriaRepository::class)
 */
class CuentaMateria
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Cuenta::class, inversedBy="cuentaMaterias")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cuenta;

    /**
     * @ORM\ManyToOne(targetEntity=Materia::class, inversedBy="cuentaMaterias")
     * @ORM\JoinColumn(nullable=false)
     */
    private $materia;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $estado;

    /**
     * @ORM\OneToMany(targetEntity=Cartera::class, mappedBy="cuentaMateria")
     */
    private $carteras;

    public function __construct()
    {
        $this->carteras = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCuenta(): ?Cuenta
    {
        return $this->cuenta;
    }

    public function setCuenta(?Cuenta $cuenta): self
    {
        $this->cuenta = $cuenta;

        return $this;
    }

    public function getMateria(): ?Materia
    {
        return $this->materia;
    }

    public function setMateria(?Materia $materia): self
    {
        $this->materia = $materia;

        return $this;
    }

    public function getEstado(): ?bool
    {
        return $this->estado;
    }

    public function setEstado(?bool $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * @return Collection|Cartera[]
     */
    public function getCarteras(): Collection
    {
        return $this->carteras;
    }

    public function addCartera(Cartera $cartera): self
    {
        if (!$this->carteras->contains($cartera)) {
            $this->carteras[] = $cartera;
            $cartera->setCuentaMateria($this);
        }

        return $this;
    }

    public function removeCartera(Cartera $cartera): self
    {
        if ($this->carteras->removeElement($cartera)) {
            // set the owning side to null (unless already changed)
            if ($cartera->getCuentaMateria() === $this) {
                $cartera->setCuentaMateria(null);
            }
        }

        return $this;
    }
}
