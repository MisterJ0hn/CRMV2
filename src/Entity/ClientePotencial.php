<?php

namespace App\Entity;

use App\Repository\ClientePotencialRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClientePotencialRepository::class)
 */
class ClientePotencial
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $formId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $leadgenId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $pageId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $createdTime;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $campos = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFormId(): ?int
    {
        return $this->formId;
    }

    public function setFormId(?int $formId): self
    {
        $this->formId = $formId;

        return $this;
    }

    public function getLeadgenId(): ?int
    {
        return $this->leadgenId;
    }

    public function setLeadgenId(?int $leadgenId): self
    {
        $this->leadgenId = $leadgenId;

        return $this;
    }

    public function getPageId(): ?int
    {
        return $this->pageId;
    }

    public function setPageId(?int $pageId): self
    {
        $this->pageId = $pageId;

        return $this;
    }

    public function getCreatedTime(): ?int
    {
        return $this->createdTime;
    }

    public function setCreatedTime(?int $createdTime): self
    {
        $this->createdTime = $createdTime;

        return $this;
    }

    public function getCampos(): ?array
    {
        return $this->campos;
    }

    public function setCampos(?array $campos): self
    {
        $this->campos = $campos;

        return $this;
    }
}
