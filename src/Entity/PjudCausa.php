<?php

namespace App\Entity;

use App\Repository\PjudCausaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PjudCausaRepository::class)
 */
class PjudCausa
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $rit;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $caratulado;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tribunalNombre;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaIngreso;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $estado;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $etapa;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalMovimientos;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalPdfs;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=PjudEbook::class, mappedBy="pjudCausa")
     */
    private $pjudEbooks;

    /**
     * @ORM\OneToMany(targetEntity=PjudMovimiento::class, mappedBy="pjudCausa")
     */
    private $pjudMovimientos;

    public function __construct()
    {
        $this->pjudEbooks = new ArrayCollection();
        $this->pjudMovimientos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRit(): ?string
    {
        return $this->rit;
    }

    public function setRit(string $rit): self
    {
        $this->rit = $rit;

        return $this;
    }

    public function getCaratulado(): ?string
    {
        return $this->caratulado;
    }

    public function setCaratulado(?string $caratulado): self
    {
        $this->caratulado = $caratulado;

        return $this;
    }

    public function getTribunalNombre(): ?string
    {
        return $this->tribunalNombre;
    }

    public function setTribunalNombre(?string $tribunalNombre): self
    {
        $this->tribunalNombre = $tribunalNombre;

        return $this;
    }

    public function getFechaIngreso(): ?\DateTimeInterface
    {
        return $this->fechaIngreso;
    }

    public function setFechaIngreso(?\DateTimeInterface $fechaIngreso): self
    {
        $this->fechaIngreso = $fechaIngreso;

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

    public function getEtapa(): ?string
    {
        return $this->etapa;
    }

    public function setEtapa(?string $etapa): self
    {
        $this->etapa = $etapa;

        return $this;
    }

    public function getTotalMovimientos(): ?int
    {
        return $this->totalMovimientos;
    }

    public function setTotalMovimientos(int $totalMovimientos): self
    {
        $this->totalMovimientos = $totalMovimientos;

        return $this;
    }

    public function getTotalPdfs(): ?int
    {
        return $this->totalPdfs;
    }

    public function setTotalPdfs(int $totalPdfs): self
    {
        $this->totalPdfs = $totalPdfs;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, PjudEbook>
     */
    public function getPjudEbooks(): Collection
    {
        return $this->pjudEbooks;
    }

    public function addPjudEbook(PjudEbook $pjudEbook): self
    {
        if (!$this->pjudEbooks->contains($pjudEbook)) {
            $this->pjudEbooks[] = $pjudEbook;
            $pjudEbook->setPjudCausa($this);
        }

        return $this;
    }

    public function removePjudEbook(PjudEbook $pjudEbook): self
    {
        if ($this->pjudEbooks->removeElement($pjudEbook)) {
            // set the owning side to null (unless already changed)
            if ($pjudEbook->getPjudCausa() === $this) {
                $pjudEbook->setPjudCausa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PjudMovimiento>
     */
    public function getPjudMovimientos(): Collection
    {
        return $this->pjudMovimientos;
    }

    public function addPjudMovimiento(PjudMovimiento $pjudMovimiento): self
    {
        if (!$this->pjudMovimientos->contains($pjudMovimiento)) {
            $this->pjudMovimientos[] = $pjudMovimiento;
            $pjudMovimiento->setPjudCausa($this);
        }

        return $this;
    }

    public function removePjudMovimiento(PjudMovimiento $pjudMovimiento): self
    {
        if ($this->pjudMovimientos->removeElement($pjudMovimiento)) {
            // set the owning side to null (unless already changed)
            if ($pjudMovimiento->getPjudCausa() === $this) {
                $pjudMovimiento->setPjudCausa(null);
            }
        }

        return $this;
    }
}
