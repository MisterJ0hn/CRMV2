<?php

namespace App\Entity;

use App\Repository\VwPjudMovimientosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VwPjudMovimientosRepository::class)
 */
class VwPjudMovimientos
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=PjudCausa::class, inversedBy="VwPjudMovimientos")
     */
    private $pjudCausa;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $folio;

    /**
     * @ORM\Column(type="boolean")
     */
    private $tienePdf;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $etapa;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $tramite;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fecha;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $foja;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $indice;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cuadernoId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cuadernoNombre;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pdfPrincipalNombre;

    /** 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pdfAnexoNombre;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $pdfPrincipalBase64;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $pdfAnexoBase64;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cantidad_anexos;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $pdfPrincipalDescargado;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $pdfAnexoDescargado;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPjudCausa(): ?PjudCausa
    {
        return $this->pjudCausa;
    }

    public function setPjudCausa(?PjudCausa $pjudCausa): self
    {
        $this->pjudCausa = $pjudCausa;

        return $this;
    }

    public function getFolio(): ?string
    {
        return $this->folio;
    }

    public function setFolio(?string $folio): self
    {
        $this->folio = $folio;

        return $this;
    }

    public function getTienePdf(): ?bool
    {
        return $this->tienePdf;
    }

    public function setTienePdf(bool $tienePdf): self
    {
        $this->tienePdf = $tienePdf;

        return $this;
    }

    public function getEtapa(): ?string
    {
        return $this->etapa;
    }

    public function setEtapa(?string $etapa): self
    {
        $this->etapa = $etapa;

        return $this;
    }

    public function getTramite(): ?string
    {
        return $this->tramite;
    }

    public function setTramite(string $tramite): self
    {
        $this->tramite = $tramite;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getFecha(): ?string
    {
        return $this->fecha;
    }

    public function setFecha(?string $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getFoja(): ?string
    {
        return $this->foja;
    }

    public function setFoja(?string $foja): self
    {
        $this->foja = $foja;

        return $this;
    }

    public function getIndice(): ?int
    {
        return $this->indice;
    }

    public function setIndice(?int $indice): self
    {
        $this->indice = $indice;

        return $this;
    }

    public function getCuadernoId(): ?int
    {
        return $this->cuadernoId;
    }

    public function setCuadernoId(?int $cuadernoId): self
    {
        $this->cuadernoId = $cuadernoId;

        return $this;
    }

    public function getCuadernoNombre(): ?string
    {
        return $this->cuadernoNombre;
    }

    public function setCuadernoNombre(?string $cuadernoNombre): self
    {
        $this->cuadernoNombre = $cuadernoNombre;

        return $this;
    }

    public function getPdfPrincipalNombre(): ?string
    {
        return $this->pdfPrincipalNombre;
    }
    public function setPdfPrincipalNombre(?string $pdfPrincipalNombre): self
    {
        $this->pdfPrincipalNombre = $pdfPrincipalNombre;

        return $this;
    }
    public function getPdfAnexoNombre(): ?string
    {
        return $this->pdfAnexoNombre;
    }
    public function setPdfAnexoNombre(?string $pdfAnexoNombre): self
    {
        $this->pdfAnexoNombre = $pdfAnexoNombre;
        return $this;
    }

    public function getPdfPrincipalBase64(): ?string
    {
        return $this->pdfPrincipalBase64;
    }
    public function setPdfPrincipalBase64(?string $pdfPrincipalBase64): self
    {
        $this->pdfPrincipalBase64 = $pdfPrincipalBase64;

        return $this;
    }
    public function getPdfAnexoBase64(): ?string
    {
        return $this->pdfAnexoBase64;
    }
    public function setPdfAnexoBase64(?string $pdfAnexoBase64): self
    {
        $this->pdfAnexoBase64 = $pdfAnexoBase64;

        return $this;
    }

    public function getCantidadAnexos(): ?int
    {
        return $this->cantidad_anexos;
    }

    public function setCantidadAnexos(?int $cantidad_anexos): self
    {
        $this->cantidad_anexos = $cantidad_anexos;

        return $this;
    }

    public function getPdfPrincipalDescargado(): ?bool
    {
        return $this->pdfPrincipalDescargado;
    }

    public function setPdfPrincipalDescargado(?bool $pdfPrincipalDescargado): self
    {
        $this->pdfPrincipalDescargado = $pdfPrincipalDescargado;

        return $this;
    }

    public function getPdfAnexoDescargado(): ?bool
    {
        return $this->pdfAnexoDescargado;
    }

    public function setPdfAnexoDescargado(?bool $pdfAnexoDescargado): self
    {
        $this->pdfAnexoDescargado = $pdfAnexoDescargado;

        return $this;
    }

}
