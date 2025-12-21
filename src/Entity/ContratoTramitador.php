<?php

namespace App\Entity;

use App\Repository\ContratoTramitadorRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ContratoTramitadorRepository::class)
 */
class ContratoTramitador
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
    private $tramitadores;

    /**
     * @ORM\ManyToOne(targetEntity=Contrato::class, inversedBy="contratoTramitadores")
     * @ORM\JoinColumn(nullable=false)
     */
    private $contrato;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTramitadores(): ?Usuario
    {
        return $this->tramitadores;
    }

    public function setTramitadores(?Usuario $tramitadores): self
    {
        $this->tramitadores = $tramitadores;

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
}
