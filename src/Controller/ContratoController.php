<?php

namespace App\Controller;

use App\Entity\Actuacion;
use App\Entity\AgendaContacto;
use App\Entity\Contrato;
use App\Entity\ContratoRol;
use App\Entity\Usuario;
use App\Entity\Cuota;
use App\Entity\Region;
use App\Entity\Ciudad;
use App\Entity\Comuna;
use App\Form\ContratoType;
use App\Entity\AgendaObservacion;
use App\Entity\Cartera;
use App\Entity\Causa;
use App\Entity\CausaObservacion;
use App\Entity\CausaObservacionArchivo;
use App\Entity\ContratoAudios;
use App\Entity\ContratoMee;
use App\Entity\ContratoObservacion;
use App\Entity\Cuaderno;
use App\Entity\DetalleCuaderno;
use App\Entity\EstrategiaJuridica;
use App\Entity\EstrategiaJuridicaReporteArchivos;
use App\Entity\LineaTiempoObservacion;
use App\Entity\LineaTiempoTerminada;
use App\Entity\Mensaje;
use App\Form\ContratoRolType;
use App\Form\MensajeType;
use App\Repository\ActuacionAnexoProcesalRepository;
use App\Repository\ActuacionRepository;
use App\Repository\AgendaContactoRepository;
use App\Repository\AgendaObservacionRepository;
use App\Repository\ContratoRepository;
use App\Repository\ContratoRolRepository;
use App\Repository\JuzgadoRepository;
use App\Repository\SucursalRepository;
use App\Repository\CuentaRepository;
use App\Repository\DiasPagoRepository;
use App\Repository\UsuarioRepository;
use App\Repository\UsuarioTipoRepository;
use App\Repository\AgendaStatusRepository;
use App\Repository\AnexoProcesalRepository;
use App\Repository\CarteraRepository;
use App\Repository\CausaObservacionRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\CuotaRepository;
use App\Repository\ConfiguracionRepository;
use App\Repository\LotesRepository;
use App\Repository\RegionRepository;
use App\Repository\CiudadRepository;
use App\Repository\ComunaRepository;
use App\Repository\ContratoAudiosRepository;
use App\Repository\ContratoMeeRepository;
use App\Repository\ContratoObservacionRepository;
use App\Repository\CorteRepository;
use App\Repository\CuadernoRepository;
use App\Repository\CuentaMateriaRepository;
use App\Repository\DetalleCuadernoRepository;
use App\Repository\EstrategiaJuridicaReporteArchivosRepository;
use App\Repository\EstrategiaJuridicaReporteRepository;
use App\Repository\JuzgadoCuentaRepository;
use App\Repository\LineaTiempoTerminadaRepository;

use App\Repository\LineaTiempoEtapasRepository;
use App\Repository\LineaTiempoObservacionRepository;
use App\Repository\MateriaCorteRepository;
use App\Repository\MateriaEstrategiaRepository;
use App\Repository\MeeRepository;
use App\Repository\UsuarioCarteraRepository;
use App\Repository\VwContratoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Service\ContratoFunciones;
use App\Service\Toku;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Exception;
use phpDocumentor\Reflection\Types\Null_;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Validator\Constraints\Length;

/**
 * @Route("/contrato")
 */
class ContratoController extends AbstractController
{
    /**
     * @Route("/", name="contrato_index", methods={"GET","POST"})
     */
    public function index(VwContratoRepository $contratoRepository,PaginatorInterface $paginator,ModuloPerRepository $moduloPerRepository,Request $request,CuentaRepository $cuentaRepository): Response
    {
        $this->denyAccessUnlessGranted('view','contrato');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('contrato',$user->getEmpresaActual());
        $filtro=null;
        $error='';
        $error_toast="";
        $otros="";
        $folio="";
        if(null !== $request->query->get('error_toast')){
            $error_toast=$request->query->get('error_toast');
        }
        $compania=null;
        if(null !== $request->query->get('bFolio') && $request->query->get('bFolio')!=''){
            $folio=$request->query->get('bFolio');
            $otros=" (c.folioContrato= '$folio' or c.agenda = '$folio') ";

            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');
            $fecha=$otros. " and a.status in (7,14)";

        }else{
            if(null !== $request->query->get('bFiltro') && $request->query->get('bFiltro')!=''){
                $filtro=$request->query->get('bFiltro');
                $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
                $dateFin=date('Y-m-d');
                $fecha=$otros. " a.status in (7,14)";
            }else{
                if(null !== $request->query->get('bCompania') && $request->query->get('bCompania')!=0){
                    $compania=$request->query->get('bCompania');
                }
                if(null !== $request->query->get('bFecha')){
                    $aux_fecha=explode(" - ",$request->query->get('bFecha'));
                    $dateInicio=$aux_fecha[0];
                    $dateFin=$aux_fecha[1];
                }else{
                    //$dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*7);
                    $dateInicio=date('Y-m-d');
                    
                    $dateFin=date('Y-m-d');
                }
                $fecha="c.fechaCreacion between '$dateInicio' and '$dateFin 23:59:59' and a.status in (7,14)" ;
            }
        }
      
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 4:
            case 1:
            case 8:
            case 11:
                $query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;
            case 13:
            case 10:
                $companias=$cuentaRepository->findByPers($user->getId());
                $listCompanias='';

                $i=0;
                foreach($companias as $compania_loop){
                    
                    if($i>0){
                        $listCompanias.=',';
                    }
                    $listCompanias.=$compania_loop->getId();
                    $i++;
                }
                $fecha.=" and a.cuenta in ($listCompanias) ";
                $query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha);
                break;
            case 7:
                $carteras;
                foreach($user->getUsuarioCarteras() as $usuarioCartera){
                    $carteras[]=$usuarioCartera->getCartera()->getId();
                }
                if(count($carteras)>0){
                    $fecha.=" and c.cartera in (".implode(",",$carteras).") ";
                }else{
                    $fecha.=" and c.cartera is null ";
                }

                $query=$contratoRepository->findByPers(null,null,$compania,$filtro,null,$fecha);
                $companias=$cuentaRepository->findByPers($user->getId());
                break;
            case 12://Cobradores
                $lotes;
                foreach($user->getUsuarioLotes() as $usuarioLote){
                    $lotes[]=$usuarioLote->getLote()->getId();
                }
                if(count($lotes)>0){
                    $fecha.=" and c.idLote in (".implode(",",$lotes).") ";
                }else{
                    $fecha.=" and c.idLote is null ";
                }
                //$fecha.=" and c.idLote in (".implode(",",$lotes).") ";
                $query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;
            default:
                $query=$contratoRepository->findByPers($user->getId(),null,$compania,$filtro,null,$fecha);
                $companias=$cuentaRepository->findByPers($user->getId());
                
            break;
        }
        //$companias=$cuentaRepository->findByPers($user->getId());
        //$query=$contratoRepository->findAll();
        $contratos=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/,
            array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));
        return $this->render('contrato/index.html.twig', [
            'contratos' => $contratos,
            'bFiltro'=>$filtro,
            'bFolio'=>$folio,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'pagina'=>$pagina->getNombre(),
            'error'=>$error,
            'error_toast'=>$error_toast,
            'TipoFiltro'=>'Contrato'
        ]);
    }

     /**
     * @Route("/actualizafecha", name="contrato_actualizaFecha", methods={"GET","POST"})
     */
    public function actualizafecha(Request $request,ContratoRepository $contratoRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $contratos=$contratoRepository->findAll();
        foreach($contratos as $contrato){
            $agenda=$contrato->getAgenda();
            $agenda->setFechaContrato($contrato->getFechaCreacion());
            $entityManager->persist($agenda);
            $entityManager->flush();
        }
        return $this->redirectToRoute('contrato_index');
    }

    /**
     * @Route("/export_excel", name="contrato_export_excel", methods={"GET","POST"})
     */
    public function exportExcel(Request $request, VwContratoRepository $contratoRepository, CuentaRepository $cuentaRepository): Response
    {

        $this->denyAccessUnlessGranted('view','exportar_excel_contrato');
        $user=$this->getUser();
        $filtro=null;
        $error='';
        $error_toast="";
        $otros="";
        $folio="";
        if(null !== $request->query->get('error_toast')){
            $error_toast=$request->query->get('error_toast');
        }
        $compania=null;
        if(null !== $request->query->get('bFolio') && $request->query->get('bFolio')!=''){
            $folio=$request->query->get('bFolio');
            $otros=" c.folioContrato= '$folio'";

            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');
            $fecha=$otros. " and a.status in (7,14)";

        }else{
            if(null !== $request->query->get('bFiltro') && $request->query->get('bFiltro')!=''){
                $filtro=$request->query->get('bFiltro');
            }
            if(null !== $request->query->get('bCompania') && $request->query->get('bCompania')!=0){
                $compania=$request->query->get('bCompania');
            }
            if(null !== $request->query->get('bFecha')){
                $aux_fecha=explode(" - ",$request->query->get('bFecha'));
                $dateInicio=$aux_fecha[0];
                $dateFin=$aux_fecha[1];
            }else{
                //$dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*7);
                $dateInicio=date('Y-m-d');
                
                $dateFin=date('Y-m-d');
            }
            $fecha="c.fechaCreacion between '$dateInicio' and '$dateFin 23:59:59' and a.status in (7,14)" ;
        }
      
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 4:
            case 1:
            case 8:
            case 11:
                $query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha);
             
                break;
            case 13:
                $companias=$cuentaRepository->findByPers($user->getId());
                $listCompanias='';

                $i=0;
                foreach($companias as $compania_loop){
                    
                    if($i>0){
                        $listCompanias.=',';
                    }
                    $listCompanias.=$compania_loop->getId();
                    $i++;
                }
                $fecha.=" and a.cuenta in ($listCompanias) ";
                $query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha);
                
                break;
            case 7:
                $carteras;
                foreach($user->getUsuarioCarteras() as $usuarioCartera){
                    $carteras[]=$usuarioCartera->getCartera()->getId();
                }
                if(count($carteras)>0){
                    $fecha.=" and c.cartera in (".implode(",",$carteras).") ";
                }else{
                    $fecha.=" and c.cartera is null ";
                }

                $query=$contratoRepository->findByPers(null,null,$compania,$filtro,null,$fecha);

                break;
            case 12://Cobradores
                $lotes;
                foreach($user->getUsuarioLotes() as $usuarioLote){
                    $lotes[]=$usuarioLote->getLote()->getId();
                }
                if(count($lotes)>0){
                    $fecha.=" and c.idLote in (".implode(",",$lotes).") ";
                }else{
                    $fecha.=" and c.idLote is null ";
                }
                //$fecha.=" and c.idLote in (".implode(",",$lotes).") ";
                $query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha);
              
                break;
            default:
                $query=$contratoRepository->findByPers($user->getId(),null,$compania,$filtro,null,$fecha);
                
                
            break;
        }

        $fileName="contratos.csv";
        $titulo = "Contratos";
        
        
        $spreadSheet=new Spreadsheet();

        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadSheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Fecha Creación');
        $sheet->setCellValue('B1', 'Agenda Id');
        $sheet->setCellValue('C1', 'Teléfono');
        $sheet->setCellValue('D1', 'Folio');
       
        $sheet = $spreadSheet->getActiveSheet();
        $i=2;
        foreach($query as $contrato){

            $sheet->setCellValue("A$i",$contrato->getFechaCreacion());
            $sheet->setCellValue("B$i",$contrato->getAgenda()->getId());
            $sheet->setCellValue("C$i",$contrato->getTelefono());
            $sheet->setCellValue("D$i",$contrato->getFolio());
            
            $i++;
        }

        $sheet->setTitle($titulo);
 
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Csv($spreadSheet);
        $writer->setDelimiter(',');
        $writer->setEnclosure('');
            
        // Create a Temporary file in the system
        
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
    
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
    
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        
    }

    /**
     * @Route("/new", name="contrato_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('create','contrato');
        $contrato = new Contrato();
        $form = $this->createForm(ContratoType::class, $contrato);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contrato);
            $entityManager->flush();

            return $this->redirectToRoute('contrato_index');
        }

        return $this->render('contrato/new.html.twig', [
            'contrato' => $contrato,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/regenerapdfs", name="contrato_regenerapdfs", methods={"GET","POST"})
     */
    public function regenerapdfs(\Knp\Snappy\Pdf $snappy,ContratoRepository $contratoRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','contrato');

        $contratos=$contratoRepository->findBy(['pdf'=>null]);

        foreach($contratos as $contrato){
            $filename = sprintf('Contrato-'.$contrato->getId().'-%s.pdf',rand(0,9000));
        
            $html = $this->renderView('contrato/print.html.twig', array(
                'contrato' => $contrato,
                'Titulo'=>"Contrato"
            ));

            $entityManager = $this->getDoctrine()->getManager();
            $contrato->setPdf($filename);
            $entityManager->persist($contrato);
            $entityManager->flush();

            $snappy->generateFromHtml(
            $html,
            $this->getParameter('url_root'). $this->getParameter('pdf_contratos').$filename
            );    
        }
        return $this->redirectToRoute('contrato_index');
    }
    /**
     * @Route("/{id}", name="contrato_show", methods={"GET"})
     */
    public function show(Contrato $contrato,
                        DiasPagoRepository $diasPagoRepository,
                        ModuloPerRepository $moduloPerRepository,
                        CausaObservacionRepository $causaObservacionRepository): Response
    {
        $this->denyAccessUnlessGranted('view','contrato');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('contrato',$user->getEmpresaActual());
        return $this->render('contrato/show.html.twig', [
            'contrato' => $contrato,
            'agenda'=>$contrato->getAgenda(),
            'pagina'=>$pagina->getNombre(),
            'diasPagos'=>$diasPagoRepository->findAll(),
            'observaciones'=>$causaObservacionRepository->findBy(['contrato'=>$contrato],['fechaRegistro'=>'Desc'])
        ]);
    }

    /**
     * @Route("/{id}/new_rol", name="contrato_new_rol", methods={"GET","POST"})
     */
    public function newRol(Contrato $contrato,Request $request,JuzgadoRepository $juzgadoRepository,ContratoRolRepository $contratoRolRepository): Response
    {
        
        $user=$this->getUser();
        if (null==$request->query->get('mode')){
            $mode='edit';
        }else{
            $mode=$request->query->get('mode');
        }
        
        $contrato_rol = new ContratoRol();
        $contrato_rol->setContrato($contrato);
        $abogado=$this->getDoctrine()->getRepository(Usuario::class)->find($user->getId());
        $contrato_rol->setAbogado($abogado);

        if(isset($_GET['nombre'])){
            $contrato_rol->setNombreRol($_GET['nombre']);
            $contrato_rol->setInstitucionAcreedora($_GET['institucion']);
            $contrato_rol->setJuzgado($juzgadoRepository->find($_GET['juzgado']));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contrato_rol);
            $entityManager->flush();

        }
        return $this->render('contrato/contratoRoles.html.twig', [
            'contrato_rols' => $contratoRolRepository->findBy(['contrato'=>$contrato->getId()]),
            'mode'=>$mode,
           
        ]);
    }
    
    /**
     * @Route("/{id}/del_rol", name="contrato_del_rol",  methods={"DELETE"})
     */
    public function delRol(ContratoRol $contratoRol,Request $request,JuzgadoRepository $juzgadoRepository,ContratoRolRepository $contratoRolRepository): Response
    {
        
        $user=$this->getUser();

        $contrato=$contratoRol->getContrato();
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($contratoRol);
        $entityManager->flush();

        
        return $this->render('contrato/contratoRoles.html.twig', [
            'contrato_rols' => $contratoRolRepository->findBy(['contrato'=>$contrato->getId()]),
           
        ]);
    }
    
    /**
     * @Route("/{id}/edit", name="contrato_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, 
                    Contrato $contrato,
                    JuzgadoRepository $juzgadoRepository,
                    SucursalRepository $sucursalRepository,
                    DiasPagoRepository $diasPagoRepository,
                    UsuarioRepository $usuarioRepository,
                    CuentaRepository $cuentaRepository,
                    ModuloPerRepository $moduloPerRepository,
                    CuotaRepository $cuotaRepository,
                    RegionRepository $regionRepository,
                    ComunaRepository $comunaRepository,
                    CiudadRepository $ciudadRepository,
                    CuentaMateriaRepository $cuentaMateriaRepository,
                    CarteraRepository $carteraRepository,
                    UsuarioCarteraRepository $usuarioCarteraRepository,
                    AgendaContactoRepository $agendaContactoRepository,
                    \Knp\Snappy\Pdf $snappy): Response
    {
        $this->denyAccessUnlessGranted('edit','contrato');
        $user=$this->getUser();

        $pagina=$moduloPerRepository->findOneByName('contrato',$user->getEmpresaActual());
        $juzgados=$juzgadoRepository->findAll();
        $form = $this->createForm(ContratoType::class, $contrato);
        $form->add('fechaPrimeraCuota',DateType::class,array('widget'=>'single_text','html5'=>false));
        $form->add('vigencia');
        $form->add('pagoActual');
        $form->add('isIncorporacion');
        
        $form->add('cuotas', ChoiceType::class,[
            'choices'=>[
                0,
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                8,
                9,
                10,
                11,
                12,
                13,
                14,
                15,
                16,
                17,
                18,
                19,
                20,
                21,
                22,
                23,
                24,

            ]
            ]);
        $form->handleRequest($request);
        //buscamos la primera cuota para sabes si tiene algun pago asociado:::
        $cuota=$cuotaRepository->findOneByUltimaPagada($contrato->getId());
        $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
        
        $tienePago=false;
        if(null != $cuota){
            foreach( $cuota->getPagoCuotas() as $pago){
                $tienePago=true;
            }
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $compania=$_POST['cboCompanias'];
            $contrato->setSucursal($sucursalRepository->find($request->request->get('cboSucursal')));
            $contrato->setDiaPago($request->request->get('chkDiasPago'));
         
            $contrato->setFechaPrimerPago(new \DateTime(date($request->request->get('txtFechaPago')."-1 00:00:00")));

            $contrato->setCregion($regionRepository->find($request->request->get('cboRegion')));
            $contrato->setCciudad($ciudadRepository->find($request->request->get('cboCiudad')));
            $contrato->setCcomuna($comunaRepository->find($request->request->get('cboComuna')));
            $contrato->setSexo($request->request->get('cboSexo'));
            $entityManager = $this->getDoctrine()->getManager();
            $contrato->setPdf(null);
            $entityManager->persist($contrato);
            $entityManager->flush();
            
            $agenda=$contrato->getAgenda();
            //se agrega el canal de contacto
            $agenda->setAgendaContacto($agendaContactoRepository->find($request->request->get('cboContacto')));
            
            $agenda->setNombreCliente($contrato->getNombre());
            $agenda->setTelefonoCliente($contrato->getTelefono());
            $agenda->setEmailCliente($contrato->getEmail());
            $agenda->setReunion($contrato->getReunion());
            $agenda->setCuenta($cuentaRepository->find($compania));
            $entityManager->persist($agenda);
            $entityManager->flush();


            $contratoMees=$contrato->getContratoMees();

            foreach($contratoMees as $contratoMee){
                $entityManager->remove($contratoMee);
                $entityManager->flush();
            }
            if(!$tienePago){
                $detalleCuotas=$contrato->getDetalleCuotas();
                foreach($detalleCuotas as $detalleCuota){
            
                    $entityManager->remove($detalleCuota);
                    $entityManager->flush();
                }
                

                $countCuotas=$contrato->getCuotas();
                $fechaPrimerPago=$contrato->getFechaPrimerPago();
                $diaPago=$contrato->getDiaPago();
                $sumames=0;
                $numeroCuota=1;
                $isAbono=$contrato->getIsAbono();
                $isTotal=$contrato->getIsTotal();
                $isIncorporacion=$contrato->getIsIncorporacion();
                if($isAbono==true || $isTotal==true){
                    $cuota=new Cuota();
                    $cuota->setContrato($contrato);
                    $cuota->setNumero($numeroCuota);
                    $cuota->setFechaPago($contrato->getFechaPrimeraCuota());
                    $cuota->setMonto($contrato->getPrimeraCuota());
                    $entityManager->persist($cuota);
                    $entityManager->flush();
                    $numeroCuota++;

                    $contrato->setProximoVencimiento($contrato->getFechaPrimeraCuota());
                    $entityManager->persist($contrato);
                    $entityManager->flush();
                }
                if($isIncorporacion==true){
                    $contrato->setFechaPrimeraCuota(new \DateTime($request->request->get('txtFechaIncorporacion')));
                    $cuota=new Cuota();
                    $cuota->setContrato($contrato);
                    $cuota->setNumero($numeroCuota);
                    $cuota->setFechaPago($contrato->getFechaPrimeraCuota());
                    $cuota->setMonto($contrato->getPrimeraCuota());
                    $entityManager->persist($cuota);
                    $entityManager->flush();
                    $numeroCuota++;
                    $contrato->setProximoVencimiento($contrato->getFechaPrimeraCuota());
                    $entityManager->persist($contrato);
                    $entityManager->flush();
                }
                $primerPago=date("Y-m-".$diaPago,strtotime($fechaPrimerPago->format('Y-m-d')));
                if(date("n",strtotime($fechaPrimerPago->format('Y-m-d')))==2){
                    if($diaPago==30)
                        $primerPago=date("Y-m-28",strtotime($fechaPrimerPago->format('Y-m-d')));
                }                
                $timePrimerPago=strtotime($primerPago);   
                $timeFechaActual=strtotime(date("Y-m-d"));  
                if($timeFechaActual>=$timePrimerPago){

                    $sumames=1;
                }else{
                    if($isIncorporacion==true){
                        if(date("m",strtotime($fechaPrimerPago->format('Y-m-d')))==date("m")){
                            $sumames=1;
                        }
                    }
                }
                if($contrato->getValorCuota()!=0){
                    for($i=0;$i<$countCuotas;$i++){
                        $cuota=new Cuota();
                        $i_aux=$i;
                        $cuota->setContrato($contrato);
                        $cuota->setNumero($numeroCuota);
                        $ts = mktime(0, 0, 0, date('m',$timePrimerPago) + $sumames+$i_aux, 1,date('Y',$timePrimerPago));
                        $dia=$diaPago;
                        if(date("n",$ts)==2){
                            if($diaPago==30){
                                $dia=date("d",mktime(0,0,0,date('m',$timePrimerPago)+ $sumames+$i_aux+1,1,date('Y',$timePrimerPago))-24);
                            }
                        }
                        $fechaCuota=date("Y-m-d", mktime(0,0,0,date('m',$timePrimerPago) + $sumames+$i_aux,$dia,date('Y',$timePrimerPago)));
                        $cuota->setFechaPago(new \DateTime($fechaCuota));
                        $cuota->setMonto($contrato->getValorCuota());
                        if($numeroCuota==1 && $isIncorporacion==false && $isAbono==false && $isTotal==false){
                            $contrato->setProximoVencimiento(new \DateTime($fechaCuota));
                            $entityManager->persist($contrato);
                            $entityManager->flush();
                        }
                        $entityManager->persist($cuota);
                        $entityManager->flush();
                        $numeroCuota++;
                    }
                }
            }

            $materias = $contrato->getAgenda()->getCuenta()->getCuentaMaterias();
            $materia_id=0;
            foreach ($materias as $materia) {
                $materia_id=$materia->getMateria()->getId();
            }
            error_log("agenda ID: ".$agenda->getCuenta()->getId(),3,$this->getParameter('url_raiz')."/var/log/dev.log");
            
            $cartera=$carteraRepository->findPrimerDisponible($materia_id,$agenda->getCuenta()->getId());

            if(null == $cartera){
                //si no hay carteras para utilizar, se setean en false todos para poder utilizar...
                //$lotes=$lotesRepository->findBy(['empresa'=>$user->getEmpresaActual(),'estado'=>true]);
                #$carteras=$carteraRepository->findBy(['materia'=>$materia_id,'estado'=>true]);
                $carteras=$carteraRepository->findCarterasDisponibles($materia_id,$agenda->getCuenta()->getId());
                foreach($carteras as $_cartera){
                    $_cartera->setUtilizado(false);
                    $entityManager->persist($_cartera);
                    $entityManager->flush();
                }
                //Buscamos nuevamente el primer disponible.
                $cartera=$carteraRepository->findPrimerDisponible($materia_id,$agenda->getCuenta()->getId());
                if($cartera  != null){
                    $cartera->setUtilizado(true);
                    $entityManager->persist($cartera);
                    $entityManager->flush();
                }
                
            }else{
                $cartera->setUtilizado(true);
                $entityManager->persist($cartera);
                $entityManager->flush();
            }

            if($cartera != null){
                $usuarioCarteras=$usuarioCarteraRepository->findBy(['cartera'=>$cartera]);
                foreach ($usuarioCarteras as $usuarioCartera) {
                    //$usuarioCuenta=$usuarioCuentaRepository->findOneBy(['usuario'=>$usuarioCartera->getUsuario(),'cuenta'=>$agenda->getCuenta()]);
                    //$contrato->setTramitador($usuarioCuenta->getUsuario());
                    $contrato->setTramitador($usuarioCartera->getUsuario());
                }
                $contrato->setCartera($cartera);
                $contrato->setCarteraOrden($cartera->getNombre());
                $entityManager->persist($contrato);
                $entityManager->flush();
            }
            return $this->redirectToRoute('contrato_pdf',['id'=>$contrato->getId()]);
        }
        return $this->render('contrato/edit.html.twig', [
            'contrato' => $contrato,
            'agenda' => $contrato->getAgenda(),
            'companias'=>$companias,
            'tienePago'=>$tienePago,
            'agenda'=>$contrato->getAgenda(),
            'form' => $form->createView(),
            'juzgados'=>$juzgados,
            'pagina'=>$pagina->getNombre()." N° ".$contrato->getFolio(),
            'tramitadores'=>$usuarioRepository->findByCuenta($contrato->getAgenda()->getCuenta()->getId(),['usuarioTipo'=>7,'estado'=>1]),
            'diasPagos'=>$diasPagoRepository->findAll(),
            'sucursales'=>$sucursalRepository->findBy(['cuenta'=>$contrato->getAgenda()->getCuenta()->getId()]),
            'regiones'=>$regionRepository->findAll(),
            'cuenta_materias'=>$cuentaMateriaRepository->findBy(['cuenta'=>$contrato->getAgenda()->getCuenta(),'estado'=>1]),
            'contratoMees'=>$contrato->getContratoMees(),
            'agendaContactos'=>$agendaContactoRepository->findAll(),
        ]);
    }
    /**
     * @Route("/{id}/finalizar", name="contrato_finalizar", methods={"GET","POST"})
     */
    public function finalizar(Request $request, 
                            Contrato $contrato,
                            JuzgadoRepository $juzgadoRepository,
                            SucursalRepository $sucursalRepository,
                            DiasPagoRepository $diasPagoRepository,
                            UsuarioRepository $usuarioRepository,
                            UserPasswordEncoderInterface $encoder,
                            UsuarioTipoRepository $usuarioTipoRepository,
                            ConfiguracionRepository $configuracionRepository,
                            ContratoRepository $contratoRepository,
                            ContratoFunciones $contratoFunciones,
                            CuentaRepository $cuentaRepository,
                            LotesRepository $lotesRepository,
                            RegionRepository $regionRepository,
                            ComunaRepository $comunaRepository,
                            CiudadRepository $ciudadRepository,
                            MeeRepository $meeRepository,
                            CuentaMateriaRepository $cuentaMateriaRepository
                            ): Response
    {
        $this->denyAccessUnlessGranted('create','panel_abogado');
        $user=$this->getUser();
        $juzgados=$juzgadoRepository->findAll();
        $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
        $toku = new Toku();
        
        $form = $this->createForm(ContratoType::class, $contrato);
        $form->add('fechaPrimeraCuota',DateType::class,array('widget'=>'single_text','html5'=>false));
        $form->add('vigencia');
        $form->add('pagoActual');
        $form->add('isIncorporacion');
        
        $form->add('cuotas', ChoiceType::class,[
            'choices'=>[
                0,
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                8,
                9,
                10,
                11,
                12,
                13,
                14,
                15,
                16,
                17,
                18,
                19,
                20,
                21,
                22,
                23,
                24,

            ]
            ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $configuracion=$configuracionRepository->find(1);
            $entityManager = $this->getDoctrine()->getManager();


            
            $contrato->setCregion($regionRepository->find($request->request->get('cboRegion')));
            $contrato->setCciudad($ciudadRepository->find($request->request->get('cboCiudad')));
            $contrato->setCcomuna($comunaRepository->find($request->request->get('cboComuna')));
            $contrato->setSexo($request->request->get('cboSexo'));

            $contrato->setDiaPago($request->request->get('chkDiasPago'));
            $contrato->setFechaCreacion(new \DateTime(date("Y-m-d H:i:s")));
            $contrato->setSucursal($sucursalRepository->find($request->request->get('cboSucursal')));
            //$contrato->setTramitador($usuarioRepository->find($request->request->get('cboTramitador')));
            //$contrato->setIdLote($lote);
            
            //Extraer ultimo Folio:::

            /*$ultContrato=$contratoRepository->findLoteMax($user->getEmpresaActual(),'c.folio');

            //sumamos el folio:::
            if($ultContrato){
                $nuevoFolio= $ultContrato->getFolio()+1;
            }else{
                $nuevoFolio=1;
            }

            $contrato->setFolio($nuevoFolio);

            */
            $agenda=$contrato->getAgenda();

            $usuario=$usuarioRepository->findOneBy(['username'=>$contrato->getEmail()]);

            if(!$usuario){
               
                $usuario=new Usuario();
                $usuario->setUsername($contrato->getEmail());
                $password=$usuario->getPassword();
                $encoded=$encoder->encodePassword($usuario,$password);
                $usuario->setPassword($encoded);
                $usuario->setCorreo($contrato->getEmail());
                $usuario->setNombre($contrato->getNombre());
                $usuario->setTelefono($contrato->getTelefono());
                $usuario->setEstado(1);
                $usuario->setFechaActivacion(new \DateTime(date("Y-m-d H:i:s")));
                $usuario->setUsuarioTipo($usuarioTipoRepository->find(9));
                $usuario->setEmpresaActual($agenda->getCuenta()->getEmpresa()->getId());

                $entityManager->persist($usuario);
                $entityManager->flush();
            }
            $contrato->setCliente($usuario);
            $entityManager->persist($contrato);
            $entityManager->flush();
            $agenda->setNombreCliente($contrato->getNombre());
            $agenda->setTelefonoCliente($contrato->getTelefono());
            $agenda->setEmailCliente($contrato->getEmail());
            $agenda->setReunion($contrato->getReunion());
            $entityManager->persist($agenda);
            $entityManager->flush();

            $countCuotas=$contrato->getCuotas();
            $fechaPrimerPago=$contrato->getFechaPrimerPago();
            $diaPago=$contrato->getDiaPago();
            $sumames=0;
            $numeroCuota=1;
            $isAbono=$contrato->getIsAbono();
            $isTotal=$contrato->getIsTotal();
            $isIncorporacion=$contrato->getIsIncorporacion();

            $detalleCuotas=$contrato->getDetalleCuotas();
            foreach($detalleCuotas as $detalleCuota){
            // $contrato->removeDetalleCuota($detalleCuota);
                $entityManager->remove($detalleCuota);
                $entityManager->flush();
            }


            if($isAbono==true || $isTotal==true){
                $cuota=new Cuota();

                $cuota->setContrato($contrato);
                $cuota->setNumero($numeroCuota);
                $cuota->setFechaPago($contrato->getFechaPrimeraCuota());
                $cuota->setMonto($contrato->getPrimeraCuota());

                

                $entityManager->persist($cuota);
                $entityManager->flush();
                $numeroCuota++;

                $contrato->setProximoVencimiento($contrato->getFechaPrimeraCuota());
                $entityManager->persist($contrato);
                $entityManager->flush();
            }

            if($isIncorporacion==true){

                
                $contrato->setFechaPrimeraCuota(new \DateTime($request->request->get('txtFechaIncorporacion')));
               
                $cuota=new Cuota();

                $cuota->setContrato($contrato);
                $cuota->setNumero($numeroCuota);
                $cuota->setFechaPago($contrato->getFechaPrimeraCuota());
                $cuota->setMonto($contrato->getPrimeraCuota());
                $entityManager->persist($cuota);
                $entityManager->flush();
                $numeroCuota++;
                $contrato->setProximoVencimiento($contrato->getFechaPrimeraCuota());
                $entityManager->persist($contrato);
                $entityManager->flush();  
            }
            $primerPago=date("Y-m-".$diaPago,strtotime($fechaPrimerPago->format('Y-m-d')));
            if(date("n",strtotime($fechaPrimerPago->format('Y-m-d')))==2){
                if($diaPago==30)
                    $primerPago=date("Y-m-28",strtotime($fechaPrimerPago->format('Y-m-d')));
            }
            $timePrimerPago=strtotime($primerPago);
            $timeFechaActual=strtotime(date("Y-m-d"));
            if($timeFechaActual>=$timePrimerPago){
                $sumames=1;
            }else{
                if($isIncorporacion==true){
                    if(date("m",strtotime($fechaPrimerPago->format('Y-m-d')))==date("m")){
                        $sumames=1;
                    }
                }
            }
            if($contrato->getValorCuota()!=0){
                for($i=0;$i<$countCuotas;$i++){
                    $cuota=new Cuota();
                    $i_aux=$i;
                    $cuota->setContrato($contrato);
                    $cuota->setNumero($numeroCuota);
                    $ts = mktime(0, 0, 0, date('m',$timePrimerPago) + $sumames+$i_aux, 1,date('Y',$timePrimerPago));
                    $dia=$diaPago;
                    if(date("n",$ts)==2){
                        if($dia==30){
                            $dia=date("d",mktime(0,0,0,date('m',$timePrimerPago)+ $sumames+$i_aux+1,1,date('Y',$timePrimerPago))-24);
                        }
                    }
                    $fechaCuota=date("Y-m-d", mktime(0,0,0,date('m',$timePrimerPago) + $sumames+$i_aux,$dia,date('Y',$timePrimerPago)));
                    $cuota->setFechaPago(new \DateTime($fechaCuota));
                    $cuota->setMonto($contrato->getValorCuota());
                    if($numeroCuota==1 && $isIncorporacion==false && $isAbono==false && $isTotal==false){
                        $contrato->setProximoVencimiento(new \DateTime($fechaCuota));
                        $entityManager->persist($contrato);
                        $entityManager->flush();
                    }
                    $entityManager->persist($cuota);
                    $entityManager->flush();
                    $numeroCuota++;
                }
            }
    
          
            return $this->redirectToRoute('contrato_pdf',['id'=>$contrato->getId()]);
        }

        return $this->render('contrato/finalizar.html.twig', [
            'contrato' => $contrato,
            'agenda'=> $contrato->getAgenda(),
            
            'companias'=>$companias,
            'form' => $form->createView(),
            'tienePago'=>false,
            'juzgados'=>$juzgados,
            'pagina'=>"Revise los datos para finalizar",
            'tramitadores'=>$usuarioRepository->findByCuenta($contrato->getAgenda()->getCuenta()->getId(),['usuarioTipo'=>7,'estado'=>1]),
            'diasPagos'=>$diasPagoRepository->findAll(),
            'sucursales'=>$sucursalRepository->findBy(['cuenta'=>$contrato->getAgenda()->getCuenta()->getId()]),
            'regiones'=>$regionRepository->findAll(),
            'cuenta_materias'=>$cuentaMateriaRepository->findBy(['cuenta'=>$contrato->getAgenda()->getCuenta(),'estado'=>1]),
            'contratoMees'=>$contrato->getContratoMees(),
        ]);
    }

    /**
     * @Route("/{id}/reasignar", name="contrato_reasignar", methods={"GET","POST"})
     */
    public function reasignar(Request $request, 
                            Contrato $contrato,
                            UsuarioRepository $usuarioRepository
                            ): Response
    {
        $this->denyAccessUnlessGranted('create','contrato_reasignar');
        $user=$this->getUser();
       
        $cerradores = $usuarioRepository->findBy(['usuarioTipo'=>6,'estado'=>1,'empresaActual'=>$user->getEmpresaActual()]); 

        if($request->request->get('cboCerrador')){
            $agenda=$contrato->getAgenda();
            $agenda->setAbogado($usuarioRepository->find($request->request->get('cboCerrador')));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($agenda);
            $entityManager->flush();
            return $this->redirect($request->headers->get('referer'));
        }
        return $this->render('contrato/_reasignar.html.twig', [
            'contrato' => $contrato,
            'cerradores'=>$cerradores,         
        ]);
    }

   
    /**
     * @Route("/{id}/pdf", name="contrato_pdf", methods={"GET","POST"})
     */
    public function pdf(Contrato $contrato,ContratoMeeRepository $contratoMeeRepository)
    {
        $this->denyAccessUnlessGranted('view','contrato');
        $filename = sprintf('Contrato-'.$contrato->getId().'-%s.pdf',rand(0,9000));
       
        $html = $this->renderView('contrato/print.html.twig', array(
            'contrato' => $contrato,
            'Titulo'=>"Contrato",
            'mees'=>$contratoMeeRepository->findByContrato($contrato->getId()),
        ));
        $entityManager = $this->getDoctrine()->getManager();
        $contrato->setPdf($filename);
        $entityManager->persist($contrato);
        $entityManager->flush();
        // Configure Dompdf según sus necesidades
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'helvetica');
        // Crea una instancia de Dompdf con nuestras opciones
        $dompdf = new Dompdf($pdfOptions);
        $dompdf->getOptions()->setChroot(array($this->getParameter('url_raiz')));
        // Cargar HTML en Dompdf
        $dompdf->loadHtml($html);
        // (Opcional) Configure el tamaño del papel y la orientación 'vertical' o 'vertical'
        $dompdf->setPaper('letter', 'portrait');
        // Renderiza el HTML como PDF
        $dompdf->render();
        $file=$dompdf->output();
        file_put_contents($this->getParameter('url_root'). $this->getParameter('pdf_contratos').$filename,$file);
        // Envíe el PDF generado al navegador (descarga forzada)
        /*$dompdf->stream($filename, [
            "Attachment" => true
        ]);*/
        return $this->redirectToRoute('contrato_index');
    }
    /**
     * @Route("/{id}/terminar", name="contrato_terminar", methods={"GET","POST"})
     */
    function terminar(Contrato $contrato,
                    DiasPagoRepository $diasPagoRepository,
                    ModuloPerRepository $moduloPerRepository,
                    Request $request,
                    ContratoFunciones $contratoFunciones): Response
    {
        $this->denyAccessUnlessGranted('create','terminos');
        $user=$this->getUser();
        
        $pagina=$moduloPerRepository->findOneByName('contrato',$user->getEmpresaActual());

        if(null !== $request->query->get('status')){
            $error_toast=$contratoFunciones->terminarContrato($contrato,$request->query->get('status'),$request->request->get('txtObservacion'));
           
            return $this->redirectToRoute('contrato_index',['error_toast'=>$error_toast]);

        }
        
        return $this->render('contrato/show.html.twig', [
            'contrato' => $contrato,
            'agenda'=>$contrato->getAgenda(),
            'pagina'=>$pagina->getNombre(),
            'diasPagos'=>$diasPagoRepository->findAll(),
            'metodo'=>"T",
            
        ]);
    }

     /**
     * @Route("/{id}/ciudad", name="contrato_ciudad", methods={"GET","POST"})
     */
    function ciudad(Region $region, CiudadRepository $ciudadRepository,Request $request): Response
    {
        $ciudad_def=null;
       
        if(null != $request->query->get('ciudad')){
            $ciudad_def=$request->query->get('ciudad');
        }
        $ciudades=$ciudadRepository->findBy(['region'=>$region->getId()]);
        return $this->render('contrato/_ciudades.html.twig', [
            'ciudades' => $ciudades,
            'ciudad_def'=>$ciudad_def,
        ]);
    }

    /**
     * @Route("/{id}/comuna", name="contrato_comuna", methods={"GET","POST"})
     */
    function comuna(Ciudad $ciudad, ComunaRepository $comunaRepository,Request $request): Response
    {
        
        $comuna_def=null;
        if(null != $request->query->get('comuna')){
            $comuna_def=$request->query->get('comuna');
        }
        $comunas=$comunaRepository->findBy(['ciudad'=>$ciudad->getId()]);
        return $this->render('contrato/_comunas.html.twig', [
            'comunas' => $comunas,
            'comuna_def'=>$comuna_def,
        ]);
    }
    /**
     * @Route("/{id}/linea_tiempo", name="contrato_linea_tiempo", methods={"GET","POST"})
     */
    function lineaTiempo(Contrato $contrato, 
                        ModuloPerRepository $moduloPerRepository,
                        MateriaEstrategiaRepository $materiaEstrategiaRepository,
                        CuentaMateriaRepository $cuentaMateriaRepository,
                        MateriaCorteRepository $materiaCorteRepository,
                        ContratoObservacionRepository $contratoObservacionRepository): Response
    {
        $this->denyAccessUnlessGranted('create','linea_tiempo');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('linea_tiempo',$user->getEmpresaActual());

        $cuenta=$contrato->getAgenda()->getCuenta();
        $cuenta_materia=$cuentaMateriaRepository->findOneBy(['cuenta'=>$cuenta->getId(),'estado'=>1]);

        return $this->render('contrato/lineaTiempo.html.twig', [
            'contrato' => $contrato,
            'pagina'=>$pagina->getNombre(),
            'cortes'=>$materiaCorteRepository->findBy(['materia'=>$cuenta_materia->getMateria()->getId()]),
            'juzgados' => $cuenta->getJuzgadoCuentas(),
            'servicios'=>$materiaEstrategiaRepository->findBy(['materia'=>$cuenta_materia->getMateria()->getId(),'estado'=>1]),
            'observaciones'=>$contratoObservacionRepository->findBy(['contrato'=>$contrato],['fechaRegistro'=>'Desc']),
        ]);
    }
    /**
     * @Route("/{id}/linea_tiempo_reporte", name="contrato_linea_tiempo_reporte", methods={"GET","POST"})
     */
    function lineaTiempoReporte(Causa $causa, 
                        ModuloPerRepository $moduloPerRepository,
                        EstrategiaJuridicaReporteRepository $estrategiaJuridicaReporte,
                         ContratoObservacionRepository $contratoObservacionRepository,
                        EstrategiaJuridicaReporteArchivosRepository $estrategiaJuridicaReporteArchivosRepository): Response
    {
        $this->denyAccessUnlessGranted('create','linea_tiempo');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('linea_tiempo',$user->getEmpresaActual());

        $contrato = $causa->getAgenda()->getContrato();
        $reportes=$estrategiaJuridicaReporte->findBy(['estrategiaJuridica'=>$causa->getMateriaEstrategia()->getEstrategiaJuridica()],['id'=>'Asc']);

        return $this->render('contrato/lineaTiempoReporte.html.twig', [
            'contrato' => $contrato,
            'causa' => $causa,
            'reportes' => $reportes,
            'pagina'=>$pagina->getNombre(),
             'observaciones'=>$contratoObservacionRepository->findBy(['contrato'=>$contrato],['fechaRegistro'=>'Desc']),
            'archivosReporte'=>$estrategiaJuridicaReporteArchivosRepository->findBy(['causa'=>$causa],['fechaYHoraCarga'=>'Desc']) ,
        ]);
    }
    /**
     * @Route("/{id}/linea_tiempo_reporte_subir_archivo", name="contrato_linea_tiempo_reporte_subir_archivo", methods={"GET","POST"})
     */
    function lineaTiempoReporteSubirArchivo(Causa $causa, 
                        Request $request,
                        EstrategiaJuridicaReporteRepository $estrategiaJuridicaReporteRepository
                        ): Response
    {
        $this->denyAccessUnlessGranted('create','linea_tiempo');
        $user=$this->getUser();
       
        if($request->files->get('archivoReporte')){
            $archivo=$request->files->get('archivoReporte');
            $reporteId=$request->request->get('reporte');
            $originalFilename = pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME);
            
            $newFilename = $originalFilename.'-'.uniqid().'.'.$archivo->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $ruta= $this->getParameter('url_root').$this->getParameter('causa_reportes');
                            
                $archivo->move(
                    $ruta,
                    $newFilename
                );
                // updates the 'brochure' property to store the PDF file name
                // instead of its contents
                $reporteArchivo=new EstrategiaJuridicaReporteArchivos();
                $reporteArchivo->setCausa($causa);
                $estrategiaJuridicaReporte=$estrategiaJuridicaReporteRepository->find($reporteId);
                $reporteArchivo->setEstrategiaJuridicaReporte($estrategiaJuridicaReporte);
                $reporteArchivo->setArchivo($newFilename);
                $reporteArchivo->setNombre($originalFilename);
                $reporteArchivo->setUsuarioCreacion($user);
                $reporteArchivo->setFechaYHoraCarga(new \DateTime(date("Y-m-d H:i")));
                $em=$this->getDoctrine()->getManager();
                $em->persist($reporteArchivo);
                $em->flush();
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
                print_r("Error al subir el archivo: ".$e->getMessage());
                exit;
            }

            
        }        
        return $this->redirectToRoute('contrato_linea_tiempo_reporte',['id'=>$causa->getId()]);
    }
    /**
     * @Route("/{id}/linea_tiempo_detalle_agregarDetalleCuaderno", name="contrato_linea_tiempo_detalle_agregarDetalleCuaderno", methods={"GET","POST"})
     */
    function agregarDetalleCuaderno(Causa $causa,
                                    Request $request, 
                                    DetalleCuadernoRepository $detalleCuadernoRepository,
                                    CuadernoRepository $cuadernoRepository,
                                    ActuacionRepository $actuacionRepository,
                                    AnexoProcesalRepository $anexoProcesalRepository): Response
    {

        $user = $this->getUser();
        $cuaderno=null;
        $actuacion=null;
        $anexoProcesal=null;
        $observacion="";
        if($request->request->get("cboCuaderno")) $cuaderno=$cuadernoRepository->find($request->request->get("cboCuaderno"));
        if($request->request->get("cboActuacion")) $actuacion=$actuacionRepository->find($request->request->get("cboActuacion"));
        if($request->request->get("cboAnexoProcesal")) $anexoProcesal=$anexoProcesalRepository->find($request->request->get("cboAnexoProcesal"));
        if($request->request->get("txtObservacion")) $observacion=$request->request->get("txtObservacion");

        //Buscamos si existe coincidencias.
        $detalleCuaderno = $detalleCuadernoRepository->findOneBy(["causa"=>$causa,"cuaderno"=>$cuaderno,"actuacion"=>$actuacion,"anexoProcesal"=>$anexoProcesal]);

        if(!$detalleCuaderno){
            $detalleCuaderno=new DetalleCuaderno();
            $detalleCuaderno->setUsuarioCreacion($user);
            $detalleCuaderno->setFechaCreacion(new \DateTime(date("Y-m-d H:i")));
            $detalleCuaderno->setCausa($causa);
            $detalleCuaderno->setCuaderno($cuaderno);
            $detalleCuaderno->setActuacion($actuacion);
            if(!null==$anexoProcesal)
                $detalleCuaderno->setAnexoProcesal($anexoProcesal);

            $em=$this->getDoctrine()->getManager();
            $em->persist($detalleCuaderno);
            $em->flush();

            if($observacion!=""){
                $causaObservacion = new CausaObservacion();
                

                $causaObservacion->setCausa($causa);
                $causaObservacion->setUsuarioRegistro($user);
                $causaObservacion->setContrato($causa->getAgenda()->getContrato());
                $causaObservacion->setFechaRegistro(new \DateTime(date("Y-m-d H:i")));
                $causaObservacion->setObservacion($observacion);
                $causaObservacion->setDetalleCuaderno($detalleCuaderno);

                
                
                $em->persist($causaObservacion);
                $em->flush();

                $files=$request->files->get('file-actuacion');

                /*if ($files) {
                    foreach ($files as $file) {
                        if ($file) {
                            $nombre = uniqid().'.'.$file->guessExtension();
                            $nombreOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                            $ruta= $this->getParameter('url_root').$this->getParameter('archivos_contratos');
                            $file->move($ruta, $nombre);
                            $causaObservacionArchivo = new CausaObservacionArchivo();
                            $causaObservacionArchivo->setCausaObservacion($causaObservacion);
                            $causaObservacionArchivo->setNombreArchivo($nombre);
                            $causaObservacionArchivo->setNombreOriginal($nombreOriginal);
                            $em->persist($causaObservacionArchivo);
                            $em->flush();
                            // aquí puedes guardar cada archivo en BD si quieres
                        }
                    }
                }*/

                if (!empty($_FILES['file-actuacion'])) {
                    $files = $_FILES['file-actuacion'];
                    $count = is_array($files['name']) ? count($files['name']) : 0;

                    for ($i = 0; $i < $count; $i++) {
                        $error = $files['error'][$i];
                        if ($error !== UPLOAD_ERR_OK) {
                            // gestionar error (size, partial, etc.)
                            continue;
                        }

                        $tmpName = $files['tmp_name'][$i];
                        $originalName = $files['name'][$i];
                        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                        // validar extensión
                        if (!in_array($ext, ['pdf','doc','docx','jpg','png'])) {
                            // rechazar o continuar
                            continue;
                        }

                        // validar tamaño (ej. max 5MB)
                        if ($files['size'][$i] > 5 * 1024 * 1024) {
                            continue;
                        }

                        if (is_uploaded_file($tmpName)) {
                            $newName = uniqid() . '.' . $ext;
                            $dest = $this->getParameter('url_root') . $this->getParameter('archivos_contratos') . $newName;
                            if (move_uploaded_file($tmpName, $dest)) {
                                // persistir registro en BD (ej. CausaObservacionArchivo)
                                $causaObservacionArchivo = new CausaObservacionArchivo();
                            $causaObservacionArchivo->setCausaObservacion($causaObservacion);
                            $causaObservacionArchivo->setNombreArchivo($newName);
                            $causaObservacionArchivo->setNombreOriginal($originalName);
                            $em->persist($causaObservacionArchivo);
                            $em->flush();
                            }
                        }
                    }
                }
                
            }
        }
        
        return $this->redirectToRoute('contrato_linea_tiempo_detalle',["id"=>$causa->getId(),"cuadernoId"=>$cuaderno->getId()]);
    }
    /**
     * @Route("/linea_tiempo_detalle_asignarObservacionDetalleCuaderno", name="contrato_linea_tiempo_detalle_asignarObservacionDetalleCuaderno", methods={"GET","POST"})
     */
    function asignarObservacionDetalleCuaderno(Request $request, 
                                    DetalleCuadernoRepository $detalleCuadernoRepository,
                                    CuadernoRepository $cuadernoRepository,
                                    ActuacionRepository $actuacionRepository,
                                    AnexoProcesalRepository $anexoProcesalRepository,
                                    CausaObservacionRepository $causaObservacionRepository): Response
    {

        $user = $this->getUser();
        $cuaderno=null;
        $actuacion=null;
        $anexoProcesal=null;
        if($request->request->get("cboCuaderno-asignar")) $cuaderno=$cuadernoRepository->find($request->request->get("cboCuaderno-asignar"));
        if($request->request->get("cboActuacion-asignar")) $actuacion=$actuacionRepository->find($request->request->get("cboActuacion-asignar"));
        if($request->request->get("cboAnexoProcesal-asignar")) $anexoProcesal=$anexoProcesalRepository->find($request->request->get("cboAnexoProcesal-asignar"));
        if($request->request->get("idObservacion")) $causaObservacion=$causaObservacionRepository->find($request->request->get("idObservacion"));
        
        //Buscamos si existe coincidencias.
        $detalleCuaderno = $detalleCuadernoRepository->findOneBy(["causa"=>$causaObservacion->getCausa(),"cuaderno"=>$cuaderno,"actuacion"=>$actuacion,"anexoProcesal"=>$anexoProcesal]);
         $em=$this->getDoctrine()->getManager();
        if(!$detalleCuaderno){
            $detalleCuaderno=new DetalleCuaderno();
            $detalleCuaderno->setUsuarioCreacion($user);
            $detalleCuaderno->setFechaCreacion(new \DateTime(date("Y-m-d")));
            $detalleCuaderno->setCausa($causaObservacion->getCausa());
            $detalleCuaderno->setCuaderno($cuaderno);
            $detalleCuaderno->setActuacion($actuacion);
            if(! null==$anexoProcesal)
                $detalleCuaderno->setAnexoProcesal($anexoProcesal);

           
            $em->persist($detalleCuaderno);
            $em->flush();

            
        }
        $causaObservacion->setDetalleCuaderno($detalleCuaderno);
        $em->persist($causaObservacion);
        $em->flush();

        return $this->redirectToRoute('contrato_linea_tiempo_detalle',["id"=>$causaObservacion->getCausa()->getId()]);
    }
    /**
     * @Route("/{id}/linea_tiempo_detalle_agregarObservacionCuaderno", name="contrato_linea_tiempo_detalle_agregarObservacionCuaderno", methods={"GET","POST"})
     */
    function agregarObservacionCuaderno(DetalleCuaderno $detalleCuaderno,
                                    Request $request
                                    ): JsonResponse
    {

        $user = $this->getUser();
        $observacion = "";
        $file="";
        $exito = 0;
        $mensaje="";
        try{
            if($request->request->get('observacion')){
                $observacion=$request->request->get('observacion');

                $causaObservacion = new CausaObservacion();
                

                $causaObservacion->setCausa($detalleCuaderno->getCausa());
                $causaObservacion->setUsuarioRegistro($user);
                $causaObservacion->setContrato($detalleCuaderno->getCausa()->getAgenda()->getContrato());
                $causaObservacion->setFechaRegistro(new \DateTime(date("Y-m-d H:i")));
                $causaObservacion->setObservacion($observacion);
                $causaObservacion->setDetalleCuaderno($detalleCuaderno);

                $em=$this->getDoctrine()->getManager();
                $em->persist($causaObservacion);
                $em->flush();
                
                $files=$request->files->get('files');

                if ($files) {
                    foreach ($files as $file) {
                        if ($file) {
                            $nombre = uniqid().'.'.$file->guessExtension();
                            $nombreOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                            $ruta= $this->getParameter('url_root').$this->getParameter('archivos_contratos');
                            $file->move($ruta, $nombre);
                            $causaObservacionArchivo = new CausaObservacionArchivo();
                            $causaObservacionArchivo->setCausaObservacion($causaObservacion);
                            $causaObservacionArchivo->setNombreArchivo($nombre);
                            $causaObservacionArchivo->setNombreOriginal($nombreOriginal);
                            $em->persist($causaObservacionArchivo);
                            $em->flush();
                            // aquí puedes guardar cada archivo en BD si quieres
                        }
                    }
                }

                $exito=1;
            } else{
                $exito=0;
                 $mensaje="No hay observaciones";
            }
        }catch(Exception $ex){
            $exito=0;
            $mensaje=$ex->getMessage();
        }
        
        
        return $this->json(["exito"=>$exito,"mensaje"=>$mensaje]);  
    }
    /**
     * @Route("/{id}/linea_tiempo_detalle_observacionesCuaderno", name="contrato_linea_tiempo_detalle_observacionesCuaderno", methods={"GET","POST"})
     */
    function obtenerObservacionCuaderno(DetalleCuaderno $detalleCuaderno,
                                    Request $request, 
                                    CausaObservacionRepository $causaObservacionRepository
                                    ): Response
    {
        
        return $this->render('contrato/_observacionesDetalleCuaderno.html.twig', [
            'causaObservaciones'=>$causaObservacionRepository->findBy(['detalleCuaderno'=>$detalleCuaderno],['fechaRegistro'=>'Desc']),

            
        ]);
    }
    /**
     * @Route("/{id}/linea_tiempo_detalle_validarCuaderno", name="contrato_linea_tiempo_detalle_validarCuaderno", methods={"GET","POST"})
     */
    function validarCuaderno(Causa $causa,
                            Request $request,
                            DetalleCuadernoRepository $detalleCuadernoRepository, 
                            CuadernoRepository $cuadernoRepository
                            ): JsonResponse
    {

        if($request->request->get('cuadernoId')) $cuaderno=$cuadernoRepository->find($request->request->get('cuadernoId'));
        
        if(!$cuaderno->getDependeCuaderno()){
            return $this->json(["exito"=>1,"mensaje"=>"El cuaderno no depende de otro cuaderno."]);  
        }
        $detallesCuaderno = $detalleCuadernoRepository->findBy(["causa"=>$causa,"cuaderno"=>$cuaderno->getDependeCuaderno()]);
        foreach ($detallesCuaderno as $detalle) {
            if(count($detalle->getCausaObservaciones())>0){
                $exito=1;
                $mensaje="El cuaderno padre tiene datos.";
                return $this->json(["exito"=>$exito,"mensaje"=>$mensaje]);
            }
        }
        $exito=0;
        $mensaje="El cuaderno padre no tiene datos.";
        
        
        return $this->json(["exito"=>$exito,"mensaje"=>$mensaje]);  
    }
    /**
     * @Route("/{id}/linea_tiempo_detalle_existeActuacion", name="contrato_linea_tiempo_detalle_existeActuacion", methods={"GET","POST"})
     */
    function existeActuacion(Causa $causa,
                            Request $request,
                            DetalleCuadernoRepository $detalleCuadernoRepository,
                            ActuacionRepository $actuacionRepository,
                            AnexoProcesalRepository $anexoProcesalRepository
                            ): JsonResponse
    {
        
        $actuacion=null;
        $anexoProcesal=null;
        $detalleCuaderno=null;
        if($request->request->get('actuacionId')) $actuacion=$actuacionRepository->find($request->request->get('actuacionId'));

        if($request->request->get('anexoProcesalId')) $anexoProcesal=$anexoProcesalRepository->find($request->request->get('anexoProcesalId'));
        if($actuacion==null){
            return $this->json(["exito"=>0,"mensaje"=>"La actuacion no existe."]);  
        }
        $cuaderno=$actuacion->getCuaderno();

        if($anexoProcesal){
            $detalleCuaderno = $detalleCuadernoRepository->findOneBy(["causa"=>$causa,"cuaderno"=>$cuaderno,"actuacion"=>$actuacion,"anexoProcesal"=>$anexoProcesal]);
            if($detalleCuaderno){
                $exito=0;
                $mensaje="La actuacion y anexo procesal ya existen en el cuaderno.";
                return $this->json(["exito"=>$exito,"mensaje"=>$mensaje]);  
            }
        }else{
            $detalleCuaderno = $detalleCuadernoRepository->findOneBy(["causa"=>$causa,"cuaderno"=>$cuaderno,"actuacion"=>$actuacion]);
            if($detalleCuaderno){
                $exito=0;
                $mensaje="La actuacion ya existe en el cuaderno.";
                return $this->json(["exito"=>$exito,"mensaje"=>$mensaje]);  
            }
        }


        
        $exito=1;
        $mensaje="La actuacion no existe en el cuaderno.";
        
        
        return $this->json(["exito"=>$exito,"mensaje"=>$mensaje]);  
    }
    /**
     * @Route("/{id}/obtener_actuaciones", name="contrato_obtener_actuaciones", methods={"GET","POST"})
     */
    function obtenerActuaciones(Cuaderno $cuaderno, ActuacionRepository $actuacionRepository): JsonResponse
    {
        $actuaciones=$actuacionRepository->findBy(["cuaderno"=>$cuaderno]);
        $result = [];
        foreach ($actuaciones as $actuacion) {
            $result[] = [
                'id' => $actuacion->getId(),
                'nombre' => $actuacion->getNombre(), // Cambia getNombre() por el método correcto si el campo se llama distinto
            ];
        }
        return $this->json($result);        
    }
    /**
     * @Route("/{id}/obtener_anexo_procesal", name="contrato_obtener_anexo_procesal", methods={"GET","POST"})
     */
    function obtenerAnexoProcesal(Actuacion $actuacion, ActuacionAnexoProcesalRepository $actuacionAnexoProcesalRepository): JsonResponse
    {
        $actuacionAnexoProcesales=$actuacionAnexoProcesalRepository->findBy(["actuacion"=>$actuacion]);
        $result = [];
        foreach ($actuacionAnexoProcesales as $anexo) {
            $result[] = [
                'id' => $anexo->getAnexoProcesal()->getId(),
                'nombre' => $anexo->getAnexoProcesal()->getNombre(), // Cambia getNombre() por el método correcto si el campo se llama distinto
            ];
        }
        return $this->json($result);
    }
    /**
     * @Route("/{id}/linea_tiempo_detalle", name="contrato_linea_tiempo_detalle", methods={"GET","POST"})
     */
    function lineaTiempoDetalle(Causa $causa, Request $request,
                                LineaTiempoTerminadaRepository $lineaTiempoTerminadaRepository,
                                ModuloPerRepository $moduloPerRepository,
                                CausaObservacionRepository $causaObservacionRepository,
                                CuadernoRepository $cuadernoRepository,
                                DetalleCuadernoRepository $detalleCuadernoRepository
                                ): Response
    {
        $this->denyAccessUnlessGranted('create','linea_tiempo');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('linea_tiempo',$user->getEmpresaActual());
        $fechaUltimoIngreso=$causa->getFechaUltimoIngreso();
        
        $cuadernoId=$request->query->get('cuadernoId',0);
        
        $causa->setFechaUltimoIngreso(new DateTime(date('Y-m-d h:i:s')));
        $em=$this->getDoctrine()->getManager();
        $em->persist($causa);
        $em->flush();

        $cuadernos=$cuadernoRepository->findBy(["estrategiaJuridica"=>$causa->getMateriaEstrategia()->getEstrategiaJuridica()->getId()]);

        $mensaje = new Mensaje();
        $form = $this->createForm(MensajeType::class, $mensaje);
        $mensaje->setUsuarioDestino($user);
        $mensaje->setFechaCreacion(new DateTime(date("Y-m-d H:i")));
        $mensaje->setUsuarioRegistro($user);
        $mensaje->setLeido(false);
        $form->add('fechaAviso',DateType::class,array('widget'=>'single_text','html5'=>false));
        $form->add('observacion');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $mensaje->setObservacion($causa->getIdCausa()." - ". $mensaje->getObservacion());
            $entityManager->persist($mensaje);
            $entityManager->flush();

            return $this->redirectToRoute('mensaje_index');
        }

        
        $contrato=$causa->getAgenda()->getContrato();
        $terminados=$lineaTiempoTerminadaRepository->findBy(['causa'=>$causa->getId(),'estado'=>1]);
        $LineaTiempoTerminados=$lineaTiempoTerminadaRepository->findBy(['causa'=>$causa->getId()]);
        return $this->render('contrato/lineaTiempoDetalle.html.twig', [
            'causa' => $causa,
            'contrato'=>$contrato,
            'cuadernoId'=>$cuadernoId,
            'form'=>$form->createView(),
            'terminados'=>$terminados,
            'detalleCuadernos'=>$detalleCuadernoRepository->findBy(['causa'=>$causa],['fechaCreacion'=>'desc']),
            'lineaTiempoTerminados'=>$LineaTiempoTerminados,
            'pagina'=>$pagina->getNombre(),
            'observacionesAntiguas'=>$causaObservacionRepository->findBy(['contrato'=>$contrato,'causa'=>$causa,'detalleCuaderno'=>null],['fechaRegistro'=>'Desc']),
            'fechaUltimoIngreso'=>$fechaUltimoIngreso,
            'cuadernos'=>$cuadernos
            
        ]);
    }


    /**
     * @Route("/{id}/linea_tiempo_observacion", name="contrato_linea_tiempo_observacion", methods={"GET","POST"})
     */
    function lineaTiempoObservacion(Causa $causa, 
                                    LineaTiempoEtapasRepository $lineaTiempoEtapaRepository, 
                                    LineaTiempoTerminadaRepository $lineaTiempoTerminadaRepository ,
                                    Request $request): Response
    {
        //$this->denyAccessUnlessGranted('create','terminos');
        $user=$this->getUser();
        $contrato=$causa->getAgenda()->getContrato();
        
        $terminada=$lineaTiempoTerminadaRepository->findOneBy(['causa'=>$causa->getId(),'estado'=>1],array('id'=>'desc'));

        if($request->request->get('hdEtapa')!=null){
            $etapa1=$lineaTiempoEtapaRepository->find($request->request->get('hdEtapa'));
            
        }

        $terminada!=null?$etapaInicio=$terminada->getLineaTiempoEtapas()->getId():$etapaInicio=0;

                

       

        $etapas=$lineaTiempoEtapaRepository->findByRango($etapaInicio,$etapa1->getId(), $causa->getMateriaEstrategia()->getEstrategiaJuridica()->getLineaTiempo()->getId());
        

        foreach ($etapas as $etapa) {
            $terminado=new LineaTiempoTerminada();
            $terminado->setLineaTiempoEtapas($etapa);
            $terminado->setFecha(new \DateTime(date('Y-m-d H:i:s')));
            $terminado->setUsuarioRegistro($user);
            $terminado->setCausa($causa);

            if($request->request->get('txtObservacion')!=null){
                $terminado->setObservacion($request->request->get('txtObservacion'));
            }
            if($request->request->get('hdEstado')!=null){
                $terminado->setEstado($request->request->get('hdEstado'));
            }
            
            
            $em=$this->getDoctrine()->getManager();
            $em->persist($terminado);
            $em->flush();
        }

            
        
        
        return $this->redirectToRoute('contrato_linea_tiempo_detalle',['id'=>$causa->getId()]);
    }

   
    /**
     * @Route("/{id}/linea_tiempo_finalizar", name="contrato_linea_tiempo_finalizar", methods={"GET","POST"})
     */
    function lineaTiempoFinalizar(Causa $causa): Response
    {
        //$this->denyAccessUnlessGranted('create','terminos');
        $user=$this->getUser();
        $causa->setCausaFinalizada(1);
        $causa->setFechaFInalizado(new \DateTime(date('Y-m-d H:i:s')));
        
        $em=$this->getDoctrine()->getManager();
        $em->persist($causa);
        $em->flush();

        return $this->redirectToRoute('contrato_linea_tiempo_detalle',['id'=>$causa->getId()]);
    }
    /**
     * @Route("/{id}/observacion", name="contrato_observacion", methods={"GET","POST"})
     */
    function observacion(Causa $causa, 
                        LineaTiempoEtapasRepository $lineaTiempoEtapaRepository, 
                        LineaTiempoTerminadaRepository $lineaTiempoTerminadaRepository ,
                        Request $request): Response
    {
        //$this->denyAccessUnlessGranted('create','terminos');
        $user=$this->getUser();
        $contrato=$causa->getAgenda()->getContrato();

        $lineaTiempo=$causa->getMateriaEstrategia()->getEstrategiaJuridica()->getLineaTiempo();
        if($request->request->get('txtObservacion')!=null){
            $observacion=new CausaObservacion();
            $observacion->setContrato($contrato);
            $observacion->setObservacion($request->request->get('txtObservacion'));
            $observacion->setFechaRegistro(new DateTime(date('Y-m-d H:i:s')));
            $observacion->setUsuarioRegistro($user);
            $observacion->setCausa($causa);
            
            $em=$this->getDoctrine()->getManager();
            $em->persist($observacion);
            $em->flush();

        }

        return $this->redirectToRoute('contrato_linea_tiempo',['id'=>$contrato->getId()]);
    }
    /**
     * @Route("/{id}/observacion_sac", name="contrato_observacion_sac", methods={"GET","POST"})
     */
    function observacionSac(Contrato $contrato, 
                            Request $request): Response
    {
        //$this->denyAccessUnlessGranted('create','terminos');
        $user=$this->getUser();
        
        if($request->request->get('txtObservacion')!=null){
            $observacion=new ContratoObservacion();
            $observacion->setContrato($contrato);
            $observacion->setObservacion($request->request->get('txtObservacion'));
            $observacion->setFechaRegistro(new DateTime(date('Y-m-d H:i:s')));
            $observacion->setUsuarioRegistro($user);
      
            
            $em=$this->getDoctrine()->getManager();
            $em->persist($observacion);
            $em->flush();

        }

        return $this->redirectToRoute('contrato_linea_tiempo',['id'=>$contrato->getId()]);
    }
    /**
     * @Route("/{id}/modificar_servicio", name="contrato_modificar_servicio", methods={"GET","POST"})
     */
    function modificarServicio(Causa $causa,Request $request,
                                MateriaEstrategiaRepository $materiaEstrategiaRepository,
                                JuzgadoRepository $juzgadoRepository,
                                CorteRepository $corteRepository
                                ): Response
    {

        
        if($request->request->get('juzgado')!=null){            
            $causa->setJuzgado($juzgadoRepository->find($request->request->get('juzgado')));
           
        }
        if($request->request->get('corte')!=null){            
            $causa->setCorte($corteRepository->find($request->request->get('corte')));           
        }
        if($request->request->get('txtNombreCausa')!=null){
            $causa->setIdCausa($request->request->get('txtNombreCausa'));
        }
        
        if($request->request->get('txtCaratulado')!=null){
            $causa->setCausaNombre($request->request->get('txtCaratulado'));
        }

        if($request->request->get('txtLetra')!=null){
            $causa->setLetra($request->request->get('txtLetra'));
        }
        if($request->request->get('txtRol')!=null){
            $causa->setRol($request->request->get('txtRol'));
        }
        if($request->request->get('txtAnio')!=null){
            $causa->setAnio($request->request->get('txtAnio'));
        }

        $entity=$this->getDoctrine()->getManager();
        $entity->persist($causa);
        $entity->flush();
        
        return $this->redirectToRoute('contrato_linea_tiempo',['id'=>$causa->getAgenda()->getContrato()->getId()]);
    }

     /**
     * @Route("/{id}/anexos", name="contrato_anexos", methods={"GET","POST"})
     */
    public function anexos(Contrato $contrato, Request $request): Response
    {

        return $this->render('contrato/_anexos.html.twig',[
            'contrato'=>$contrato
        ]);
    }

    /**
     * @Route("/{id}/cartera", name="contrato_cartera", methods={"GET","POST"})
     */
    public function cartera(Contrato $contrato, 
                            Request $request,
                            CuentaMateriaRepository $cuentaMateriaRepository,
                            CarteraRepository $carteraRepository,
                            UsuarioCarteraRepository $usuarioCarteraRepository): Response
    {
        $cuentaMateria=$cuentaMateriaRepository->findOneBy(['cuenta'=>$contrato->getAgenda()->getCuenta(),'estado'=>true]);
        //$carteras=$carteraRepository->findBy(['materia'=>$cuentaMateria->getMateria(),'estado'=>true,'asignado'=>1]);
        $carteras=$carteraRepository->findBy(['materia'=>$cuentaMateria->getMateria()]);
        if($request->request->get('cboCarteras')){
            $cartera=$carteraRepository->find($request->request->get('cboCarteras'));

            $contrato->setCartera($cartera);
            $contrato->setCarteraOrden($cartera->getNombre());


            $usuarioCarteras=$usuarioCarteraRepository->findBy(['cartera'=>$cartera]);
            foreach ($usuarioCarteras as $usuarioCartera) {
                $contrato->setTramitador($usuarioCartera->getUsuario());
            }


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contrato);
            $entityManager->flush();

           
            


            return $this->redirectToRoute('contrato_index');
        }
        return $this->render('contrato/cartera.html.twig',[
            'contrato'=>$contrato,
            'carteras'=>$carteras
        ]);
    }

    /**
     * @Route("/{id}/tramitador", name="contrato_tramitador", methods={"GET","POST"})
     */
    public function tramitadores(Cartera $cartera, 
                            UsuarioCarteraRepository $usuarioCarteraRepository): Response
    {
        $tramitador=[];
        $usuarioCarteras=$usuarioCarteraRepository->findBy(['cartera'=>$cartera]);
        $i=0;
        foreach ($usuarioCarteras as $usuarioCartera) {
            $i++;
            $tramitador=array("nombre"=>$usuarioCartera->getUsuario()->getNombre());
        }
        if($i==0){
            $tramitador=array("nombre"=>'');
        }
        return $this->json($tramitador);
    }

    /**
     * @Route("/{id}/mod_clave_unica", name="contrato_mod_clave_unica", methods={"GET","POST"})
     */
    public function modificarClaveUnica(Contrato $contrato, 
                            Request $request): Response
    {
        if($request->query->get("txtClaveUnica"))
        {
            $entityManager = $this->getDoctrine()->getManager();

            $clave = $request->query->get("txtClaveUnica");
            $contrato->setClaveUnica($clave);

            $entityManager->persist($contrato);
            $entityManager->flush();
        }
        return $this->json("{ok:ok}");
    }
    /**
     * @Route("/{id}", name="contrato_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Contrato $contrato,
                            AgendaStatusRepository $agendaStatusRepository, 
                            AgendaObservacionRepository $agendaObservacionRepository): Response
    {
        $this->denyAccessUnlessGranted('full','contrato');
        if ($this->isCsrfTokenValid('delete'.$contrato->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $agenda=$contrato->getAgenda();
            $agenda->setStatus($agendaStatusRepository->find('5'));

            $entityManager->persist($agenda);
            $entityManager->flush();

            $contrato->setAgenda(null);
            $entityManager->persist($contrato);
            $entityManager->flush();
            $contratoRoles=$contrato->getContratoRols();
            foreach($contratoRoles as $contratoRol){
                $contrato->removeContratoRol($contratoRol);
            }
            $statusContrato=$agendaStatusRepository->find('7');
            $statusAgendaContratadas=$agendaObservacionRepository->findBy(['agenda'=>$agenda,'status'=>$statusContrato]);
            foreach ($statusAgendaContratadas as $agendaContratada) {
                $entityManager->remove($agendaContratada);
                $entityManager->flush();
            }

            $entityManager->remove($contrato);
            $entityManager->flush();

            
        }

        return $this->redirectToRoute('contrato_index');
    }
    

    
    public function pdf2(Contrato $contrato)
    {
        $this->denyAccessUnlessGranted('view','contrato');
        $filename = sprintf('Contrato-'.$contrato->getId().'-%s.pdf',rand(0,9000));
       
        $html = $this->renderView('contrato/print.html.twig', array(
            'contrato' => $contrato,
            'Titulo'=>"Contrato"
        ));

        $entityManager = $this->getDoctrine()->getManager();
        $contrato->setPdf($filename);
        $entityManager->persist($contrato);
        $entityManager->flush();

        /*$snappy->generateFromHtml(
           $html,
           $this->getParameter('url_root'). $this->getParameter('pdf_contratos').$filename
        );
        return new PdfResponse(
            $snappy->getOutputFromHtml($html, array(
                'page-size' => 'letter')),
            $filename
        );*/


        // Configure Dompdf según sus necesidades
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'helvetica');
    
        //$pdfOptions->set('fontHeightRatio',0.1);
        
        // Crea una instancia de Dompdf con nuestras opciones
        $dompdf = new Dompdf($pdfOptions);

        $dompdf->getOptions()->setChroot(array($this->getParameter('url_raiz')));
        
        // Recupere el HTML generado en nuestro archivo twig
       /* $html = $this->renderView('default/mypdf.html.twig', [
            'title' => "Welcome to our PDF Test"
        ]);*/
        
        // Cargar HTML en Dompdf
        $dompdf->loadHtml($html);
        
        // (Opcional) Configure el tamaño del papel y la orientación 'vertical' o 'vertical'
        $dompdf->setPaper('letter', 'portrait');

        // Renderiza el HTML como PDF
        $dompdf->render();

        $file=$dompdf->output();
        file_put_contents($this->getParameter('url_root'). $this->getParameter('pdf_contratos').$filename,$file);
        // Envíe el PDF generado al navegador (descarga forzada)
        /*$dompdf->stream($filename, [
            "Attachment" => true
        //]);*/
    }

    /**
     * @Route("/audio_upload", name="contrato_audio_upload", methods={"GET","POST"})
     */
    public function upload(Request $request, ContratoRepository $contratoRepository){
        $user=$this->getUser();
        
        print_r($_FILES);
        $brochureFile = $_FILES['file']['name'];
        $contratoId=$_POST['hdContrato'];
        echo "Comienzo a cargar";
        // this condition is needed because the 'brochure' field is not required
        // so the PDF file must be processed only when a file is uploaded
        if ($brochureFile) {
            
            $contrato=$contratoRepository->find($contratoId);

            //$nombre=$contrato->getFolio().rand(0,1000).".mp3";

            $arrayNombre=explode(".",$brochureFile);
            $nombre=$contrato->getFolio()."-".$arrayNombre[0].".mp3";
            $nombre_original=$contrato->getFolio()."-".$brochureFile;


            $contratoAudio=new ContratoAudios();
            $contratoAudio->setUsuarioRegistro($user);
            $contratoAudio->setContrato($contrato);
            $contratoAudio->setUrl($nombre);
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($contratoAudio);
            $entityManager->flush();
           
            $fichero_subido = $this->getParameter('url_root').
            $this->getParameter('audio_contratos') . $nombre_original;
            
           /* if (move_uploaded_file($_FILES['file']['tmp_name'][0], $fichero_subido)) {
                echo "El fichero es válido y se subió con éxito.\n";
            } else {
                echo "¡Posible ataque de subida de ficheros!\n";
            }*/

           
            $source=$_FILES['file']['tmp_name'];
            try{
                if(move_uploaded_file($source, $fichero_subido))
                {
                   // $message ='Audio cagado con exito';

                    $message="Toast.fire({
                        icon: 'success',
                        title: 'Audio Cargado con exito!!'
                      })";
                }
                else
                {
                    //$message = 'Ha Ocurrido un problema, el audio no ha sido cargado';
                    $message="Toast.fire({
                        icon: 'danger',
                        title: 'Ocurrio un error, el Audio no ha sido cargado!!'
                      })";
                }
                if($arrayNombre[1] != "mp3"){

               
                    $process=new Process([
                                "ffmpeg","-i",$this->getParameter('url_root').
                    $this->getParameter('audio_contratos').$nombre_original,$this->getParameter('url_root').
                    $this->getParameter('audio_contratos').$nombre
                    
                                ]);

                    $process->run();


                    
                    // executes after the command finishes
                    if (!$process->isSuccessful()) {
                        //throw new ProcessFailedException($process);

                        echo "error convertir ";
                    }
    
                    echo $process->getOutput();

                    $process=new Process([
                        "rm","-rf",$this->getParameter('url_root').
                    $this->getParameter('audio_contratos').$nombre_original
                        ]);

                    $process->run();

                    echo $process->getOutput();
                }
                
                echo $message;
                
            }catch(Exception $e){
                echo $e->getMessage();
            }
                        /*
            $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',$originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

            // Move the file to the directory where brochures are stored
            echo $this->getParameter('url_root').
            $this->getParameter('pagos');
            $brochureFile->move($this->getParameter('url_root').
                $this->getParameter('pagos'),
                $newFilename
            );
            */
        }

        
        return $this->redirectToRoute('contrato_index',['error_toast'=>$message]);
    }
    /**
     * @Route("/{id}/audio_delete", name="contrato_audio_delete", methods={"GET","POST"})
     */
    public function audiodelete(Contrato $contrato, Request $request, ContratoAudiosRepository $contratoAudiosRepository){
        $contratoAudios=$contratoAudiosRepository->findBy(['contrato'=>$contrato]);
        $entityManager=$this->getDoctrine()->getManager();
        $message="";
        foreach ($contratoAudios as $contratoAudio) {
            

            $message="Toast.fire({
                icon: 'success',
                title: 'Audio eliminado'
              })";

              $process=new Process([
                "rm","-rf",$this->getParameter('url_root').
            $this->getParameter('audio_contratos').$contratoAudio->getUrl()
                ]);

            $process->run();

            echo $process->getOutput();

            $entityManager->remove($contratoAudio);
            $entityManager->flush();
        }

        return $this->redirectToRoute('contrato_index',['error_toast'=>$message]);
    }



}
