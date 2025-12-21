<?php

namespace App\Entity;

use App\Repository\MeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MeeRepository::class)
 */
class Mee
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=MateriaEstrategia::class, inversedBy="mees")
     * @ORM\JoinColumn(nullable=false)
     */
    private $materiaEstrategia;

    /**
     * @ORM\ManyToOne(targetEntity=Escritura::class, inversedBy="mees")
     * @ORM\JoinColumn(nullable=false)
     */
    private $escritura;

    /**
     * @ORM\OneToMany(targetEntity=ContratoMee::class, mappedBy="mee")
     */
    private $contratoMees;

    public function __construct()
    {
        $this->contratoMees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMateriaEstrategia(): ?MateriaEstrategia
    {
        return $this->materiaEstrategia;
    }

    public function setMateriaEstrategia(?MateriaEstrategia $materiaEstrategia): self
    {
        $this->materiaEstrategia = $materiaEstrategia;

        return $this;
    }

    public function getEscritura(): ?Escritura
    {
        return $this->escritura;
    }

    public function setEscritura(?Escritura $escritura): self
    {
        $this->escritura = $escritura;

        return $this;
    }

    /**
     * @return Collection|ContratoMee[]
     */
    public function getContratoMees(): Collection
    {
        return $this->contratoMees;
    }

    public function addContratoMee(ContratoMee $contratoMee): self
    {
        if (!$this->contratoMees->contains($contratoMee)) {
            $this->contratoMees[] = $contratoMee;
            $contratoMee->setMee($this);
        }

        return $this;
    }

    public function removeContratoMee(ContratoMee $contratoMee): self
    {
        if ($this->contratoMees->removeElement($contratoMee)) {
            // set the owning side to null (unless already changed)
            if ($contratoMee->getMee() === $this) {
                $contratoMee->setMee(null);
            }
        }

        return $this;
    }
}
