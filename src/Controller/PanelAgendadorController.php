<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Agenda;
use App\Entity\AgendaObservacion;
use App\Entity\AgendaStatus;
use App\Entity\AgendaSubStatus;
use App\Entity\Usuario;
use App\Entity\Empresa;
use App\Entity\ModuloPer;
use App\Form\AgendaType;
use App\Repository\AgendaContactoRepository;
use App\Repository\AgendaRepository;
use App\Repository\ReunionRepository;
use App\Repository\UsuarioRepository;
use App\Repository\UsuarioNoDisponibleRepository;
use App\Repository\AgendaStatusRepository;
use App\Repository\AgendaSubStatusRepository;
use App\Repository\ContratoRepository;
use App\Repository\CuentaRepository;
use App\Repository\UsuarioTipoRepository;
use App\Service\ActualizaLead;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Nick\SecureSpreadsheet\Encrypt;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @Route("/panel_agendador")
 */
class PanelAgendadorController extends AbstractController
{
    /**
     * @Route("/", name="panel_agendador_index", methods={"GET","POST"})
     */
    public function index(AgendaRepository $agendaRepository,
                          CuentaRepository $cuentaRepository,
                          PaginatorInterface $paginator,
                          UsuarioRepository $usuarioRepository,
                          UsuarioTipoRepository $usuarioTipoRepository,
                          Request $request): Response
    {
        $this->denyAccessUnlessGranted('view','panel_agendador');

        $user=$this->getUser();

        $pagina=$this->getDoctrine()->getRepository(ModuloPer::class)->findOneByName('panel_agendador',$user->getEmpresaActual());
        $filtro=null;
        $compania=null;
        $fecha=6;
        //$statues='1,2,3,4,5,6,7,8,9,10,11,12,13,14,15';
        $statues='1';
        $statuesgroup="1,2,3,4,5,6,7,8,9,10,11,12,13,14,15";
        $status=null;
        $tipo_fecha=0;
        $agendador=null;
        $folio=null;
        $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
        //$dateInicio=date('Y-m-d');
        $dateFin=date('Y-m-d');
        $fecha=" 1 = 1";
        if(null !== $request->query->get('bFolio') && trim($request->query->get('bFolio'))!=''){
            $folio=$request->query->get('bFolio');
            $fecha=" a.id=$folio ";
             $statues=$statuesgroup;
            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');
            
        }else{
            if(null !== $request->query->get('bFiltro') && trim($request->query->get('bFiltro'))!=''){
                $filtro=$request->query->get('bFiltro');
                $statues=$statuesgroup;
            }else{
                if(null !== $request->query->get('bCompania')&&$request->query->get('bCompania')!=0){
                    $compania=$request->query->get('bCompania');
                }
        
                if(null !== $request->query->get('bFecha')){
                    $aux_fecha=explode(" - ",$request->query->get('bFecha'));
                    $dateInicio=$aux_fecha[0];
                    $dateFin=$aux_fecha[1];
                    $statues=$statuesgroup;
                }else{
                    $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
                    //$dateInicio=date('Y-m-d');
                    $dateFin=date('Y-m-d');
                }
                if(null !== $request->query->get('bTipofecha') ){
                    $tipo_fecha=$request->query->get('bTipofecha');
                }
        
                if(null !== $request->query->get('bAgendador')){
                    if($request->query->get('bAgendador')==0){
                        $agendador=null;
                    }else{
                        $agendador=$request->query->get('bAgendador');
                    }
        
                }
                switch($tipo_fecha){
                    case 0:
                        $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
                        break;
                    case 1:
                        $fecha="a.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
                        break;
                    case 2:
                        $fecha="a.fechaContrato between '$dateInicio' and '$dateFin 23:59:59'" ;
                        break;
                    default:
                        $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
                        break;
                }
                //$fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
            
                if(null !== $request->query->get('bStatus') && trim($request->query->get('bStatus')!='')){
                    $status=$request->query->get('bStatus');
                    $statues=$status;
                    $statuesgroup=$status;
                }
            }
        }

        $agendadores=$usuarioRepository->findBy(['usuarioTipo'=>$usuarioTipoRepository->find(5),'estado'=>1]);


        
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 1:
            case 8:
            case 13:
                $agendadores=$usuarioRepository->findBy(['usuarioTipo'=>$usuarioTipoRepository->find(5),'estado'=>1]);

                $query=$agendaRepository->findByPers($agendador,$user->getEmpresaActual(),$compania,$statues,$filtro,0,$fecha);
                $queryresumen=$agendaRepository->findByPersGroup($agendador,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,0,$fecha);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
            break;
            default:
            $agendadores=$usuarioRepository->findBy(['id'=>$user->getId()]);

                $query=$agendaRepository->findByPers($user->getId(),null,$compania,$statues,$filtro,0,$fecha);
                $queryresumen=$agendaRepository->findByPersGroup($user->getId(),null,$compania,$statuesgroup,$filtro,0,$fecha);
                $companias=$cuentaRepository->findByPers($user->getId());
            break;
        }

         
        
        $agendas=$paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /*page number*/
        20 /*limit per page*/,
        array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));

        return $this->render('panel_agendador/index.html.twig', [
            'agendas' => $agendas,
            'pagina'=>$pagina->getNombre(),
            'bFiltro'=>$filtro,
            'bFolio'=>$folio,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'resumenes'=>$queryresumen,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'status'=>$status,
            'statuesGroup'=>$statuesgroup,
            'tipoFecha'=>$tipo_fecha,
            'agendadores'=>$agendadores,
            'bAgendador'=>$agendador,
            'TipoFiltro'=>'Panel_agendador'
        ]);
    }
    /**
     * @Route("/exportar_xls", name="panel_agendador_xls", methods={"GET","POST"})
     */
    public function exportarXls(AgendaRepository $agendaRepository,
                          Request $request): Response
    {
        
        $this->denyAccessUnlessGranted('view','panel_agendador_xls');
        ini_set('memory_limit', '2048M');
        $user=$this->getUser();

       $filtro=null;
        $compania=null;
        $fecha=6;
        //$statues='1,2,3,4,5,6,7,8,9,10,11,12,13,14,15';
        $statues='1';
        $statuesgroup="1,2,3,4,5,6,7,8,9,10,11,12,13,14,15";
        $status=null;
        $tipo_fecha=0;
        $agendador=null;
        $folio=null;
        $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
        //$dateInicio=date('Y-m-d');
        $dateFin=date('Y-m-d');
        $fecha=" 1 = 1";
        if(null !== $request->query->get('bFolio') && trim($request->query->get('bFolio'))!=''){
            $folio=$request->query->get('bFolio');
            $fecha=" a.id=$folio ";
             $statues=$statuesgroup;
            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');
            
        }else{
            if(null !== $request->query->get('bFiltro') && trim($request->query->get('bFiltro'))!=''){
                $filtro=$request->query->get('bFiltro');
                $statues=$statuesgroup;
            }else{
                if(null !== $request->query->get('bCompania')&&$request->query->get('bCompania')!=0){
                    $compania=$request->query->get('bCompania');
                }
        
                if(null !== $request->query->get('dateInicio') && null !== $request->query->get('dateFin') ){

                    $dateInicio=$request->query->get('dateInicio');
                    $dateFin=$request->query->get('dateFin');
                    $statues=$statuesgroup;
                }else{
                    $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
                    //$dateInicio=date('Y-m-d');
                    $dateFin=date('Y-m-d');
                }
                if(null !== $request->query->get('tipoFecha') ){
                    $tipo_fecha=$request->query->get('tipoFecha');
                }
        
                if(null !== $request->query->get('bAgendador')){
                    if($request->query->get('bAgendador')==0){
                        $agendador=null;
                    }else{
                        $agendador=$request->query->get('bAgendador');
                    }
        
                }
                switch($tipo_fecha){
                    case 0:
                        $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
                        break;
                    case 1:
                        $fecha="a.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
                        break;
                    case 2:
                        $fecha="a.fechaContrato between '$dateInicio' and '$dateFin 23:59:59'" ;
                        break;
                    default:
                        $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
                        break;
                }
                //$fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
            
                if(null !== $request->query->get('bStatus') && trim($request->query->get('bStatus')!='')){
                    $status=$request->query->get('bStatus');
                    $statues=$status;
                    $statuesgroup=$status;
                }
            }
        }

      
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 1:
            case 8:
            case 13:
            

                $query=$agendaRepository->findByPers($agendador,$user->getEmpresaActual(),$compania,$statues,$filtro,0,$fecha);
                
            break;
            default:


                $query=$agendaRepository->findByPers($user->getId(),null,$compania,$statues,$filtro,0,$fecha);
              
            break;
        }
        

        $spreadSheet=new Spreadsheet();
        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadSheet->getActiveSheet();
        $sheet->setCellValue('A1',"Fecha y hora de descarga: ".date("Y-m-d H:i:s"));


        $sheet->setCellValue('A2', 'AgendaId');
        $sheet->setCellValue('B2', 'Compañia');
        $sheet->setCellValue('C2', 'Canal');
        $sheet->setCellValue('D2', 'Status');
        $sheet->setCellValue('E2', 'Sub Status');
        $sheet->setCellValue('F2', 'Agendador');
        $sheet->setCellValue('G2', 'Reunión');
        $sheet->setCellValue('H2', 'Abogado');
        $sheet->setCellValue('I2', 'Nombre');
        $sheet->setCellValue('J2', 'Fecha Carga');
        $sheet->setCellValue('K2', 'Fecha Asignado');
        $sheet->setCellValue('L2', 'Fecha Contrato');    
       
        $sheet = $spreadSheet->getActiveSheet();
        $i=3;
        foreach($query as $agenda){
            
            $sheet->setCellValue('A'.$i, $agenda->getId());
            $sheet->setCellValue('B'.$i, $agenda->getCuenta()->getNombre());
            $sheet->setCellValue('C'.$i, $agenda->getCanal());
            $sheet->setCellValue('D'.$i, $agenda->getStatus()? $agenda->getStatus()->getNombre() : '');
            $sheet->setCellValue('E'.$i, $agenda->getSubStatus()? $agenda->getSubStatus()->getNombre() : '');
            $sheet->setCellValue('F'.$i, $agenda->getAgendador()->getNombre());
            $sheet->setCellValue('G'.$i, $agenda->getReunion()? $agenda->getReunion()->getNombre() : '');
            $sheet->setCellValue('H'.$i, $agenda->getAbogado()? $agenda->getAbogado()->getNombre() : '');
            $sheet->setCellValue('I'.$i, $agenda->getNombreCliente());
            $sheet->setCellValue('J'.$i, $agenda->getFechaCarga()? $agenda->getFechaCarga()->format('Y-m-d H:i:s') : '');
            $sheet->setCellValue('K'.$i, $agenda->getFechaAsignado()? $agenda->getFechaAsignado()->format('Y-m-d') : '');
            $sheet->setCellValue('L'.$i, $agenda->getFechaContrato() ? $agenda->getFechaContrato()->format('Y-m-d') : ''    );

            $i++;
        }

        $sheet->setTitle("Agendas");
        $security = $spreadSheet->getSecurity();
        $security->setLockWindows(true);
        $security->setLockStructure(true);
        $security->setWorkbookPassword("123456"); 
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadSheet);
        /*$writer->setDelimiter(',');
        $writer->setEnclosure('');*/
        $fileName="agendas_" . date("Y-m-d_H-i-s") . ".xlsx";
        $fileName_protegido="agendas_" . date("Y-m-d_H-i-s") . ".xlsx";
        // Create a Temporary file in the system
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $temp_file_protegido = tempnam(sys_get_temp_dir(), $fileName_protegido);
        $writer->save($temp_file);
       
        $encryptor = new Encrypt();
        $encryptor->input($temp_file)
        ->password('U1JuYO5a8yKd')
        ->output( $temp_file_protegido);
        $temp_file = $temp_file_protegido;   
        // Create the excel file in the tmp directory of the system
        

        //exec('libreoffice --headless --convert-to xlsx:"Calc MS Excel 2007 XML:EncryptFile=true;Password=123456" '.$temp_file);
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
       
    }
    /**
     * @Route("/reasignar", name="panel_agendador_reasignar", methods={"GET","POST"})
     */
    public function reasignar(Request $request,UsuarioRepository $usuarioRepository,AgendaRepository $agendaRepository):Response
    {
        $user=$this->getUser();
        $agenda_id=$request->query->get('agenda');
        $agenda=$agendaRepository->find($agenda_id);
        $empresa=$this->getDoctrine()->getRepository(Empresa::class)->find($user->getEmpresaActual());
        return $this->render('panel_agendador/reasignar.html.twig', [
            'cuentas'=>$empresa->getCuentas(),
            'agenda'=> $agenda,     
        ]);
    }

    /**
     * @Route("/sub_status", name="panel_agendador_sub_status", methods={"GET","POST"})
     */
    public function subStatus(Request $request, 
                                AgendaSubStatusRepository $agendaSubStatusRepository,
                                AgendaStatusRepository $agendaStatusRepository):Response
    {
        $status=$agendaStatusRepository->find($request->query->get('status'));
        $agendaSubStatues=$agendaSubStatusRepository->findBy(['agendaStatus'=>$status]);
       
        return $this->render('panel_agendador/sub_status.html.twig',[
                'sub_statues'=>$agendaSubStatues
            ]);
    }


    /**
     * @Route("/horas", name="panel_agendador_horas", methods={"GET","POST"})
     */
    public function hora(Request $request, 
                        UsuarioRepository $usuarioRepository,
                        AgendaRepository $agendaRepository, 
                        UsuarioNoDisponibleRepository $usuarioNoDisponibleRepository):Response
    {
        //$agendas=$agendaRepository->findBy(['cuenta'=>$agenda->getCuenta()->getId(),'status'=>[4,5]]);
        $usuario=$request->query->get('abogado');
        $fecha=$request->query->get('fecha');
        $dia = date("N",strtotime($fecha));
        $abogado=$usuarioRepository->find($usuario);
        $horario_inicio="";
        $horario_fin="";
        $horario=array();
        $nodisponibles=array();
        $status=false;
        $sobrecupo="";
        $mensaje="Horas";
        $agendas="";

        switch($dia)
        {
            case 1: //Lunes
                if($abogado->getLunes()){
                    $horario_inicio=$abogado->getLunesStart();
                    $horario_fin=$abogado->getLunesEnd();
                    $status=true;
                }
            break;
            case 2: //Martes
                if($abogado->getMartes()){
                    $horario_inicio=$abogado->getMartesStart();
                    $horario_fin=$abogado->getMartesEnd();
                    $status=true;
                }
            break;
            case 3: //Miercoles
                if($abogado->getMiercoles()){
                    $horario_inicio=$abogado->getMiercolesStart();
                    $horario_fin=$abogado->getMiercolesEnd();
                    $status=true;
                }
            break;
            case 4: //Jueves
                if($abogado->getJueves()){
                    $horario_inicio=$abogado->getJuevesStart();
                    $horario_fin=$abogado->getJuevesEnd();
                    $status=true;
                }
            break;
            case 5: //Viernes
                if($abogado->getViernes()){
                    $horario_inicio=$abogado->getViernesStart();
                    $horario_fin=$abogado->getViernesEnd();
                    $status=true;
                }
            break;
            case 6: //Sabado
                if($abogado->getSabado()){
                    $horario_inicio=$abogado->getSabadoStart();
                    $horario_fin=$abogado->getSabadoEnd();
                    $status=true;
                }
            break;
            case 7: //Domingo
                if($abogado->getDomingo()){
                    $horario_inicio=$abogado->getDomingoStart();
                    $horario_fin=$abogado->getDomingoEnd();
                    $status=true;
                }
            break;
        }
        if($abogado->getUsuarioTipo()->getId()==6){
            $agendas=$agendaRepository->findByPers($usuario,null,null,'4,5,7,6,8,9,10', null,1," a.fechaAsignado >= '$fecha' and  a.fechaAsignado <= '$fecha 23:59:59'");
            $nodisponibleI=$usuarioNoDisponibleRepository->findByIntervalo($usuario,$fecha);
            $nodisponibleD=$usuarioNoDisponibleRepository->findByDinamico($usuario,$fecha);
            if($status){
                $horario_inicio=explode(":",$horario_inicio->format("G:i"));
                $horario_fin=explode(":",$horario_fin->format("G:i"));
            
                if(strtotime($fecha)>=strtotime(date('Y-m-d'))){
                    for($i=intval($horario_inicio[0]);$i<=intval($horario_fin[0]);$i++){

                        if($i==intval($horario_inicio[0]) && $horario_inicio[1]=="30"){
                            if(strtotime(date('Y-m-d H:i:00'))<strtotime($fecha." $i:30:00"))
                                $horario[]="$i:30";
         
                            continue;
                        }
                        if($i==intval($horario_fin[0]) && intval($horario_fin[1])==00){
                            if(strtotime(date('Y-m-d H:i:00'))<strtotime($fecha." $i:00:00"))
                                $horario[]="$i:00";
                            continue;
                        }
                        if(strtotime(date('Y-m-d H:i:00'))<strtotime($fecha." $i:00:00"))
                            $horario[]="$i:00";
                        if(strtotime(date('Y-m-d H:i:00'))<strtotime($fecha." $i:30:00"))
                            $horario[]="$i:30";
                        
                    }
                }else{
                    $mensaje="Sin horas";
                }
                
            }else{
                $mensaje="Sin horas";
            }

            foreach($nodisponibleI as $nd){
                $nd_inicio=explode(":",$nd->getHoraInicio()->format("G:i"));
                $nd_fin=explode(":",$nd->getHoraFin()->format("G:i"));
            
                for($i=intval($nd_inicio[0]);$i<=intval($nd_fin[0]);$i++){
                    if($i==intval($nd_inicio[0]) && $nd_inicio[1]=="30"){
                        $nodisponibles[]="$i:30";
                        continue;
                    }
                    if($i==intval($nd_fin[0]) && intval($nd_fin[1])==00){
                        $nodisponibles[]="$i:00";
                        continue;
                    }
                    $nodisponibles[]="$i:00";
                    $nodisponibles[]="$i:30";
                    
                }
            }
            foreach($nodisponibleD as $nd){
                $nd_inicio=explode(":",$nd->getHoraInicio()->format("G:i"));
                $nd_fin=explode(":",$nd->getHoraFin()->format("G:i"));
            
                for($i=intval($nd_inicio[0]);$i<=intval($nd_fin[0]);$i++){
                    if($i==intval($nd_inicio[0]) && $nd_inicio[1]=="30"){
                        $nodisponibles[]="$i:30";
                        continue;
                    }
                    if($i==intval($nd_fin[0]) && intval($nd_fin[1])==00){
                        $nodisponibles[]="$i:00";
                        continue;
                    }
                    $nodisponibles[]="$i:00";
                    $nodisponibles[]="$i:30";
                    
                }
            }

            $nodisponibles=array_unique($nodisponibles);

            if(strtotime($fecha)>=strtotime(date('Y-m-d'))){
                if($abogado->getSobrecupo()>0){
                    $agenda_sobrecupos=$agendaRepository->findByPers($usuario,null,null,'4,5,7,8,9,10', null,1," a.fechaAsignado = '$fecha 00:00:00'");
                    $cont=0;
                    foreach($agenda_sobrecupos as $agenda_sobrecupo){
                        $cont++;
                    }
                    if($cont<$abogado->getSobrecupo()){
                        $sobrecupo="Sobre Cupo";
                    }
                }
            }
        }else{
            $horario_inicio=explode(":","00:00");
            $horario_fin=explode(":","23:30");
        
            for($i=intval($horario_inicio[0]);$i<=intval($horario_fin[0]);$i++){
                if($i==intval($horario_inicio[0]) && $horario_inicio[1]=="30"){
                    $horario[]="$i:30";
                    continue;
                }
                if($i==intval($horario_fin[0]) && intval($horario_fin[1])==00){
                    $horario[]="$i:00";
                    continue;
                }
                $horario[]="$i:00";
                $horario[]="$i:30";
                
            }
        }  
        return $this->render('panel_agendador/horas.html.twig',[
            'agendas'=>$agendas,
            'horarios'=>$horario,
            'nodisponibles'=>$nodisponibles,
            'mensaje'=>$mensaje,
            'sobrecupo'=>$sobrecupo,
        ]);
    }
    /**
     * @Route("/{id}", name="panel_agendador_new", methods={"GET","POST"})
     */
    public function new(Agenda $agenda,
                        AgendaRepository $agendaRepository,
                        AgendaStatusRepository $agendaStatusRepository,
                        CuentaRepository $cuentaRepository,
                        UsuarioRepository $usuarioRepository,
                        ReunionRepository $reunionRepository,
                        ActualizaLead $actualizaLead,
                        AgendaContactoRepository $agendaContactoRepository,
                        AgendaSubStatusRepository $agendaSubStatusRepository,
                        ContratoRepository $contratoRepository,
                        Request $request): Response
    {
        $this->denyAccessUnlessGranted('create','panel_agendador');

        //Buscar si tiene Campaña::::

        $actualizaLead->completar($agenda);

        $error='';
        $abortar=false;
        $subStatus=null;
        $sql='';
        $sql1='';
        $abogadoDestino=null;
        $user=$this->getUser();
        $pagina=$this->getDoctrine()->getRepository(ModuloPer::class)->findOneByName('panel_agendador',$user->getEmpresaActual());
        $form = $this->createForm(AgendaType::class, $agenda);
        
        $form->handleRequest($request);

        $telefono = $agenda->getTelefonoCliente();
        $telefonoRecado= $agenda->getTelefonoRecadoCliente();
        $correo=$agenda->getEmailCliente();
        $rut=$agenda->getRutCliente();
        
        if(trim($telefono)!=""){
            $sql=" (a.telefonoCliente='$telefono' or a.telefonoRecadoCliente='$telefono' ) ";
        }

        if(trim($telefonoRecado)!=""){
            if(trim($telefono)!=""){
              
                $sql1=" and (a.telefonoCliente='$telefonoRecado' or a.telefonoRecadoCliente='$telefonoRecado' ) ";
            }else{
                $sql1="  (a.telefonoCliente='$telefonoRecado' or a.telefonoRecadoCliente='$telefonoRecado' ) ";
            }
            
        }
        if(trim($correo)!=""){
            $sql1.=" or a.emailCliente='$correo' ";
        }
        if(trim($rut)!=""){
            $sql1.=" or a.rutCliente='$rut' ";
        }

        if($sql=="" && $sql1==""){
            $sql=" 1=2";
        }
        $registrar=true;
        //$agenda_existe=$contratoRepository->findByPersSinContr(null,null,null,null,null,3,$sql.$sql1);
        //$contrato_existe=$contratoRepository->findByPers(null,null,null,null,null, $sql.$sql1);
        //if(null != $contrato_existe){
        $agendas=$agendaRepository->findByPers(null,null,null,null,null, 3,$sql.$sql1);
        
        if(null != $agendas ){
            $cont_agendas=0;
            foreach($agendas as $_agenda ){
                
                $contrato_terminado=$contratoRepository->findOneBy(['agenda'=>$_agenda, 'isFinalizado'=>true]);

                if(null == $contrato_terminado){
                    $cont_agendas+=1;
                }
            }
            if($cont_agendas>1){
                $registrar=false;
            }
        }

        
        if(!$registrar){
         $error='<div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Advertencia!!</h5>
                Este usuario se encuentra duplicado
                </div>';
        }

        if ($form->isSubmitted() && $form->isValid()) {
            

           
            if($request->request->get('chkStatus')==5){
                if(null !== $request->request->get('cboAbogado')){
                    $abogado=$usuarioRepository->find($request->request->get('cboAbogado'));
                    $agenda->setAbogado($abogado);
                    $abogadoDestino=$abogado;
                }
                if($request->request->get('cboHoras')=='00:00'){
                    $agenda_sobrecupos=$agendaRepository->findByPers($request->request->get('cboAbogado'),null,null,'4,5,7,8,9,10', null,1," a.fechaAsignado = '".$request->request->get('txtFechaAgendamiento')." 00:00:00'");
                    $cont=0;
                    $isAgendado=true;
                    foreach($agenda_sobrecupos as $agenda_sobrecupo){
                        $cont++;
                    }
                    if($cont<$abogado->getSobrecupo()){
                        //$sobrecupo="Sobre Cupo";
                        $isAgendado=null;
                    }
                    
                }else{
                    $isAgendado=$agendaRepository->findBy(['abogado'=>$request->request->get('cboAbogado'),
                                                    'fechaAsignado'=>new \DateTime($request->request->get('txtFechaAgendamiento')." ".$request->request->get('cboHoras').":00"),
                                                    'status'=>$request->request->get('chkStatus')]);
                }
                if(null != $isAgendado){
                    $error='<div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-ban"></i> Alguien se adelanto!!</h5>
                    En el momento que presionaste en Gestionar, otro agendador asignó al abogado '.$abogado->getNombre().' a las '.$request->request->get('txtFechaAgendamiento')." ".$request->request->get('cboHoras').":00".' hrs. Intenta otro Horario. 
                  </div>';

                  $this->addFlash('error','En el momento que presionaste en Gestionar, otro agendador asignó al abogado '.$abogado->getNombre().' a las '.$request->request->get('txtFechaAgendamiento')." ".$request->request->get('cboHoras').":00".' hrs. Intenta otro Horario. ');
                    $abortar=true;
                }else{
                    //reasignamos conversación con adereso async. solo si se inicio una primera conversación.
                    if($agenda->getAderesoStatusCode()=='200' || $agenda->getAderesoStatusCode()=='207'){   
                        shell_exec("cd ". $this->getParameter('url_raiz').";php bin/console adereso:reasignar-conversacion --agendaId ".$agenda->getId()." > /dev/null 2>&1 &");
                    }

                }
            }
            
            
            /*if(null !== $request->request->get('cboAbogado')){
                $abogado=$usuarioRepository->find($request->request->get('cboAbogado'));
                $agenda->setAbogado($abogado);
            }*/
            

            switch($request->request->get('chkStatus')){
                case 8:
                case 9:
                    $agenda->setAbogado(null);
                    $subStatus=$agendaSubStatusRepository->find($request->request->get('cboSubStatus'));
                    break;
                case 2:
                    $agenda->setFechaSeguimiento(new DateTime(date("Y-m-d H:i:s")));
                    break;
               
            }

            if(null !== $request->request->get('cboAgendador')){
                $agenda->setAgendador($usuarioRepository->find($request->request->get('cboAgendador')));
            }
            
            if(null !== $request->request->get('txtCiudad')){
                $agenda->setCiudadCliente($request->request->get('txtCiudad'));
            }
            if(null !== $request->request->get('txtFechaAgendamiento')){
                $agenda->setFechaAsignado(new \DateTime($request->request->get('txtFechaAgendamiento')." ".$request->request->get('cboHoras').":00"));
            }
            if(null !== $request->request->get('txtMonto')){
                $agenda->setMonto($request->request->get('txtMonto'));
            }
            if(null !== $request->request->get('txtPagoActual')){
                $agenda->setPagoActual($request->request->get('txtPagoActual'));
            }
            if(null !== $request->request->get('cboReunion')){
                $agenda->setReunion($reunionRepository->find($request->request->get('cboReunion')));
            }
           
            $agenda->setObservacion($request->request->get('txtObservacion'));
            $agenda->setSubStatus($subStatus);
            $this->getDoctrine()->getManager()->flush();
            $entityManager = $this->getDoctrine()->getManager();
            
            if(!$abortar){
                $agenda->setStatus($agendaStatusRepository->find($request->request->get('chkStatus')));
                $observacion=new AgendaObservacion();
                $observacion->setAgenda($agenda);
                $observacion->setUsuarioRegistro($usuarioRepository->find($user->getId()));
                $observacion->setStatus($agendaStatusRepository->find($request->request->get('chkStatus')));
                $observacion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                $observacion->setObservacion($request->request->get('txtObservacion'));
                $observacion->setSubStatus($subStatus);
                $observacion->setAbogadoDestino($abogadoDestino);
                $entityManager->persist($observacion);
                $entityManager->flush();
                $entityManager->persist($agenda);
                $entityManager->flush();
                return $this->redirectToRoute('panel_agendador_index');
            }

        }
       
        return $this->render('panel_agendador/new.html.twig', [
            'agenda'=>$agenda,
            'error'=>$error,
            'form' => $form->createView(),
            'pagina'=>$pagina->getNombre().' | Asignar',
            'statues'=>$agendaStatusRepository->findBy(['id'=>$agenda->getAgendador()->getUsuarioTipo()->getStatues()],['orden'=>'asc']),
        ]);

    }
    /**
     * @Route("/{id}/engestion", name="panel_agendador_engestion", methods={"GET","POST"})
     */
    public function engestion(Agenda $agenda):Response
    {
        return $this->render('panel_agendador/engestion.html.twig');
    }

    

    /**
     * @Route("/{id}/abogados", name="panel_agendador_abogados", methods={"GET","POST"})
     */
    public function abogados(Agenda $agenda,Request $request,
                        UsuarioRepository $usuarioRepository,
                        ReunionRepository $reunionRepository):Response
    {
        if($agenda->getCampania() == 'FDTC120225 NOTTFO2'){ 
            $abogados = $usuarioRepository->findBy(['id'=>[16952,23850,247,20955,22299,23854,15574,6288]]);
        }else{
            $abogados = $usuarioRepository->findByCuenta($agenda->getCuenta()->getId(),['usuarioTipo'=>6,'estado'=>1]);
        }   
        return $this->render('panel_agendador/abogados.html.twig', [
            'abogados'=>$abogados,
            'agenda'=>$agenda,
        
            'reuniones'=>$reunionRepository->findAll(),
            
        ]);
    }

    /**
     * @Route("/{id}/calendario", name="panel_agendador_calendario", methods={"GET","POST"})
     */
    public function calendario(Agenda $agenda, Request $request, UsuarioRepository $usuarioRepository,AgendaRepository $agendaRepository):Response
    {
        //$agendas=$agendaRepository->findBy(['cuenta'=>$agenda->getCuenta()->getId(),'status'=>[4,5]]);
        $usuario=$request->query->get('abogado');
        $usuario=$usuarioRepository->find($request->query->get('abogado'));
        
        $agendas=$agendaRepository->findByPers($usuario->getId(),$agenda->getCuenta()->getEmpresa()->getId(),null,'4,5,7,8,9,10', null,1);
        
        return $this->render('panel_agendador/calendario.html.twig',[
            'agendas'=>$agendas,
            'abogado'=>$usuario,
            
        ]);
    }
    
    /**
     * @Route("/{id}/edit", name="panel_agendador_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Agenda $agenda): Response
    {
        $this->denyAccessUnlessGranted('edit','panel_agendador');
        $form = $this->createForm(AgendaType::class, $agenda);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('panel_agendador_index');
        }

        return $this->render('panel_agendador/edit.html.twig', [
            'agenda' => $agenda,
            'form' => $form->createView(),
        ]);
    }
     
}
