<?php

namespace App\Entity;

use App\Repository\CarteraRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CarteraRepository::class)
 */
class Cartera
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
    private $nombre;

    /**
     * @ORM\Column(type="boolean")
     */
    private $estado;

    /**
     * @ORM\Column(type="integer")
     */
    private $orden;

    /**
     * @ORM\ManyToOne(targetEntity=Materia::class, inversedBy="carteras")
     * @ORM\JoinColumn(nullable=false)
     */
    private $materia;

    /**
     * @ORM\Column(type="boolean")
     */
    private $utilizado;

    /**
     * @ORM\Column(type="boolean")
     */
    private $asignado;

    /**
     * @ORM\OneToMany(targetEntity=UsuarioCartera::class, mappedBy="cartera")
     */
    private $usuarioCarteras;

    public function __construct()
    {
        $this->usuarioCarteras = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCuentaMateria(): ?CuentaMateria
    {
        return $this->cuentaMateria;
    }

    public function setCuentaMateria(?CuentaMateria $cuentaMateria): self
    {
        $this->cuentaMateria = $cuentaMateria;

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

    public function getEstado(): ?bool
    {
        return $this->estado;
    }

    public function setEstado(bool $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getOrden(): ?int
    {
        return $this->orden;
    }

    public function setOrden(int $orden): self
    {
        $this->orden = $orden;

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

    public function getUtilizado(): ?bool
    {
        return $this->utilizado;
    }

    public function setUtilizado(bool $utilizado): self
    {
        $this->utilizado = $utilizado;

        return $this;
    }

    public function getAsignado(): ?bool
    {
        return $this->asignado;
    }

    public function setAsignado(bool $asignado): self
    {
        $this->asignado = $asignado;

        return $this;
    }

    /**
     * @return Collection|UsuarioCartera[]
     */
    public function getUsuarioCarteras(): Collection
    {
        return $this->usuarioCarteras;
    }

    public function addUsuarioCartera(UsuarioCartera $usuarioCartera): self
    {
        if (!$this->usuarioCarteras->contains($usuarioCartera)) {
            $this->usuarioCarteras[] = $usuarioCartera;
            $usuarioCartera->setCartera($this);
        }

        return $this;
    }

    public function removeUsuarioCartera(UsuarioCartera $usuarioCartera): self
    {
        if ($this->usuarioCarteras->removeElement($usuarioCartera)) {
            // set the owning side to null (unless already changed)
            if ($usuarioCartera->getCartera() === $this) {
                $usuarioCartera->setCartera(null);
            }
        }

        return $this;
    }
}
