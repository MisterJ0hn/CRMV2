<?php

namespace App\Entity;

use App\Repository\MateriaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MateriaRepository::class)
 */
class Materia
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
     * @ORM\ManyToOne(targetEntity=Empresa::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $empresa;

    /**
     * @ORM\OneToMany(targetEntity=CuentaMateria::class, mappedBy="materia")
     */
    private $cuentaMaterias;

    /**
     * @ORM\OneToMany(targetEntity=MateriaEstrategia::class, mappedBy="materia")
     */
    private $materiaEstrategias;

    /**
     * @ORM\OneToMany(targetEntity=Cartera::class, mappedBy="materia")
     */
    private $carteras;

    public function __construct()
    {
        $this->cuentaMaterias = new ArrayCollection();
        $this->materiaEstrategias = new ArrayCollection();
        $this->carteras = new ArrayCollection();
        
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

    public function getEmpresa(): ?Empresa
    {
        return $this->empresa;
    }

    public function setEmpresa(?Empresa $empresa): self
    {
        $this->empresa = $empresa;

        return $this;
    }

    /**
     * @return Collection|CuentaMateria[]
     */
    public function getCuentaMaterias(): Collection
    {
        return $this->cuentaMaterias;
    }

    public function addCuentaMateria(CuentaMateria $cuentaMateria): self
    {
        if (!$this->cuentaMaterias->contains($cuentaMateria)) {
            $this->cuentaMaterias[] = $cuentaMateria;
            $cuentaMateria->setMateria($this);
        }

        return $this;
    }

    public function removeCuentaMateria(CuentaMateria $cuentaMateria): self
    {
        if ($this->cuentaMaterias->removeElement($cuentaMateria)) {
            // set the owning side to null (unless already changed)
            if ($cuentaMateria->getMateria() === $this) {
                $cuentaMateria->setMateria(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MateriaEstrategia[]
     */
    public function getMateriaEstrategias(): Collection
    {
        return $this->materiaEstrategias;
    }

    public function addMateriaEstrategia(MateriaEstrategia $materiaEstrategia): self
    {
        if (!$this->materiaEstrategias->contains($materiaEstrategia)) {
            $this->materiaEstrategias[] = $materiaEstrategia;
            $materiaEstrategia->setMateria($this);
        }

        return $this;
    }

    public function removeMateriaEstrategia(MateriaEstrategia $materiaEstrategia): self
    {
        if ($this->materiaEstrategias->removeElement($materiaEstrategia)) {
            // set the owning side to null (unless already changed)
            if ($materiaEstrategia->getMateria() === $this) {
                $materiaEstrategia->setMateria(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->getNombre();
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
            $cartera->setMateria($this);
        }

        return $this;
    }

    public function removeCartera(Cartera $cartera): self
    {
        if ($this->carteras->removeElement($cartera)) {
            // set the owning side to null (unless already changed)
            if ($cartera->getMateria() === $this) {
                $cartera->setMateria(null);
            }
        }

        return $this;
    }
}
