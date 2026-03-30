<?php

namespace App\Entity;

use App\Repository\ConfiguracionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConfiguracionRepository::class)
 */
class Configuracion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $diaFondoFijo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $host;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $accessToken;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $verifyToken;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lotes;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $valorMulta;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tokuId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tokuSecret;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxDiasComision;
    /**
     * @ORM\Column(type="integer")
     */
    private $deudaMinima;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $virtualPosUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $virtualPosApiKey;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $virtualPosSecretKey;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $virtualPosPlan;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $OcultarBase64EnTrasa;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $claveEncriptacionDescargas;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $diasMorisidadVip;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiaFondoFijo(): ?int
    {
        return $this->diaFondoFijo;
    }

    public function setDiaFondoFijo(int $diaFondoFijo): self
    {
        $this->diaFondoFijo = $diaFondoFijo;

        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(?string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getVerifyToken(): ?string
    {
        return $this->verifyToken;
    }

    public function setVerifyToken(?string $verifyToken): self
    {
        $this->verifyToken = $verifyToken;

        return $this;
    }

    public function getLotes(): ?int
    {
        return $this->lotes;
    }

    public function setLotes(?int $lotes): self
    {
        $this->lotes = $lotes;

        return $this;
    }

    public function getValorMulta(): ?int
    {
        return $this->valorMulta;
    }

    public function setValorMulta(?int $valorMulta): self
    {
        $this->valorMulta = $valorMulta;

        return $this;
    }

    public function getTokuId(): ?string
    {
        return $this->tokuId;
    }

    public function setTokuId(?string $tokuId): self
    {
        $this->tokuId = $tokuId;

        return $this;
    }

    public function getTokuSecret(): ?string
    {
        return $this->tokuSecret;
    }

    public function setTokuSecret(?string $tokuSecret): self
    {
        $this->tokuSecret = $tokuSecret;

        return $this;
    }

    public function getMaxDiasComision(): ?int
    {
        return $this->maxDiasComision;
    }

    public function setMaxDiasComision(int $maxDiasComision): self
    {
        $this->maxDiasComision = $maxDiasComision;

        return $this;
    }
    public function getDeudaminima(): ?int
    {
        return $this->deudaMinima;
    }

    public function setDeudaMinima(int $deudaMinima): self
    {
        $this->deudaMinima = $deudaMinima;

        return $this;
    }

    public function getVirtualPosUrl(): ?string
    {
        return $this->virtualPosUrl;
    }

    public function setVirtualPosUrl(?string $virtualPosUrl): self
    {
        $this->virtualPosUrl = $virtualPosUrl;

        return $this;
    }

    public function getVirtualPosApiKey(): ?string
    {
        return $this->virtualPosApiKey;
    }

    public function setVirtualPosApiKey(?string $virtualPosApiKey): self
    {
        $this->virtualPosApiKey = $virtualPosApiKey;

        return $this;
    }

    public function getVirtualPosSecretKey(): ?string
    {
        return $this->virtualPosSecretKey;
    }

    public function setVirtualPosSecretKey(?string $virtualPosSecretKey): self
    {
        $this->virtualPosSecretKey = $virtualPosSecretKey;

        return $this;
    }

    public function getVirtualPosPlan(): ?string
    {
        return $this->virtualPosPlan;
    }

    public function setVirtualPosPlan(string $virtualPosPlan): self
    {
        $this->virtualPosPlan = $virtualPosPlan;

        return $this;
    }

    public function getOcultarBase64EnTrasa(): ?bool
    {
        return $this->OcultarBase64EnTrasa;
    }

    public function setOcultarBase64EnTrasa(?bool $OcultarBase64EnTrasa): self
    {
        $this->OcultarBase64EnTrasa = $OcultarBase64EnTrasa;

        return $this;
    }

    public function getClaveEncriptacionDescargas(): ?string
    {
        return $this->claveEncriptacionDescargas;
    }

    public function setClaveEncriptacionDescargas(?string $claveEncriptacionDescargas): self
    {
        $this->claveEncriptacionDescargas = $claveEncriptacionDescargas;

        return $this;
    }

    public function getDiasMorisidadVip(): ?int
    {
        return $this->diasMorisidadVip;
    }

    public function setDiasMorisidadVip(?int $diasMorisidadVip): self
    {
        $this->diasMorisidadVip = $diasMorisidadVip;

        return $this;
    }
}
