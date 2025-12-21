<?php

namespace App\Entity;

use App\Repository\InfAgendadosRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InfAgendadosRepository::class)
 */
class InfAgendados
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $abogado;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuario;

    /**
     * @ORM\Column(type="integer")
     */
    private $agendados;

    /**
     * @ORM\Column(type="integer")
     */
    private $prospectos;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAbogado(): ?Usuario
    {
        return $this->abogado;
    }

    public function setAbogado(?Usuario $abogado): self
    {
        $this->abogado = $abogado;

        return $this;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getAgendados(): ?int
    {
        return $this->agendados;
    }

    public function setAgendados(int $agendados): self
    {
        $this->agendados = $agendados;

        return $this;
    }

    public function getProspectos(): ?int
    {
        return $this->prospectos;
    }

    public function setProspectos(int $prospectos): self
    {
        $this->prospectos = $prospectos;

        return $this;
    }
}
