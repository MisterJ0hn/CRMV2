<?php

namespace App\Entity;

use App\Repository\ImportanciaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ImportanciaRepository::class)
 */
class Importancia
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
    private $Urgencia;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Categorizacion;

    /**
     * @ORM\OneToMany(targetEntity=Ticket::class, mappedBy="Importancia")
     */
    private $tickets;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrgencia(): ?string
    {
        return $this->Urgencia;
    }

    public function setUrgencia(string $Urgencia): self
    {
        $this->Urgencia = $Urgencia;

        return $this;
    }

    public function getCategorizacion(): ?string
    {
        return $this->Categorizacion;
    }

    public function setCategorizacion(string $Categorizacion): self
    {
        $this->Categorizacion = $Categorizacion;

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets[] = $ticket;
            $ticket->setImportancia($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getImportancia() === $this) {
                $ticket->setImportancia(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->Categorizacion;
    }
}
