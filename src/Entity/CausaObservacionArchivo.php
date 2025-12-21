<?php

namespace App\Entity;

use App\Repository\CausaObservacionArchivoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CausaObservacionArchivoRepository::class)
 */
class CausaObservacionArchivo
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
    private $nombreArchivo;

    /**
     * @ORM\ManyToOne(targetEntity=CausaObservacion::class, inversedBy="causaObservacionArchivos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $causaObservacion;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombreOriginal;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreArchivo(): ?string
    {
        return $this->nombreArchivo;
    }

    public function setNombreArchivo(string $nombreArchivo): self
    {
        $this->nombreArchivo = $nombreArchivo;

        return $this;
    }

    public function getCausaObservacion(): ?CausaObservacion
    {
        return $this->causaObservacion;
    }

    public function setCausaObservacion(?CausaObservacion $causaObservacion): self
    {
        $this->causaObservacion = $causaObservacion;

        return $this;
    }

    public function getNombreOriginal(): ?string
    {
        return $this->nombreOriginal;
    }

    public function setNombreOriginal(string $nombreOriginal): self
    {
        $this->nombreOriginal = $nombreOriginal;

        return $this;
    }
}
