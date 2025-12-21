<?php

namespace App\Controller;

use App\Entity\Importacion;
use App\Entity\InfComisionCobradores;
use App\Entity\Pago;
use App\Entity\PagoCuotas;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ContratoRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CuentaRepository;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\ModuloPerRepository;
use App\Repository\AgendaRepository;
use App\Repository\CobranzaRepository;
use App\Repository\ConfiguracionRepository;
use App\Repository\ContratoAnexoRepository;
use App\Repository\CuotaRepository;
use App\Repository\ImportacionRepository;
use App\Repository\InfComisionCobradoresRepository;
use App\Repository\PagoCuotasRepository;
use App\Repository\PagoRepository;
use App\Repository\UsuarioRepository;
use App\Repository\UsuarioTipoRepository;
use DateTime;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @Route("/comision")
 */

class ComisionController extends AbstractController
{
    /**
     * @Route("/", name="comision_index", methods={"GET","POST"})
     */
    public function index(): Response
    {
        return $this->render('comision/index.html.twig', [
            'controller_name' => 'ComisionController',
        ]);
        
    }
    /**
     * @Route("/agendador", name="comision_agendador", methods={"GET","POST"})
     */
    public function agendador(AgendaRepository $agendaRepository,
                            ContratoRepository $contratoRepository,
                            PaginatorInterface $paginator,
                            ModuloPerRepository $moduloPerRepository,
                            Request $request,
                            CuentaRepository $cuentaRepository,
                            UsuarioTipoRepository $usuarioTipoRepository,
                            UsuarioRepository $usuarioRepository): Response
    {
        $this->denyAccessUnlessGranted('view','comision_agendador');
        $user=$this->getUser();

        $pagina=$moduloPerRepository->findOneByName('comision_agendador',$user->getEmpresaActual());
        $filtro=null;
        $compania=null;
        $statuesgroup='7,14,13';
        $status=null;
        $tipo_fecha=1;
        $agendador=null;
        


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
            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');
        }
        $fecha="a.fechaContrato between '$dateInicio' and '$dateFin 23:59:59'" ;

        if(null !== $request->query->get('bStatus') && trim($request->query->get('bStatus'))!='' && trim($request->query->get('bStatus'))!=0){
            $status=$request->query->get('bStatus');
            $statues=$status;
            $statuesgroup=$status;
        }

        if(null !== $request->query->get('bAgendador')){
            if($request->query->get('bAgendador')==0){
                $agendador=null;
            }else{
                $agendador=$request->query->get('bAgendador');
            }

        }

        switch($user->getUsuarioTipo()->getId()){
            case 1:
                $agendadores=$usuarioRepository->findBy(['usuarioTipo'=>$usuarioTipoRepository->find(5),'estado'=>1]);

                $query=$agendaRepository->findByPers($agendador,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,null,$fecha);
                $companias=$cuentaRepository->findByPers($agendador,$user->getEmpresaActual());
                $queryresumen=$agendaRepository->findByPersGroup($agendador,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,0,$fecha,null,1);
                break;
            default:
                $agendadores=$usuarioRepository->findBy(['id'=>$user->getId()]);

                $query=$agendaRepository->findByPers($agendador,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,null,$fecha);
                $companias=$cuentaRepository->findByPers($agendador,$user->getEmpresaActual());
                $queryresumen=$agendaRepository->findByPersGroup($agendador,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,0,$fecha,null,1);

            break;
        }

        //$contratos=$contratoRepository->findAll();
        $agendas=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/,
            array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));
        return $this->render('comision/comision_agendador.html.twig', [
            //'controller_name' => 'ComisionController',
            //'contratos' => $query,
            'agendas' => $agendas,
            'bFiltro'=>$filtro,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'pagina'=>$pagina->getNombre(),
            'tipoFecha'=>$tipo_fecha,
            'resumenes'=>$queryresumen,
            'agendadores'=>$agendadores,
            'status'=>$status,
            'bAgendador'=>$agendador
        ]);
    }
     
    /**
     * @Route("/abogado", name="comision_abogado", methods={"GET","POST"})
     */
    public function abogado(AgendaRepository $agendaRepository,
                            ContratoRepository $contratoRepository,
                            PaginatorInterface $paginator,
                             $moduloPerRepository,
                             Request $request,
                             CuentaRepository $cuentaRepository): Response
    {
        $this->denyAccessUnlessGranted('view','comision_abogado');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('comision_abogado',$user->getEmpresaActual());
        $filtro=null;
        $compania=null;
        $statuesgroup='7,14';
        $status=null;

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
            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');
        }
        $fecha="a.fechaContrato between '$dateInicio' and '$dateFin 23:59:59'" ;

        switch($user->getUsuarioTipo()->getId()){
            case 1:
                $query=$agendaRepository->findByPers(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,null,$fecha);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                $queryresumen=$agendaRepository->findByPersGroup(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,0,$fecha);
            break;
        }

        //$contratos=$contratoRepository->findAll();
        $agendas=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/,
            array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));
        return $this->render('comision/comision_agendador.html.twig', [
            //'controller_name' => 'ComisionController',
            //'contratos' => $query,
            'agendas' => $agendas,
            'bFiltro'=>$filtro,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'pagina'=>$pagina->getNombre(),
            'resumenes'=>$queryresumen,
            'status'=>$status,
        ]);
    }
    /**
     * @Route("/cobrador", name="comision_cobrador", methods={"GET","POST"})
     */
    public function cobrador(PaginatorInterface $paginator,
                            InfComisionCobradoresRepository $infComisionCobradoresRepository,
                            ModuloPerRepository $moduloPerRepository,
                            Request $request,
                            CuentaRepository $cuentaRepository,
                            ConfiguracionRepository $configuracionRepository,
                            ImportacionRepository $importacionRepository): Response
    {
        $this->denyAccessUnlessGranted('view','comision_generar_cobrador');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('comision_generar_cobrador',$user->getEmpresaActual());
       

        $folio=null;
        $compania=null;
        $statuesgroup=2;
        $status=null;

        if(null !== $request->query->get('bFolio') && $request->query->get('bFolio')!=''){
            $folio=$request->query->get('bFolio');
            $fecha="co.folio = ".$folio." or " ;
            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');
        }else{
            if(null !== $request->query->get('bCompania') && $request->query->get('bCompania')!=0){
                $compania=$request->query->get('bCompania');
            }
            if(null !== $request->query->get('bFecha')){
                $aux_fecha=explode(" - ",$request->query->get('bFecha'));
                $dateInicio=$aux_fecha[0];
                $dateFin=$aux_fecha[1];
            }else{
                $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
                $dateFin=date('Y-m-d');
            }
            $fecha="c.fecha between '$dateInicio' and '$dateFin 23:59:59'" ;

            if(null !== $request->query->get('bStatus') && trim($request->query->get('bStatus')!='')){
                $status=$request->query->get('bStatus');
                $statues=$status;
                $statuesgroup=$status;
            }
        }

        switch($user->getUsuarioTipo()->getId()){
            case 1:// Admin
               
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                $queryresumen=$infComisionCobradoresRepository->totales(null,$user->getEmpresaActual(),$compania,null,null,$user->getId());
                $usuario=null;
                break;

            case 12:// Cobradores
                
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                $queryresumen=$infComisionCobradoresRepository->totales($user->getId(),$user->getEmpresaActual(),$compania,null,null,$user->getId());
                $usuario=$user->getId();
                break;
            default:
               
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                $queryresumen=$infComisionCobradoresRepository->totales(null,$user->getEmpresaActual(),$compania,null,null,$user->getId());
                $usuario=$user->getId();
                break;

        }
        //$queryresumen=$infComisionCobradoresRepository->cantidadTotal(null,$user->getEmpresaActual(),$compania,null,null,0);
                
       
        //$contratos=$contratoRepository->findAll();
        $comisiones=$infComisionCobradoresRepository->findBy(['sesion'=>$user->getId()]);

        $importaciones=$importacionRepository->ultimoPorcentaje($user->getId(), 2);
        $porcentaje=0;
        foreach ($importaciones as $query) {
            $porcentaje=$query->getEstado();
        }
        $agendas=$paginator->paginate(
            $comisiones, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            100 /*limit per page*/,
            array('defaultSortFieldName' => 'contrato.folio', 'defaultSortDirection' => 'desc'));
        return $this->render('comision/comision_cobrador.html.twig', [
            //'controller_name' => 'ComisionController',
            //'contratos' => $query,
            'agendas' => $agendas,
            'bFolio'=>$folio,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'pagina'=>$pagina->getNombre(),
            'resumenes'=>$queryresumen,
            'status'=>$statuesgroup,
            'configuracion'=>$configuracionRepository->find(1),
            'usuario'=>$user,
            'porcentaje'=>$porcentaje
        ]);
    }

    /**
     * @Route("/generar_cobrador", name="comision_generar_cobrador", methods={"GET","POST"})
     */
    public function generarCobrador(CobranzaRepository $cobranzaRepository,
                            InfComisionCobradoresRepository $infComisionCobradoresRepository,
                            Request $request,
                            PagoCuotasRepository $pagoCuotasRepository,
                            CuotaRepository $cuotaRepository,
                            ConfiguracionRepository $configuracionRepository,
                            KernelInterface $kernel): Response
    {
        $this->denyAccessUnlessGranted('view','comision_generar_cobrador');
        $user=$this->getUser();
        $folio=null;
        $compania=null;
        $statuesgroup='7,14';
        $status=null;

        if(null !== $request->query->get('bFolio') && $request->query->get('bFolio')!=''){
            $folio=$request->query->get('bFolio');
            $fecha="co.folio = ".$folio;
            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*1);
            $dateFin=date('Y-m-d');
        }else{
            if(null !== $request->query->get('bCompania') && $request->query->get('bCompania')!=0){
                $compania=$request->query->get('bCompania');
            }
            if(null !== $request->query->get('bFecha')){
                $aux_fecha=explode(" - ",$request->query->get('bFecha'));
                $dateInicio=$aux_fecha[0];
                $dateFin=$aux_fecha[1];
            }else{
                $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*1);
                $dateFin=date('Y-m-d');
            }
            $fecha="pa.fechaPago between '$dateInicio' and '$dateFin 23:59:59'" ;

            if(null !== $request->query->get('bStatus') && trim($request->query->get('bStatus')!='')){
                $status=$request->query->get('bStatus');
                $statues=$status;
                $statuesgroup=$status;
            }
        }
        switch($user->getUsuarioTipo()->getId()){
            case 1:// Admin
            case 3:// Jefe de procesos
                $usuario=null;
                break;

            case 12:// Cobradores
                
               $usuario=$user->getId();
                break;
            default:
                $usuario=$user->getId();
                break;

        }
        $entityManager = $this->getDoctrine()->getManager();

        $importacion = new Importacion();
        $importacion->setUsuarioCarga($user);
        $importacion->setTipoImportacion(2);
        $importacion->setNombre('comisionCObradoores');
        $importacion->setFechaCarga(new DateTime(date('Y-m-d H:s:i')));
        $importacion->setUrl('none');
        $porcentaje=0;
                        
        $importacion->setEstado($porcentaje);
        $entityManager->persist($importacion);
        $entityManager->flush();

        
        error_log("\n php bin/console app:comision-cobradores ".$user->getId().
        " --usuario=$usuario --fechaInicio=$dateInicio --fechaFin=$dateFin --folio=$folio --compania=$compania  > log.log",3,$this->getParameter('url_raiz')."/comision.log");

        
        shell_exec("cd ". $this->getParameter('url_raiz')."; php bin/console app:comision-cobradores ".$user->getId().
        " --usuario=$usuario --fechaInicio=$dateInicio --fechaFin=$dateFin --folio=$folio --compania=$compania  > /dev/null 2>&1 &");
                

            /*$application = new Application($kernel);
            $application->setAutoExit(false);
    
            $input = new ArrayInput(array(
                'command' => 'app:comision-cobradores',
                'sesion'=>  $user->getId(),
                '--usuario' => $usuario,
                '--fechaInicio' => $dateInicio,
                '--fechaFin' => $dateFin,
                '--folio' => $folio,
                '--compania' => $compania
            ));
    
            $output = new BufferedOutput();
    
            $application->run($input, $output);
    
            // devolver la salida
            $content = $output->fetch();
*/

      
        return  $this->redirectToRoute('comision_cobrador',['bFolio'=>$folio, 'bFecha'=>"$dateInicio - $dateFin",'bCompania'=>$compania]);
    }

    /**
     * @Route("/generar_cobrador_xls", name="comision_generar_cobrador_xls", methods={"GET","POST"})
     */
    public function generarCobradorXls(CobranzaRepository $cobranzaRepository,
                            InfComisionCobradoresRepository $infComisionCobradoresRepository,
                            Request $request,
                            PagoCuotasRepository $pagoCuotasRepository,
                            CuotaRepository $cuotaRepository,
                            ConfiguracionRepository $configuracionRepository,
                            KernelInterface $kernel): Response
    {
        $this->denyAccessUnlessGranted('view','comision_generar_cobrador');
        $user=$this->getUser();
        
        $comisiones=$infComisionCobradoresRepository->findBy(['sesion'=>$user->getId()]);

        $spreadSheet=new Spreadsheet();

        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadSheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Folio');
        $sheet->setCellValue('B1', 'AgendaId');
        $sheet->setCellValue('C1', 'Cobrador');
        $sheet->setCellValue('D1', 'Última Gestión');
        $sheet->setCellValue('E1', 'Pago');
        $sheet->setCellValue('F1', 'Cuota');
        $sheet->setCellValue('G1', 'Tiempo Mora');
        $sheet->setCellValue('H1', '$ Pagó');
        
        
        $sheet = $spreadSheet->getActiveSheet();
        $i=2;
        foreach($comisiones as $comision){

            $sheet->setCellValue("A$i",$comision->getContrato()->getFolio());
             $sheet->setCellValue("B$i",$comision->getContrato()->getAgenda()->getId());
            $sheet->setCellValue("C$i",$comision->getCobranza()->getUsuarioRegistro()->getNombre());
            $sheet->setCellValue("D$i",$comision->getCobranza()->getFechaHora()->format("Y-m-d H:i"));
            $sheet->setCellValue("E$i",$comision->getPago()->getFechaPago()->format("Y-m-d H:i"));
            $sheet->setCellValue("F$i",$comision->getCuota()->getFechaPago()->format("Y-m-d"));
            $sheet->setCellValue("G$i",$comision->getDiasMora());
            $sheet->setCellValue("H$i",$comision->getPago()->getMonto());


            $i++;
        }



        $sheet->setTitle("Comision Cobradores");
 
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadSheet);
    
        // Create a Temporary file in the system
        $fileName = 'ComCobrador_'.date("dmY").'_'.date("Hi").'.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
    
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
    
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    


     /**
     * @Route("/cierre", name="comision_cierre", methods={"GET","POST"})
     */
    public function cierre( CuotaRepository $cuotaRepository,
                            ContratoRepository $contratoRepository,
                            PaginatorInterface $paginator,
                            InfComisionCobradoresRepository $infComisionCobradoresRepository,
                            ModuloPerRepository $moduloPerRepository,
                            Request $request,
                            CuentaRepository $cuentaRepository,
                            ConfiguracionRepository $configuracionRepository): Response
    {

        $this->denyAccessUnlessGranted('view','comision_cerradores');
        
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('comision_cerradores',$user->getEmpresaActual());
        $filtro=null;
        $folio=null;
        $compania=null;
        $otros='';
        $fecha=null;
        $array=null;
        $status=0;
        if(null !== $request->query->get('bFolio') && $request->query->get('bFolio')!=''){
            $folio=$request->query->get('bFolio');
            $otros=" c.folio= $folio";

            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');
            $fecha=$otros;

        }else{
            if(null != $request->query->get('bStatus') && $request->query->get('bStatus')!='' ){
                $status=$request->query->get('bStatus');
            }
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
                $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
                $dateFin=date('Y-m-d');

            }
            $fecha="p.fechaPago between '$dateInicio' and '$dateFin 23:59:59' ";
        }

        if($request->query->get('sort')){
            $array=array('sort'=>$request->query->get('sort'),
                        'direction'=>$request->query->get('direction'));
        }

        $fecha.=" and date(p.fechaPago)<=date(DATE_ADD(cuo.fechaPago, 30, 'DAY')) and cuo.numero=1 and cuo.monto<=cuo.pagado ";
        
        switch($user->getUsuarioTipo()->getId()){
            case 1://tramitador
            case 3:
            case 4:
            case 8:
            case 12:
                //$query=$cuotaRepository->findPagado(null,null,null,$filtro,null,true,$fecha);
                $query=$contratoRepository->findByCerradores(null,null,null,$fecha,$array);
                $resumenquery=$contratoRepository->findByCerradoresResumen(null,null,null,$fecha,$array);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;

            case 6: //abogado
                $query=$contratoRepository->findByCerradores($user->getId(),null,null,$fecha,$array);
                $resumenquery=$contratoRepository->findByCerradoresResumen($user->getId(),null,null,$fecha,$array);
                
                //$query=$cuotaRepository->findPagado($user->getId(),null,null,$filtro,6,true,$fecha);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;
            case 11://Administrativo
                //$query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha,true);
               // $query=$cuotaRepository->findPagado(null,null,null,$filtro,null,true,$fecha);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
            break;
            
            default:
                //$query=$contratoRepository->findByPers(null,null,$compania,$filtro,null,$fecha,true);
                //$query=$cuotaRepository->findVencimiento(null,null,null,$filtro,null,true,$fecha);
                $companias=$cuentaRepository->findByPers(null);
                
            break;
        }
        //$companias=$cuentaRepository->findByPers($user->getId());
        //$query=$contratoRepository->findAll();
        $contratos=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            100 /*limit per page*/,
            array('defaultSortFieldName' => 'cuota_numero', 'defaultSortDirection' => 'desc'));
        
        return $this->render('comision/comision_cierre.html.twig', [
            'contratos' => $contratos,
            'resumen'=> $resumenquery,
            'bFiltro'=>$filtro,
            'bFolio'=>$folio,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'pagina'=>$pagina->getNombre(),
            'finalizado'=>false,
            'status'=>$status
        ]);
        

    }

    /**
     * @Route("/cierrexls", name="comision_cierre_xls", methods={"GET","POST"})
     */
    public function cierrexls( CuotaRepository $cuotaRepository,
                            ContratoRepository $contratoRepository,
                            PaginatorInterface $paginator,
                            InfComisionCobradoresRepository $infComisionCobradoresRepository,
                            ModuloPerRepository $moduloPerRepository,
                            Request $request,
                            CuentaRepository $cuentaRepository,
                            ContratoAnexoRepository $contratoAnexoRepository,
                            ConfiguracionRepository $configuracionRepository): Response
    {

        $this->denyAccessUnlessGranted('view','comision_cerradores');
        
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('comision_cerradores',$user->getEmpresaActual());
        $filtro=null;
        $folio=null;
        $compania=null;
        $otros='';
        $fecha=null;
        $array=null;
        if(null !== $request->query->get('bFolio') && $request->query->get('bFolio')!=''){
            $folio=$request->query->get('bFolio');
            $otros=" c.folio= $folio";

            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');
            $fecha=$otros;

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
                $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
                $dateFin=date('Y-m-d');

            }
            $fecha="p.fechaPago between '$dateInicio' and '$dateFin 23:59:59' ";
        }

        if($request->query->get('sort')){
            $array=array('sort'=>$request->query->get('sort'),
                        'direction'=>$request->query->get('direction'));
        }

        //$fecha.=" and date(p.fechaPago)<=date(DATE_ADD(c.fechaPago, INTERVAL 30 DAY)) ";
       // $fecha.=" and DATEDIFF(p.fechaPago, cuo.fechaPago)>=30 and cuo.numero=1 and cuo.monto<=cuo.pagado";
       $fecha.=" and date(p.fechaPago)<=date(DATE_ADD(cuo.fechaPago, 30, 'DAY')) and cuo.numero=1 and cuo.monto<=cuo.pagado "; 
       switch($user->getUsuarioTipo()->getId()){
            case 1://tramitador
            case 3:
            case 4:
            case 8:
            case 12:
                //$query=$cuotaRepository->findPagado(null,null,null,$filtro,null,true,$fecha);
                $query=$contratoRepository->findByCerradores(null,null,null,$fecha,$array);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;
            case 7://tramitador
                $query=$cuotaRepository->findPagado($user->getId(),null,null,$filtro,7,true,$fecha);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;
            case 6: //abogado
                //$query=$cuotaRepository->findPagado($user->getId(),null,null,$filtro,6,true,$fecha);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;
            case 11://Administrativo
                //$query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha,true);
               // $query=$cuotaRepository->findPagado(null,null,null,$filtro,null,true,$fecha);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
            break;
            
            default:
                //$query=$contratoRepository->findByPers(null,null,$compania,$filtro,null,$fecha,true);
                //$query=$cuotaRepository->findVencimiento(null,null,null,$filtro,null,true,$fecha);
                $companias=$cuentaRepository->findByPers(null);
                
            break;
        }
        

        $spreadSheet=new Spreadsheet();

        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadSheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Cerrador');
        $sheet->setCellValue('B1', 'Cliente');
        $sheet->setCellValue('C1', 'TipoDcto');
        $sheet->setCellValue('D1', 'Folio');
        $sheet->setCellValue('E1', 'Fecha Creación');
        $sheet->setCellValue('F1', 'Monto Contrato');
        $sheet->setCellValue('G1', 'Número Cuota');
        $sheet->setCellValue('H1', 'Fecha Vencimiento');
        $sheet->setCellValue('I1', 'Monto Cuota');
        $sheet->setCellValue('J1', 'Monto Pago');
        $sheet->setCellValue('K1', 'Fecha Pago');
        $sheet->setCellValue('L1', 'Dia Vencimiento');
        $sheet->setCellValue('M1', 'q_dias');
        
        $sheet = $spreadSheet->getActiveSheet();
        $i=2;
        foreach($query as $contrato){

            $anexo=$contratoAnexoRepository->findOneBy(['isDesiste'=>0,'contrato'=>$contrato[0]->getId()],['folio'=>'desc']);
            $tipoDcto="C0";
            if($anexo){
                $tipoDcto="A".$anexo->getFolio();
            }
             
            $sheet->setCellValue("A$i",$contrato[0]->getAgenda()->getAbogado()->getNombre());
            $sheet->setCellValue("B$i",$contrato[0]->getNombre());
            $sheet->setCellValue("C$i",$tipoDcto);
            $sheet->setCellValue("D$i",$contrato[0]->getFolio());
            $sheet->setCellValue("E$i",$contrato[0]->getFechaCreacion());
            $sheet->setCellValue("F$i",$contrato[0]->getMontoContrato());
            $sheet->setCellValue("G$i",$contrato['cuota_numero']);
            $sheet->setCellValue("H$i",$contrato['fecha_vencimiento']);
            $sheet->setCellValue("I$i",$contrato['monto_cuota']);
            $sheet->setCellValue("J$i",$contrato['monto_pagado']);
            $sheet->setCellValue("K$i",$contrato['fecha_pago']);
            $sheet->setCellValue("L$i",$contrato['dia_vencimiento']);
            $sheet->setCellValue("M$i",$contrato['q_dias']);

            $i++;
        }



        $sheet->setTitle("Comision Cerradores");
 
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadSheet);
    
        // Create a Temporary file in the system
        $fileName = 'ComisionCerradores.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
    
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
    
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
        

    }

    /**
     * @Route("/{id}/gestiones_cobrador", name="comision_gestiones_cobrador", methods={"GET","POST"})
     */
    public function gestionesCobrador(PagoCuotas $pagoCuotas,
                                    CobranzaRepository $cobranzaRepository,
                                    InfComisionCobradoresRepository $infComisionCobradoresRepository,
                                    ConfiguracionRepository $configuracionRepository,
                                    Request $request): Response
    {
       
        
        $configuracion=$configuracionRepository->find(1);

        $pago=$pagoCuotas->getPago();
        $cuota=$pagoCuotas->getCuota();
        
        $fecha=" u.usuarioTipo=12 and (timestampdiff(second,c.fechaHora,'".$pago->getFechaPago()->format('Y-m-d H:i')."')/60/60/24)>=0 and datediff('".$pago->getFechaPago()->format('Y-m-d H:i')."',c.fechaHora)<=".$configuracion->getMaxDiasComision()." and co.id=".$cuota->getContrato()->getId();
        $cobranzas=$cobranzaRepository->findByPers(null,null,null,null,$fecha);


        return $this->render('comision/comision_gestiones_cobrador.html.twig', [
            'cobranzas'=>$cobranzas,
        ]);
        
    }

}

