<?php

namespace App\Entity;

use App\Repository\ContratoPagoAutomaticoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ContratoPagoAutomaticoRepository::class)
 */
class ContratoPagoAutomatico
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
