<?php

namespace App\Entity;

use App\Repository\UsuarioGrupoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UsuarioGrupoRepository::class)
 */
class UsuarioGrupo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="usuarioGrupos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Usuario;

    /**
     * @ORM\ManyToOne(targetEntity=Grupo::class, inversedBy="usuarioGrupos")
     */
    private $grupo;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->Usuario;
    }

    public function setUsuario(?Usuario $Usuario): self
    {
        $this->Usuario = $Usuario;

        return $this;
    }

    public function getGrupo(): ?Grupo
    {
        return $this->grupo;
    }

    public function setGrupo(?Grupo $grupo): self
    {
        $this->grupo = $grupo;

        return $this;
    }
}
