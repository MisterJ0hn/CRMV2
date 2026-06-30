<?php

namespace App\Entity;

use App\Repository\CausaObservacionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CausaObservacionRepository::class)
 */
class CausaObservacion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Causa::class, inversedBy="causaObservacions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $causa;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="causaObservacions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuarioRegistro;

    /**
     * @ORM\ManyToOne(targetEntity=Contrato::class, inversedBy="causaObservacions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $contrato;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaRegistro;

    /**
     * @ORM\Column(type="text")
     */
    private $observacion;

    /**
     * @ORM\ManyToOne(targetEntity=DetalleCuaderno::class, inversedBy="causaObservaciones")
     */
    private $detalleCuaderno;

    /**
     * @ORM\OneToMany(targetEntity=CausaObservacionArchivo::class, mappedBy="causaObservacion")
     */
    private $causaObservacionArchivos;

    public function __construct()
    {
        $this->causaObservacionArchivos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCausa(): ?Causa
    {
        return $this->causa;
    }

    public function setCausa(?Causa $causa): self
    {
        $this->causa = $causa;

        return $this;
    }

    public function getUsuarioRegistro(): ?Usuario
    {
        return $this->usuarioRegistro;
    }

    public function setUsuarioRegistro(?Usuario $usuarioRegistro): self
    {
        $this->usuarioRegistro = $usuarioRegistro;

        return $this;
    }

    public function getContrato(): ?Contrato
    {
        return $this->contrato;
    }

    public function setContrato(?Contrato $contrato): self
    {
        $this->contrato = $contrato;

        return $this;
    }

    public function getFechaRegistro(): ?\DateTimeInterface
    {
        return $this->fechaRegistro;
    }

    public function setFechaRegistro(\DateTimeInterface $fechaRegistro): self
    {
        $this->fechaRegistro = $fechaRegistro;

        return $this;
    }

    public function getObservacion(): ?string
    {
        return $this->observacion;
    }

    public function setObservacion(string $observacion): self
    {
        $this->observacion = $observacion;

        return $this;
    }

    public function getDetalleCuaderno(): ?DetalleCuaderno
    {
        return $this->detalleCuaderno;
    }

    public function setDetalleCuaderno(?DetalleCuaderno $detalleCuaderno): self
    {
        $this->detalleCuaderno = $detalleCuaderno;

        return $this;
    }

    /**
     * @return Collection<int, CausaObservacionArchivo>
     */
    public function getCausaObservacionArchivos(): Collection
    {
        return $this->causaObservacionArchivos;
    }

    public function addCausaObservacionArchivo(CausaObservacionArchivo $causaObservacionArchivo): self
    {
        if (!$this->causaObservacionArchivos->contains($causaObservacionArchivo)) {
            $this->causaObservacionArchivos[] = $causaObservacionArchivo;
            $causaObservacionArchivo->setCausaObservacion($this);
        }

        return $this;
    }

    public function removeCausaObservacionArchivo(CausaObservacionArchivo $causaObservacionArchivo): self
    {
        if ($this->causaObservacionArchivos->removeElement($causaObservacionArchivo)) {
            // set the owning side to null (unless already changed)
            if ($causaObservacionArchivo->getCausaObservacion() === $this) {
                $causaObservacionArchivo->setCausaObservacion(null);
            }
        }

        return $this;
    }
}
