<?php
// src/Entity/ApiToken.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ApiToken
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\Column(type="string", length=64, unique=true) */
    private $token;

    /** @ORM\Column(type="datetime") */
    private $expiresAt;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     */
    private $user;

    // getters/setters
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }
    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }
    public function setExpiresAt(\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }
    public function getUser(): ?Usuario
    {
        return $this->user;
    }
    public function setUser(?Usuario $user): self
    {
        $this->user = $user;

        return $this;
    }
    
}
