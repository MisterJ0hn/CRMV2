<?php

namespace App\Entity;
use App\Repository\VwContratoRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass=ContratoRepository::class)
 * @ORM\Table(name="vw_contrato")
 */
class VwContrato
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
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $telefono;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $ciudad;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $rut;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $direccion;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $comuna;

    /**
     * @ORM\ManyToOne(targetEntity=EstadoCivil::class, inversedBy="contratos")
     */
    private $estadoCivil;

    /**
     * @ORM\ManyToOne(targetEntity=SituacionLaboral::class, inversedBy="contratos")
     */
    private $situacionLaboral;

    /**
     * @ORM\ManyToOne(targetEntity=EstrategiaJuridica::class, inversedBy="contratos")
     */
    private $estrategiaJuridica;

    /**
     * @ORM\ManyToOne(targetEntity=Escritura::class, inversedBy="contratos")
     */
    private $escritura;

    /**
     * @ORM\OneToOne(targetEntity=Agenda::class,inversedBy="contrato", cascade={"persist", "remove"})
     */
    private $agenda;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tituloContrato;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=0, nullable=true)
     */
    private $montoNivelDeuda;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=0, nullable=true)
     */
    private $MontoContrato;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cuotas;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=0, nullable=true)
     */
    private $valorCuota;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     */
    private $interes;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $diaPago;

    /**
     * @ORM\OneToMany(targetEntity=ContratoRol::class, mappedBy="contrato")
     */
    private $contratoRols;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaCreacion;

    /**
     * @ORM\ManyToOne(targetEntity=Sucursal::class, inversedBy="contratos")
     */
    private $sucursal;

    
    private $contratoTramitadores;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="contratos")
     */
    private $tramitador;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="usuarioContratos")
     */
    private $cliente;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $claveUnica;

    /**
     * @ORM\ManyToOne(targetEntity=Pais::class, inversedBy="contratos")
     */
    private $pais;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $telefonoRecado;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaPrimerPago;

    /**
     * @ORM\ManyToOne(targetEntity=ContratoVehiculo::class, inversedBy="contratos")
     */
    private $vehiculo;

    /**
     * @ORM\ManyToOne(targetEntity=ContratoVivienda::class, inversedBy="contratos")
     */
    private $vivienda;

    /**
     * @ORM\ManyToOne(targetEntity=Reunion::class, inversedBy="contratos")
     */
    private $reunion;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $primeraCuota;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fechaPrimeraCuota;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pdf;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $observacion;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isAbono;

    /**
     * @ORM\OneToMany(targetEntity=Cuota::class, mappedBy="contrato", orphanRemoval=true)
     */
    private $detalleCuotas;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fechaUltimoPago;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isFinalizado;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lote;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pdfTermino;

    /**
     * @ORM\OneToMany(targetEntity=ContratoAnexo::class, mappedBy="contrato")
     */
    private $contratoAnexos;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaTermino;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $vigencia;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaDesiste;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaPdfAnexo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ultimaFuncion;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $qMov;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fechaCompromiso;

    /**
     * @ORM\OneToMany(targetEntity=Cobranza::class, mappedBy="contrato", orphanRemoval=true)
     */
    private $cobranzas;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $folio;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $folioContrato;

    /**
     * @ORM\OneToOne(targetEntity=Lotes::class, cascade={"persist", "remove"})
     */
    private $idLote;

    /**
     * @ORM\ManyToOne(targetEntity=Comuna::class, inversedBy="contratos")
     */
    private $ccomuna;

    /**
     * @ORM\ManyToOne(targetEntity=Ciudad::class)
     */
    private $cciudad;

    /**
     * @ORM\ManyToOne(targetEntity=Region::class, inversedBy="contratos")
     */
    private $cregion;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $sexo;

    /**
     * @ORM\OneToMany(targetEntity=ContratoMee::class, mappedBy="contrato")
     */
    private $contratoMees;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $IsAnexo;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $proximoVencimiento;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fechaUltimaGestion;

    /**
     * @ORM\OneToMany(targetEntity=ContratoAudios::class, mappedBy="contrato")
     */
    private $contratoAudios;

    /**
     * @ORM\OneToMany(targetEntity=Ticket::class, mappedBy="contrato")
     */
    private $tickets;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=0, nullable=true)
     */
    private $pagoActual;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isTotal;


    /**
     * @ORM\Column(type="integer")
     */
    private $carteraOrden;

    /**
     * @ORM\ManyToOne(targetEntity=Cartera::class)
     */
    private $cartera;


    /**
     * @ORM\OneToMany(targetEntity=CausaObservacion::class, mappedBy="contrato")
     */
    private $causaObservacions;

     /**
     * @ORM\OneToMany(targetEntity=ContratoArchivos::class, mappedBy="contrato")
     */
    private $contratoArchivos;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isIncorporacion;

    /**
     * @ORM\OneToMany(targetEntity=ContratoObservacion::class, mappedBy="contrato")
     */
    private $contratoObservacions;

    /**
     * @ORM\OneToMany(targetEntity=Pago::class, mappedBy="contrato")
     */
    private $pagos;

    /**
     * @ORM\ManyToOne(targetEntity=Grupo::class, inversedBy="contratos")
     */
    private $grupo;

    /**
     * @ORM\OneToMany(targetEntity=Encuesta::class, mappedBy="contrato")
     */
    private $encuestas;

    /**
     * @ORM\ManyToOne(targetEntity=EstadoEncuesta::class)
     */
    private $estadoEncuesta;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ObservacionEncuesta;

    /**
     * @ORM\Column(type="datetime")
     */
    private $FechaEncuesta;

    /**
     * @ORM\Column(type="datetime")
     */
    private $FechaGestion;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $qtyEncuesta;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $qtyGestionEncuesta;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ultimaNota;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $usuarioEncuestaId;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $usuarioGestionId;

     /**
     * @ORM\Column(type="string", length=255)
     */
    private $usuarioCalidad;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $encuestaFuncionEncuesta;

     /**
     * @ORM\Column(type="string", length=255)
     */
    private $encuestaFuncionRespuesta;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $gestionFuncionEncuesta;

     /**
     * @ORM\Column(type="string", length=255)
     */
    private $gestionFuncionRespuesta;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $gestionObservacion;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $encuestaObservacion;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $encuestaNota;	
     /**
     * @ORM\Column(type="datetime")
     */	
	private $encuestaFechaCierre;	
    /**
     * @ORM\Column(type="string", length=255)
     */
	private $encuestaRespuestaAbierta;
    /**
     * @ORM\Column(type="string", length=255)
     */
	private $encuestaPregunta;
    /**
     * @ORM\Column(type="datetime")
     */	
	private $fechaPago;	
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $monto;
        /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numero;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $vip;

    public function __construct()
    {
        $this->contratoRols = new ArrayCollection();
        $this->detalleCuotas = new ArrayCollection();
        $this->contratoAnexos = new ArrayCollection();
        $this->cobranzas = new ArrayCollection();
        $this->contratoMees = new ArrayCollection();
        $this->contratoAudios = new ArrayCollection();
        $this->tickets = new ArrayCollection();
        $this->causaObservacions = new ArrayCollection();
        
        $this->contratoArchivos = new ArrayCollection();
        $this->contratoObservacions = new ArrayCollection();
        $this->pagos = new ArrayCollection();
        $this->encuestas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): self
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getCiudad(): ?string
    {
        return $this->ciudad;
    }

    public function setCiudad(string $ciudad): self
    {
        $this->ciudad = $ciudad;

        return $this;
    }

    public function getRut(): ?string
    {
        return $this->rut;
    }

    public function setRut(?string $rut): self
    {
        $this->rut = $rut;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(string $direccion): self
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getComuna(): ?string
    {
        return $this->comuna;
    }

    public function setComuna(string $comuna): self
    {
        $this->comuna = $comuna;

        return $this;
    }

    public function getEstadoCivil(): ?EstadoCivil
    {
        return $this->estadoCivil;
    }

    public function setEstadoCivil(?EstadoCivil $estadoCivil): self
    {
        $this->estadoCivil = $estadoCivil;

        return $this;
    }

    public function getSituacionLaboral(): ?SituacionLaboral
    {
        return $this->situacionLaboral;
    }

    public function setSituacionLaboral(?SituacionLaboral $situacionLaboral): self
    {
        $this->situacionLaboral = $situacionLaboral;

        return $this;
    }

    public function getEstrategiaJuridica(): ?EstrategiaJuridica
    {
        return $this->estrategiaJuridica;
    }

    public function setEstrategiaJuridica(?EstrategiaJuridica $estrategiaJuridica): self
    {
        $this->estrategiaJuridica = $estrategiaJuridica;

        return $this;
    }

    public function getEscritura(): ?Escritura
    {
        return $this->escritura;
    }

    public function setEscritura(?Escritura $escritura): self
    {
        $this->escritura = $escritura;

        return $this;
    }

    public function getAgenda(): ?Agenda
    {
        return $this->agenda;
    }

    public function setAgenda(?Agenda $agenda): self
    {
        $this->agenda = $agenda;

        return $this;
    }

    public function getTituloContrato(): ?string
    {
        return $this->tituloContrato;
    }

    public function setTituloContrato(?string $tituloContrato): self
    {
        $this->tituloContrato = $tituloContrato;

        return $this;
    }

    public function getMontoNivelDeuda(): ?string
    {
        return $this->montoNivelDeuda;
    }

    public function setMontoNivelDeuda(?string $montoNivelDeuda): self
    {
        $this->montoNivelDeuda = $montoNivelDeuda;

        return $this;
    }

    public function getMontoContrato(): ?string
    {
        return $this->MontoContrato;
    }

    public function setMontoContrato(?string $MontoContrato): self
    {
        $this->MontoContrato = $MontoContrato;

        return $this;
    }

    public function getCuotas(): ?int
    {
        return $this->cuotas;
    }

    public function setCuotas(?int $cuotas): self
    {
        $this->cuotas = $cuotas;

        return $this;
    }

    public function getValorCuota(): ?string
    {
        return $this->valorCuota;
    }

    public function setValorCuota(?string $valorCuota): self
    {
        $this->valorCuota = $valorCuota;

        return $this;
    }

    public function getInteres(): ?string
    {
        return $this->interes;
    }

    public function setInteres(?string $interes): self
    {
        $this->interes = $interes;

        return $this;
    }

    public function getDiaPago(): ?int
    {
        return $this->diaPago;
    }

    public function setDiaPago(?int $diaPago): self
    {
        $this->diaPago = $diaPago;

        return $this;
    }

    /**
     * @return Collection|ContratoRol[]
     */
    public function getContratoRols(): Collection
    {
        return $this->contratoRols;
    }

    public function addContratoRol(ContratoRol $contratoRol): self
    {
        if (!$this->contratoRols->contains($contratoRol)) {
            $this->contratoRols[] = $contratoRol;
            $contratoRol->setContrato($this);
        }

        return $this;
    }

    public function removeContratoRol(ContratoRol $contratoRol): self
    {
        if ($this->contratoRols->removeElement($contratoRol)) {
            // set the owning side to null (unless already changed)
            if ($contratoRol->getContrato() === $this) {
                $contratoRol->setContrato(null);
            }
        }

        return $this;
    }
    public function __toString(){
        return $this->getId()." ".$this->getNombre();
    }

    public function getFechaCreacion(): ?\DateTimeInterface
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(?\DateTimeInterface $fechaCreacion): self
    {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    public function getSucursal(): ?Sucursal
    {
        return $this->sucursal;
    }

    public function setSucursal(?Sucursal $sucursal): self
    {
        $this->sucursal = $sucursal;

        return $this;
    }

    public function getTramitador(): ?Usuario
    {
        return $this->tramitador;
    }

    public function setTramitador(?Usuario $tramitador): self
    {
        $this->tramitador = $tramitador;

        return $this;
    }

    public function getCliente(): ?Usuario
    {
        return $this->cliente;
    }

    public function setCliente(?Usuario $cliente): self
    {
        $this->cliente = $cliente;

        return $this;
    }

    public function getClaveUnica(): ?string
    {
        return $this->claveUnica;
    }

    public function setClaveUnica(?string $claveUnica): self
    {
        $this->claveUnica = $claveUnica;

        return $this;
    }

    public function getPais(): ?Pais
    {
        return $this->pais;
    }

    public function setPais(?Pais $pais): self
    {
        $this->pais = $pais;

        return $this;
    }

    public function getTelefonoRecado(): ?string
    {
        return $this->telefonoRecado;
    }

    public function setTelefonoRecado(?string $telefonoRecado): self
    {
        $this->telefonoRecado = $telefonoRecado;

        return $this;
    }

    public function getFechaPrimerPago(): ?\DateTimeInterface
    {
        return $this->fechaPrimerPago;
    }

    public function setFechaPrimerPago(?\DateTimeInterface $fechaPrimerPago): self
    {
        $this->fechaPrimerPago = $fechaPrimerPago;

        return $this;
    }

    public function getVehiculo(): ?ContratoVehiculo
    {
        return $this->vehiculo;
    }

    public function setVehiculo(?ContratoVehiculo $vehiculo): self
    {
        $this->vehiculo = $vehiculo;

        return $this;
    }

    public function getVivienda(): ?ContratoVivienda
    {
        return $this->vivienda;
    }

    public function setVivienda(?ContratoVivienda $vivienda): self
    {
        $this->vivienda = $vivienda;

        return $this;
    }

    public function getReunion(): ?Reunion
    {
        return $this->reunion;
    }

    public function setReunion(?Reunion $reunion): self
    {
        $this->reunion = $reunion;

        return $this;
    }

    public function getPrimeraCuota(): ?float
    {
        return $this->primeraCuota;
    }

    public function setPrimeraCuota(?float $primeraCuota): self
    {
        $this->primeraCuota = $primeraCuota;

        return $this;
    }

    public function getFechaPrimeraCuota(): ?\DateTimeInterface
    {
        return $this->fechaPrimeraCuota;
    }

    public function setFechaPrimeraCuota(?\DateTimeInterface $fechaPrimeraCuota): self
    {
        $this->fechaPrimeraCuota = $fechaPrimeraCuota;

        return $this;
    }

    public function getPdf(): ?string
    {
        return $this->pdf;
    }

    public function setPdf(?string $pdf): self
    {
        $this->pdf = $pdf;

        return $this;
    }

    public function getObservacion(): ?string
    {
        return $this->observacion;
    }

    public function setObservacion(?string $observacion): self
    {
        $this->observacion = $observacion;

        return $this;
    }

    public function getIsAbono(): ?bool
    {
        return $this->isAbono;
    }

    public function setIsAbono(?bool $isAbono): self
    {
        $this->isAbono = $isAbono;

        return $this;
    }

    /**
     * @return Collection|Cuota[]
     */
    public function getDetalleCuotas(): Collection
    {
        return $this->detalleCuotas;
    }

    public function addDetalleCuota(Cuota $detalleCuota): self
    {
        if (!$this->detalleCuotas->contains($detalleCuota)) {
            $this->detalleCuotas[] = $detalleCuota;
            $detalleCuota->setContrato($this);
        }

        return $this;
    }

    public function removeDetalleCuota(Cuota $detalleCuota): self
    {
        if ($this->detalleCuotas->removeElement($detalleCuota)) {
            // set the owning side to null (unless already changed)
            if ($detalleCuota->getContrato() === $this) {
                $detalleCuota->setContrato(null);
            }
        }

        return $this;
    }

    public function getFechaUltimoPago(): ?\DateTimeInterface
    {
        return $this->fechaUltimoPago;
    }

    public function setFechaUltimoPago(?\DateTimeInterface $fechaUltimoPago): self
    {
        $this->fechaUltimoPago = $fechaUltimoPago;

        return $this;
    }

    public function getIsFinalizado(): ?bool
    {
        return $this->isFinalizado;
    }

    public function setIsFinalizado(?bool $isFinalizado): self
    {
        $this->isFinalizado = $isFinalizado;

        return $this;
    }

    public function getLote(): ?int
    {
        return $this->lote;
    }

    public function setLote(?int $lote): self
    {
        $this->lote = $lote;

        return $this;
    }

    public function getPdfTermino(): ?string
    {
        return $this->pdfTermino;
    }

    public function setPdfTermino(?string $pdfTermino): self
    {
        $this->pdfTermino = $pdfTermino;

        return $this;
    }

    /**
     * @return Collection|ContratoAnexo[]
     */
    public function getContratoAnexos(): Collection
    {
        return $this->contratoAnexos;
    }

    public function addContratoAnexo(ContratoAnexo $contratoAnexo): self
    {
        if (!$this->contratoAnexos->contains($contratoAnexo)) {
            $this->contratoAnexos[] = $contratoAnexo;
            $contratoAnexo->setContrato($this);
        }

        return $this;
    }

    public function removeContratoAnexo(ContratoAnexo $contratoAnexo): self
    {
        if ($this->contratoAnexos->removeElement($contratoAnexo)) {
            // set the owning side to null (unless already changed)
            if ($contratoAnexo->getContrato() === $this) {
                $contratoAnexo->setContrato(null);
            }
        }

        return $this;
    }

    public function getFechaTermino(): ?\DateTimeInterface
    {
        return $this->fechaTermino;
    }

    public function setFechaTermino(?\DateTimeInterface $fechaTermino): self
    {
        $this->fechaTermino = $fechaTermino;

        return $this;
    }

    public function getVigencia(): ?int
    {
        return $this->vigencia;
    }

    public function setVigencia(?int $vigencia): self
    {
        $this->vigencia = $vigencia;

        return $this;
    }

    public function getFechaDesiste(): ?\DateTimeInterface
    {
        return $this->fechaDesiste;
    }

    public function setFechaDesiste(?\DateTimeInterface $fechaDesiste): self
    {
        $this->fechaDesiste = $fechaDesiste;

        return $this;
    }

    public function getFechaPdfAnexo(): ?\DateTimeInterface
    {
        return $this->fechaPdfAnexo;
    }

    public function setFechaPdfAnexo(?\DateTimeInterface $fechaPdfAnexo): self
    {
        $this->fechaPdfAnexo = $fechaPdfAnexo;

        return $this;
    }

    public function getUltimaFuncion(): ?string
    {
        return $this->ultimaFuncion;
    }

    public function setUltimaFuncion(?string $ultimaFuncion): self
    {
        $this->ultimaFuncion = $ultimaFuncion;

        return $this;
    }

    public function getQMov(): ?int
    {
        return $this->qMov;
    }

    public function setQMov(?int $qMov): self
    {
        $this->qMov = $qMov;

        return $this;
    }

    public function getFechaCompromiso(): ?\DateTimeInterface
    {
        return $this->fechaCompromiso;
    }

    public function setFechaCompromiso(?\DateTimeInterface $fechaCompromiso): self
    {
        $this->fechaCompromiso = $fechaCompromiso;

        return $this;
    }

    /**
     * @return Collection|Cobranza[]
     */
    public function getCobranzas(): Collection
    {
        return $this->cobranzas;
    }

    public function addCobranza(Cobranza $cobranza): self
    {
        if (!$this->cobranzas->contains($cobranza)) {
            $this->cobranzas[] = $cobranza;
            $cobranza->setContrato($this);
        }

        return $this;
    }

    public function removeCobranza(Cobranza $cobranza): self
    {
        if ($this->cobranzas->removeElement($cobranza)) {
            // set the owning side to null (unless already changed)
            if ($cobranza->getContrato() === $this) {
                $cobranza->setContrato(null);
            }
        }

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

    public function getFolioContrato(): ?string
    {
        return $this->folioContrato;
    }

    public function setFolioContrato(?string $folioContrato): self
    {
        $this->folioContrato = $folioContrato;

        return $this;
    }

    public function getIdLote(): ?Lotes
    {
        return $this->idLote;
    }

    public function setIdLote(?Lotes $idLote): self
    {
        $this->idLote = $idLote;

        return $this;
    }

    public function getCcomuna(): ?Comuna
    {
        return $this->ccomuna;
    }

    public function setCcomuna(?Comuna $ccomuna): self
    {
        $this->ccomuna = $ccomuna;

        return $this;
    }

    public function getCciudad(): ?Ciudad
    {
        return $this->cciudad;
    }

    public function setCciudad(?Ciudad $cciudad): self
    {
        $this->cciudad = $cciudad;

        return $this;
    }

    public function getCregion(): ?Region
    {
        return $this->cregion;
    }

    public function setCregion(?Region $cregion): self
    {
        $this->cregion = $cregion;

        return $this;
    }

    public function getSexo(): ?string
    {
        return $this->sexo;
    }

    public function setSexo(?string $sexo): self
    {
        $this->sexo = $sexo;

        return $this;
    }

    /**
     * @return Collection|ContratoMee[]
     */
    public function getContratoMees(): Collection
    {
        return $this->contratoMees;
    }

    public function addContratoMee(ContratoMee $contratoMee): self
    {
        if (!$this->contratoMees->contains($contratoMee)) {
            $this->contratoMees[] = $contratoMee;
            $contratoMee->setContrato($this);
        }

        return $this;
    }

    public function removeContratoMee(ContratoMee $contratoMee): self
    {
        if ($this->contratoMees->removeElement($contratoMee)) {
            // set the owning side to null (unless already changed)
            if ($contratoMee->getContrato() === $this) {
                $contratoMee->setContrato(null);
            }
        }

        return $this;
    }

    public function getIsAnexo(): ?bool
    {
        return $this->IsAnexo;
    }

    public function setIsAnexo(?bool $IsAnexo): self
    {
        $this->IsAnexo = $IsAnexo;

        return $this;
    }

    public function getProximoVencimiento(): ?\DateTimeInterface
    {
        return $this->proximoVencimiento;
    }

    public function setProximoVencimiento(?\DateTimeInterface $proximoVencimiento): self
    {
        $this->proximoVencimiento = $proximoVencimiento;

        return $this;
    }

    public function getFechaUltimaGestion(): ?\DateTimeInterface
    {
        return $this->fechaUltimaGestion;
    }

    public function setFechaUltimaGestion(?\DateTimeInterface $fechaUltimaGestion): self
    {
        $this->fechaUltimaGestion = $fechaUltimaGestion;

        return $this;
    }

    /**
     * @return Collection<int, ContratoAudios>
     */
    public function getContratoAudios(): Collection
    {
        return $this->contratoAudios;
    }

    public function addContratoAudio(ContratoAudios $contratoAudio): self
    {
        if (!$this->contratoAudios->contains($contratoAudio)) {
            $this->contratoAudios[] = $contratoAudio;
            $contratoAudio->setContrato($this);
        }

        return $this;
    }

    public function removeContratoAudio(ContratoAudios $contratoAudio): self
    {
        if ($this->contratoAudios->removeElement($contratoAudio)) {
            // set the owning side to null (unless already changed)
            if ($contratoAudio->getContrato() === $this) {
                $contratoAudio->setContrato(null);
            }
        }

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
            $ticket->setContrato($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getContrato() === $this) {
                $ticket->setContrato(null);
            }
        }

        return $this;
    }

    public function getPagoActual(): ?string
    {
        return $this->pagoActual;
    }

    public function setPagoActual(?string $pagoActual): self
    {
        $this->pagoActual = $pagoActual;

        return $this;
    }

    public function getIsTotal(): ?bool
    {
        return $this->isTotal;
    }

    public function setIsTotal(?bool $isTotal): self
    {
        $this->isTotal = $isTotal;

        return $this;
    }

    
    public function getCarteraOrden(): ?int
    {
        return $this->carteraOrden;
    }

    public function setCarteraOrden(int $carteraOrden): self
    {
        $this->carteraOrden = $carteraOrden;

        return $this;
    }

    public function getCartera(): ?Cartera
    {
        return $this->cartera;
    }

    public function setCartera(?Cartera $cartera): self
    {
        $this->cartera = $cartera;

        return $this;
    }


    /**
     * @return Collection|CausaObservacion[]
     */
    public function getCausaObservacions(): Collection
    {
        return $this->causaObservacions;
    }

    public function addCausaObservacion(CausaObservacion $causaObservacion): self
    {
        if (!$this->causaObservacions->contains($causaObservacion)) {
            $this->causaObservacions[] = $causaObservacion;
            $causaObservacion->setContrato($this);
        }

        return $this;
    }

    public function removeCausaObservacion(CausaObservacion $causaObservacion): self
    {
        if ($this->causaObservacions->removeElement($causaObservacion)) {
            // set the owning side to null (unless already changed)
            if ($causaObservacion->getContrato() === $this) {
                $causaObservacion->setContrato(null);
            }
        }

        return $this;
    }

   /**
     * @return Collection|ContratoArchivos[]
     */
    public function getContratoArchivos(): Collection
    {
        return $this->contratoArchivos;
    }

    public function addContratoArchivo(ContratoArchivos $contratoArchivo): self
    {
        if (!$this->contratoArchivos->contains($contratoArchivo)) {
            $this->contratoArchivos[] = $contratoArchivo;
            $contratoArchivo->setContrato($this);
        }

        return $this;
    }

    public function removeContratoArchivo(ContratoArchivos $contratoArchivo): self
    {
        if ($this->contratoArchivos->removeElement($contratoArchivo)) {
            // set the owning side to null (unless already changed)
            if ($contratoArchivo->getContrato() === $this) {
                $contratoArchivo->setContrato(null);
            }
        }

        return $this;
    }

    public function getIsIncorporacion(): ?bool
    {
        return $this->isIncorporacion;
    }

    public function setIsIncorporacion(?bool $isIncorporacion): self
    {
        $this->isIncorporacion = $isIncorporacion;

        return $this;
    }

    /**
     * @return Collection|ContratoObservacion[]
     */
    public function getContratoObservacions(): Collection
    {
        return $this->contratoObservacions;
    }

    public function addContratoObservacion(ContratoObservacion $contratoObservacion): self
    {
        if (!$this->contratoObservacions->contains($contratoObservacion)) {
            $this->contratoObservacions[] = $contratoObservacion;
            $contratoObservacion->setContrato($this);
        }

        return $this;
    }

    public function removeContratoObservacion(ContratoObservacion $contratoObservacion): self
    {
        if ($this->contratoObservacions->removeElement($contratoObservacion)) {
            // set the owning side to null (unless already changed)
            if ($contratoObservacion->getContrato() === $this) {
                $contratoObservacion->setContrato(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Pago[]
     */
    public function getPagos(): Collection
    {
        return $this->pagos;
    }

    public function addPago(Pago $pago): self
    {
        if (!$this->pagos->contains($pago)) {
            $this->pagos[] = $pago;
            $pago->setContrato($this);
        }

        return $this;
    }

    public function removePago(Pago $pago): self
    {
        if ($this->pagos->removeElement($pago)) {
            // set the owning side to null (unless already changed)
            if ($pago->getContrato() === $this) {
                $pago->setContrato(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Grupo>
     */
    public function getGrupos(): Collection
    {
        return $this->grupos;
    }

    public function addGrupo(Grupo $grupo): self
    {
        if (!$this->grupos->contains($grupo)) {
            $this->grupos[] = $grupo;
            $grupo->setContrato($this);
        }

        return $this;
    }

    public function removeGrupo(Grupo $grupo): self
    {
        if ($this->grupos->removeElement($grupo)) {
            // set the owning side to null (unless already changed)
            if ($grupo->getContrato() === $this) {
                $grupo->setContrato(null);
            }
        }

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

    /**
     * @return Collection<int, Encuesta>
     */
    public function getEncuestas(): Collection
    {
        return $this->encuestas;
    }

    public function addEncuesta(Encuesta $encuesta): self
    {
        if (!$this->encuestas->contains($encuesta)) {
            $this->encuestas[] = $encuesta;
            $encuesta->setContrato($this);
        }

        return $this;
    }

    public function removeEncuesta(Encuesta $encuesta): self
    {
        if ($this->encuestas->removeElement($encuesta)) {
            // set the owning side to null (unless already changed)
            if ($encuesta->getContrato() === $this) {
                $encuesta->setContrato(null);
            }
        }

        return $this;
    }

    public function getEstadoEncuesta(): ?EstadoEncuesta
    {
        return $this->estadoEncuesta;
    }

    public function setEstadoEncuesta(?EstadoEncuesta $estadoEncuesta): self
    {
        $this->estadoEncuesta = $estadoEncuesta;

        return $this;
    }

    public function getObservacionEncuesta(): ?string
    {
        return $this->ObservacionEncuesta;
    }

    public function setObservacionEncuesta(string $ObservacionEncuesta): self
    {
        $this->ObservacionEncuesta = $ObservacionEncuesta;

        return $this;
    }

    public function getFechaEncuesta(): ?\DateTimeInterface
    {
        return $this->FechaEncuesta;
    }

    public function setFechaEncuesta(\DateTimeInterface $FechaEncuesta): self
    {
        $this->FechaEncuesta = $FechaEncuesta;

        return $this;
    }

    public function getFechaGestion(): ?\DateTimeInterface
    {
        return $this->FechaGestion;
    }

    public function setFechaGestion(\DateTimeInterface $FechaGestion): self
    {
        $this->FechaGestion = $FechaGestion;

        return $this;
    }

    public function getQtyEncuesta(): ?int
    {
        return $this->qtyEncuesta;
    }

    public function setQtyEncuesta(?int $qtyEncuesta): self
    {
        $this->qtyEncuesta = $qtyEncuesta;

        return $this;
    }

    public function getQtyGestionEncuesta(): ?int
    {
        return $this->qtyGestionEncuesta;
    }

    public function setQtyGestionEncuesta(?int $qtyGestionEncuesta): self
    {
        $this->qtyGestionEncuesta = $qtyGestionEncuesta;

        return $this;
    }

    public function getUltimaNota(): ?int
    {
        return $this->ultimaNota;
    }

    public function setUltimaNota(?int $ultimaNota): self
    {
        $this->ultimaNota = $ultimaNota;

        return $this;
    }

    public function getUsuarioEncuestaId(): ?int
    {
        return $this->usuarioEncuestaId;
    }

    public function setUsuarioEncuestaId(?int $usuarioEncuestaId): self
    {
        $this->usuarioEncuestaId = $usuarioEncuestaId;

        return $this;
    }
    public function getUsuarioGestionId(): ?int
    {
        return $this->usuarioGestionId;
    }

    public function setUsuarioGestionId(?int $usuarioGestionId): self
    {
        $this->usuarioGestionId = $usuarioGestionId;

        return $this;
    }
    public function getUsuarioCalidad(): ?string
    {
        return $this->usuarioCalidad;
    }

    public function setUsuarioCalidad(string $usuarioCalidad): self
    {
        $this->usuarioCalidad = $usuarioCalidad;

        return $this;
    }
    
    public function getGestionFuncionRespuesta(): ?string
    {
        return $this->gestionFuncionRespuesta;
    }

    public function setGestionFuncionRespuesta(string $gestionFuncionRespuesta): self
    {
        $this->gestionFuncionRespuesta = $gestionFuncionRespuesta;

        return $this;
    }

    public function getEncuestaFuncionRespuesta(): ?string
    {
        return $this->encuestaFuncionRespuesta;
    }

    public function setEncuestaFuncionRespuesta(string $encuestaFuncionRespuesta): self
    {
        $this->encuestaFuncionRespuesta = $encuestaFuncionRespuesta;

        return $this;
    }
    public function getGestionFuncionEncuesta(): ?string
    {
        return $this->gestionFuncionEncuesta;
    }

    public function setGestionFuncionEncuesta(string $gestionFuncionEncuesta): self
    {
        $this->gestionFuncionEncuesta = $gestionFuncionEncuesta;

        return $this;
    }

    public function getEncuestaFuncionEncuesta(): ?string
    {
        return $this->encuestaFuncionEncuesta;
    }

    public function setEncuestaFuncionEncuesta(string $encuestaFuncionEncuesta): self
    {
        $this->encuestaFuncionEncuesta = $encuestaFuncionEncuesta;

        return $this;
    }

    public function getGestionObservacion(): ?string
    {
        return $this->gestionObservacion;
    }

    public function setGestionObservacion(string $gestionObservacion): self
    {
        $this->gestionObservacion = $gestionObservacion;

        return $this;
    }
    public function getEncuestaObservacion(): ?string
    {
        return $this->encuestaObservacion;
    }

    public function setEncuestaObservacion(string $encuestaObservacion): self
    {
        $this->encuestaObservacion = $encuestaObservacion;

        return $this;
    }

    public function getEncuestaNota(): ?string
    {
        return $this->encuestaNota;
    }

    public function setEncuestaNota(string $encuestaNota): self
    {
        $this->encuestaNota = $encuestaNota;

        return $this;
    }
    public function getEncuestaFechaCierre(): ?\DateTimeInterface
    {
        return $this->encuestaFechaCierre;
    }

    public function setEncuestaFechaCierre(\DateTimeInterface $encuestaFechaCierre): self
    {
        $this->encuestaFechaCierre = $encuestaFechaCierre;

        return $this;
    }
    public function getEncuestaRespuestaAbierta(): ?string
    {
        return $this->encuestaRespuestaAbierta;
    }

    public function setEncuestaRespuestaAbierta(string $encuestaRespuestaAbierta): self
    {
        $this->encuestaRespuestaAbierta = $encuestaRespuestaAbierta;

        return $this;
    }
    public function getEncuestaPregunta(): ?string
    {
        return $this->encuestaPregunta;
    }

    public function setEncuestaPregunta(string $encuestaPregunta): self
    {
        $this->encuestaPregunta = $encuestaPregunta;

        return $this;
    }
      public function getFechaPago(): ?\DateTimeInterface
    {
        return $this->fechaPago;
    }

    public function setFechaPago(\DateTimeInterface $fechaPago): self
    {
        $this->fechaPago = $fechaPago;

        return $this;
    }
    public function getMonto(): ?int
    {
        return $this->monto;
    }

    public function setMonto(?int $monto): self
    {
        $this->monto = $monto;

        return $this;
    }
     public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(?int $numero): self
    {
        $this->numero = $numero;

        return $this;
    }
    public function getVip(): ?int
    {
        return $this->vip;
    }

    public function setVip(?int $vip): self
    {
        $this->vip = $vip;

        return $this;
    }
}
