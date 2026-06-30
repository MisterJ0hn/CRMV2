<?php

namespace App\Entity;

use App\Repository\FuncionRespuestaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FuncionRespuestaRepository::class)
 */
class FuncionRespuesta
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=FuncionEncuesta::class, inversedBy="funcionRespuestas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $funcionEncuesta;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\OneToMany(targetEntity=Encuesta::class, mappedBy="funcionRespuesta")
     */
    private $encuestas;

    public function __construct()
    {
        $this->encuestas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFuncionEncuesta(): ?FuncionEncuesta
    {
        return $this->funcionEncuesta;
    }

    public function setFuncionEncuesta(?FuncionEncuesta $funcionEncuesta): self
    {
        $this->funcionEncuesta = $funcionEncuesta;

        return $this;
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
            $encuesta->setFuncionRespuesta($this);
        }

        return $this;
    }

    public function removeEncuesta(Encuesta $encuesta): self
    {
        if ($this->encuestas->removeElement($encuesta)) {
            // set the owning side to null (unless already changed)
            if ($encuesta->getFuncionRespuesta() === $this) {
                $encuesta->setFuncionRespuesta(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->getNombre();
    }
}
