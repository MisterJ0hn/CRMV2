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

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $diasMorosidadPat;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $morosidadPatIcono;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $morosidadPatColor;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $morosidadPatNombre;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $morosidadTramitadorMax;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $passwordHistorial;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $passwordDiasExpiracion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pjudUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pjudUsuario;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pjudPassword;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $pjudAmbiente;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $crmApiKey;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $crmSecretKey;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $aderesoUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $aderesoApiKey;


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

    public function getDiasMorosidadPat(): ?int
    {
        return $this->diasMorosidadPat;
    }

    public function setDiasMorosidadPat(?int $diasMorosidadPat): self
    {
        $this->diasMorosidadPat = $diasMorosidadPat;

        return $this;
    }

    public function getMorosidadPatIcono(): ?string
    {
        return $this->morosidadPatIcono;
    }

    public function setMorosidadPatIcono(?string $morosidadPatIcono): self
    {
        $this->morosidadPatIcono = $morosidadPatIcono;

        return $this;
    }

    public function getMorosidadPatColor(): ?string
    {
        return $this->morosidadPatColor;
    }

    public function setMorosidadPatColor(?string $morosidadPatColor): self
    {
        $this->morosidadPatColor = $morosidadPatColor;

        return $this;
    }

    public function getMorosidadPatNombre(): ?string
    {
        return $this->morosidadPatNombre;
    }

    public function setMorosidadPatNombre(?string $morosidadPatNombre): self
    {
        $this->morosidadPatNombre = $morosidadPatNombre;

        return $this;
    }

    public function getMorosidadTramitadorMax(): ?int
    {
        return $this->morosidadTramitadorMax;
    }

    public function setMorosidadTramitadorMax(?int $morosidadTramitadorMax): self
    {
        $this->morosidadTramitadorMax = $morosidadTramitadorMax;

        return $this;
    }

    public function getPaswordHistorial(): ?int
    {
        return $this->passwordHistorial;
    }

    public function setPaswordHistorial(?int $passwordHistorial): self
    {
        $this->passwordHistorial = $passwordHistorial;

        return $this;
    }

    public function getPasswordDiasExpiracion(): ?int
    {
        return $this->passwordDiasExpiracion;
    }

    public function setPasswordDiasExpiracion(?int $passwordDiasExpiracion): self
    {
        $this->passwordDiasExpiracion = $passwordDiasExpiracion;

        return $this;
    }

    public function getPjudUrl(): ?string
    {
        return $this->pjudUrl;
    }

    public function setPjudUrl(?string $pjudUrl): self
    {
        $this->pjudUrl = $pjudUrl;

        return $this;
    }

    public function getPjudUsuario(): ?string
    {
        return $this->pjudUsuario;
    }

    public function setPjudUsuario(?string $pjudUsuario): self
    {
        $this->pjudUsuario = $pjudUsuario;

        return $this;
    }

    public function getPjudPassword(): ?string
    {
        return $this->pjudPassword;
    }

    public function setPjudPassword(?string $pjudPassword): self
    {
        $this->pjudPassword = $pjudPassword;

        return $this;
    }

    public function getPjudAmbiente(): ?string
    {
        return $this->pjudAmbiente;
    }

    public function setPjudAmbiente(?string $pjudAmbiente): self
    {
        $this->pjudAmbiente = $pjudAmbiente;

        return $this;
    }

    public function getCrmApiKey(): ?string
    {
        return $this->crmApiKey;
    }

    public function setCrmApiKey(?string $crmApiKey): self
    {
        $this->crmApiKey = $crmApiKey;

        return $this;
    }

    public function getCrmSecretKey(): ?string
    {
        return $this->crmSecretKey;
    }

    public function setCrmSecretKey(?string $crmSecretKey): self
    {
        $this->crmSecretKey = $crmSecretKey;

        return $this;
    }

    public function setAderesoUrl(?string $aderesoUrl): self
    {
        $this->aderesoUrl = $aderesoUrl;
        return $this;
    }
    public function getAderesoUrl(): ?string
    {
        return $this->aderesoUrl;
    }

    
    public function setAderesoApiKey(?string $aderesoApiKey): self
    {
        $this->aderesoApiKey = $aderesoApiKey;
        return $this;
    }
    public function getAderesoApiKey(): ?string
    {
        return $this->aderesoApiKey;
    }
}
