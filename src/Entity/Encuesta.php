<?php

namespace App\Entity;

use App\Repository\EncuestaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EncuestaRepository::class)
 */
class Encuesta
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $FechaCreacion;

    /**
     * @ORM\ManyToOne(targetEntity=Contrato::class, inversedBy="encuestas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $contrato;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="encuestas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuarioCreacion;

    /**
     * @ORM\OneToMany(targetEntity=EncuestaPreguntas::class, mappedBy="encuesta")
     */
    private $encuestaPreguntas;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $observacion;

    /**
     * @ORM\ManyToOne(targetEntity=FuncionEncuesta::class, inversedBy="encuestas")
     */
    private $funcionEncuesta;

    /**
     * @ORM\ManyToOne(targetEntity=FuncionRespuesta::class, inversedBy="encuestas")
     */
    private $funcionRespuesta;

    /**
     * @ORM\ManyToOne(targetEntity=EstadoEncuesta::class, inversedBy="encuestas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $estado;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaPendiente;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaCierre;

    public function __construct()
    {
        $this->encuestaPreguntas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFechaCreacion(): ?\DateTimeInterface
    {
        return $this->FechaCreacion;
    }

    public function setFechaCreacion(?\DateTimeInterface $FechaCreacion): self
    {
        $this->FechaCreacion = $FechaCreacion;

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

    public function getUsuarioCreacion(): ?Usuario
    {
        return $this->usuarioCreacion;
    }

    public function setUsuarioCreacion(?Usuario $usuarioCreacion): self
    {
        $this->usuarioCreacion = $usuarioCreacion;

        return $this;
    }

    /**
     * @return Collection<int, EncuestaPreguntas>
     */
    public function getEncuestaPreguntas(): Collection
    {
        return $this->encuestaPreguntas;
    }

    public function addEncuestaPregunta(EncuestaPreguntas $encuestaPregunta): self
    {
        if (!$this->encuestaPreguntas->contains($encuestaPregunta)) {
            $this->encuestaPreguntas[] = $encuestaPregunta;
            $encuestaPregunta->setEncuesta($this);
        }

        return $this;
    }

    public function removeEncuestaPregunta(EncuestaPreguntas $encuestaPregunta): self
    {
        if ($this->encuestaPreguntas->removeElement($encuestaPregunta)) {
            // set the owning side to null (unless already changed)
            if ($encuestaPregunta->getEncuesta() === $this) {
                $encuestaPregunta->setEncuesta(null);
            }
        }

        return $this;
    }

    public function getObservacion(): ?string
    {
        return $this->observacion;
    }

    public function setObservacion(?string $observacion): self
    {
        $this->observacion = $observacion;

        return $this;
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

    public function getFuncionRespuesta(): ?FuncionRespuesta
    {
        return $this->funcionRespuesta;
    }

    public function setFuncionRespuesta(?FuncionRespuesta $funcionRespuesta): self
    {
        $this->funcionRespuesta = $funcionRespuesta;

        return $this;
    }

    public function getEstado(): ?EstadoEncuesta
    {
        return $this->estado;
    }

    public function setEstado(?EstadoEncuesta $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getFechaPendiente(): ?\DateTimeInterface
    {
        return $this->fechaPendiente;
    }

    public function setFechaPendiente(?\DateTimeInterface $fechaPendiente): self
    {
        $this->fechaPendiente = $fechaPendiente;

        return $this;
    }

    public function getFechaCierre(): ?\DateTimeInterface
    {
        return $this->fechaCierre;
    }

    public function setFechaCierre(?\DateTimeInterface $fechaCierre): self
    {
        $this->fechaCierre = $fechaCierre;

        return $this;
    }
}
