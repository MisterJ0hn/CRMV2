<?php

namespace App\Entity;

use App\Repository\FuncionEncuestaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FuncionEncuestaRepository::class)
 */
class FuncionEncuesta
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\OneToMany(targetEntity=FuncionRespuesta::class, mappedBy="funcionEncuesta")
     */
    private $funcionRespuestas;

    /**
     * @ORM\OneToMany(targetEntity=Encuesta::class, mappedBy="funcionEncuesta")
     */
    private $encuestas;

    public function __construct()
    {
        $this->funcionRespuestas = new ArrayCollection();
        $this->encuestas = new ArrayCollection();
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
     * @return Collection<int, FuncionRespuesta>
     */
    public function getFuncionRespuestas(): Collection
    {
        return $this->funcionRespuestas;
    }

    public function addFuncionRespuesta(FuncionRespuesta $funcionRespuesta): self
    {
        if (!$this->funcionRespuestas->contains($funcionRespuesta)) {
            $this->funcionRespuestas[] = $funcionRespuesta;
            $funcionRespuesta->setFuncionEncuesta($this);
        }

        return $this;
    }

    public function removeFuncionRespuesta(FuncionRespuesta $funcionRespuesta): self
    {
        if ($this->funcionRespuestas->removeElement($funcionRespuesta)) {
            // set the owning side to null (unless already changed)
            if ($funcionRespuesta->getFuncionEncuesta() === $this) {
                $funcionRespuesta->setFuncionEncuesta(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Encuesta>
     */
    public function getEncuestas(): Collection
    {
        return $this->encuestas;
    }

    public function addEncuesta(Encuesta $encuesta): self
    {
        if (!$this->encuestas->contains($encuesta)) {
            $this->encuestas[] = $encuesta;
            $encuesta->setFuncionEncuesta($this);
        }

        return $this;
    }

    public function removeEncuesta(Encuesta $encuesta): self
    {
        if ($this->encuestas->removeElement($encuesta)) {
            // set the owning side to null (unless already changed)
            if ($encuesta->getFuncionEncuesta() === $this) {
                $encuesta->setFuncionEncuesta(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->getNombre();
    }
}
