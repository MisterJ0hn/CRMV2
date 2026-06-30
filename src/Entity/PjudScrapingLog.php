<?php

namespace App\Entity;

use App\Repository\PjudScrapingLogRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PjudScrapingLogRepository::class)
 */
class PjudScrapingLog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Agenda::class)
     */
    private $Agenda;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaRegistro;

    /**
     * @ORM\Column(type="text",  nullable=true, columnDefinition="LONGTEXT")
     */
    private $request;

    /**
     * @ORM\Column(type="text", nullable=true,columnDefinition="LONGTEXT")
     */
    private $response;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $exito;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgenda(): ?Agenda
    {
        return $this->Agenda;
    }

    public function setAgenda(?Agenda $Agenda): self
    {
        $this->Agenda = $Agenda;

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

    public function getRequest(): ?string
    {
        return $this->request;
    }

    public function setRequest(?string $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse(?string $response): self
    {
        $this->response = $response;

        return $this;
    }

    public function getExito(): ?bool
    {
        return $this->exito;
    }

    public function setExito(?bool $exito): self
    {
        $this->exito = $exito;

        return $this;
    }
}
