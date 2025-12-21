<?php

namespace App\Entity;
use App\Repository\VwResumenCausasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VwResumenCausasRepository::class)
 * @ORM\Table(name="vw_resumen_causas")
 */
class VwResumenCausas{
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cuentaId;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $tramitador;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $tramitadorId;

    /**
     * @ORM\Column(type="integer")
     */
    private $causasActivas;

   /**
     * @ORM\Column(type="integer")
     */
    private $causasAlDia;
    /**
     * @ORM\Column(type="integer")
     */
    private $clientesActivos;
    /**
     * @ORM\Column(type="integer")
     */
    private $ClientesAlDia;
    /**
     * @ORM\Column(type="integer")
     */
    private $clientesMorosos;
    /**
     * @ORM\Column(type="integer")
     */
    private $clientesActivosVIP;
    /**
     * @ORM\Column(type="integer")
     */
    private $clientesAlDiaVIP;
    /**
     * @ORM\Column(type="integer")
     */
    private $causasActivasConRol;
    /**
     * @ORM\Column(type="integer")
     */
    private $causasActivasSinRol;
    /**
     * @ORM\Column(type="integer")
     */
    private $causasActivasFinalizadas;

    public function getCuentaId(): ?int
    {
        return $this->cuentaId;
    }

    public function setCuentaId(?int $cuentaId): self
    {
        $this->cuentaId = $cuentaId;
        return $this;
    }

    public function getTramitador(): ?string
    {
        return $this->tramitador;
    }

    public function setTramitador(?string $tramitador): self
    {
        $this->tramitador = $tramitador;
        return $this;
    }

    public function getTramitadorId(): ?int
    {
        return $this->tramitadorId;
    }

    public function setTramitadorId(?int $tramitadorId): self
    {
        $this->tramitadorId = $tramitadorId;
        return $this;
    }

    public function getCausasActivas(): ?int
    {
        return $this->causasActivas;
    }

    public function setCausasActivas(?int $causasActivas): self
    {
        $this->causasActivas = $causasActivas;
        return $this;
    }

    public function getCausasAlDia(): ?int
    {
        return $this->causasAlDia;
    }

    public function setCausasAlDia(?int $causasAlDia): self
    {
        $this->causasAlDia = $causasAlDia;
        return $this;
    }

    public function getClientesActivos(): ?int
    {
        return $this->clientesActivos;
    }

    public function setClientesActivos(?int $clientesActivos): self
    {
        $this->clientesActivos = $clientesActivos;
        return $this;
    }

    public function getClientesAlDia(): ?int
    {
        return $this->ClientesAlDia;
    }

    public function setClientesAlDia(?int $ClientesAlDia): self
    {
        $this->ClientesAlDia = $ClientesAlDia;
        return $this;
    }

    public function getClientesMorosos(): ?int
    {
        return $this->clientesMorosos;
    }

    public function setClientesMorosos(?int $clientesMorosos): self
    {
        $this->clientesMorosos = $clientesMorosos;
        return $this;
    }

    public function getClientesActivosVIP(): ?int
    {
        return $this->clientesActivosVIP;
    }

    public function setClientesActivosVIP(?int $clientesActivosVIP): self
    {
        $this->clientesActivosVIP = $clientesActivosVIP;
        return $this;
    }

    public function getClientesAlDiaVIP(): ?int
    {
        return $this->clientesAlDiaVIP;
    }

    public function setClientesAlDiaVIP(?int $clientesAlDiaVIP): self
    {
        $this->clientesAlDiaVIP = $clientesAlDiaVIP;
        return $this;
    }

    public function getCausasActivasConRol(): ?int
    {
        return $this->causasActivasConRol;
    }

    public function setCausasActivasConRol(?int $causasActivasConRol): self
    {
        $this->causasActivasConRol = $causasActivasConRol;
        return $this;
    }

    public function getCausasActivasSinRol(): ?int
    {
        return $this->causasActivasSinRol;
    }

    public function setCausasActivasSinRol(?int $causasActivasSinRol): self
    {
        $this->causasActivasSinRol = $causasActivasSinRol;
        return $this;
    }

    public function getCausasActivasFinalizadas(): ?int
    {
        return $this->causasActivasFinalizadas;
    }

    public function setCausasActivasFinalizadas(?int $causasActivasFinalizadas): self
    {
        $this->causasActivasFinalizadas = $causasActivasFinalizadas;
        return $this;
    }

}