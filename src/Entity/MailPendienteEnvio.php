<?php

namespace App\Entity;

use App\Repository\MailPendienteEnvioRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * @ORM\Entity(repositoryClass=MailPendienteEnvioRepository::class)
 */
class MailPendienteEnvio
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Contrato::class)
     * @ORM\JoinColumn(name="contrato_id", referencedColumnName="id", nullable=true)
     */
    private $contrato;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaIngreso;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $enviado;
   
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setContrato(?Contrato $contrato): self
    {
        $this->contrato= $contrato;
        return $this;
    }
    public function setFechaIngreso(?\DateTimeInterface $fechaIngreso): self
    {
        $this->fechaIngreso= $fechaIngreso;
        return $this;
    }
    public function setEnviado(?bool $enviado): self
    {
        $this->enviado= $enviado;
        return $this;
    }
   
}
