<?php

namespace App\Entity;

use App\Repository\EncuestaPreguntasRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EncuestaPreguntasRepository::class)
 */
class EncuestaPreguntas
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Encuesta::class, inversedBy="encuestaPreguntas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $encuesta;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pregunta;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nota;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $respuestaAbierta;

    /**
     * @ORM\Column(type="integer")
     */
    private $tipoPregunta;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEncuesta(): ?Encuesta
    {
        return $this->encuesta;
    }

    public function setEncuesta(?Encuesta $encuesta): self
    {
        $this->encuesta = $encuesta;

        return $this;
    }

    public function getPregunta(): ?string
    {
        return $this->pregunta;
    }

    public function setPregunta(string $pregunta): self
    {
        $this->pregunta = $pregunta;

        return $this;
    }

    public function getNota(): ?string
    {
        return $this->nota;
    }

    public function setNota(?string $nota): self
    {
        $this->nota = $nota;

        return $this;
    }

    public function getRespuestaAbierta(): ?string
    {
        return $this->respuestaAbierta;
    }

    public function setRespuestaAbierta(?string $respuestaAbierta): self
    {
        $this->respuestaAbierta = $respuestaAbierta;

        return $this;
    }

    public function getTipoPregunta(): ?int
    {
        return $this->tipoPregunta;
    }

    public function setTipoPregunta(int $tipoPregunta): self
    {
        $this->tipoPregunta = $tipoPregunta;

        return $this;
    }
}
