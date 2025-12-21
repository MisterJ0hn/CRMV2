<?php

namespace App\Controller;

use App\Repository\ContratoRepository;
use App\Repository\CuentaRepository;
use App\Repository\CuotaRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\PagoRepository;
use App\Repository\VencimientoRepository;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
/**
     * @Route("/aviso_morosos")
     */
class AvisoMorososController extends AbstractController
{
    /**
     * @Route("/", name="aviso_morosos_index")
     */
    public function index(ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','aviso_morosos');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('aviso_morosos',$user->getEmpresaActual());
        
        return $this->render('aviso_morosos/index.html.twig', [
            'pagina' => $pagina->getNombre()
        ]);
    }
    /**
     * @Route("/excel", name="aviso_morosos_excel", methods={"GET"})
     */
    public function cobranzaExcel(ContratoRepository $contratoRepository, 
                        CuotaRepository $cuotaRepository,
                        PagoRepository $pagoRepository,
                        PaginatorInterface $paginator,
                        ModuloPerRepository $moduloPerRepository,
                        Request $request,
                        CuentaRepository $cuentaRepository,
                        VencimientoRepository $vencimientoRepository): Response
    {
        $this->denyAccessUnlessGranted('view','aviso_morosos');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('aviso_morosos',$user->getEmpresaActual());
        $vencimientoArray=$vencimientoRepository->findBy(['empresa'=>$user->getEmpresaActual()],['id'=>'ASC'],1);
        $filtro=null;
        $folio=null;
        $compania=null;
        $vencimiento=$vencimientoArray[0];
        $otros=' DATEDIFF(now(),c.fechaPago)>'.$vencimiento->getValMax();
        $fecha=null;
        $error='';
        $error_toast="";
        if(null !== $request->query->get('error_toast')){
            $error_toast=$request->query->get('error_toast');
        }
        
        if(null !== $request->query->get('bFecha')){
            $dateInicio=$request->query->get('bFecha');
        
            
        }else{
            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');

        }
        $fecha="c.fechaPago between '$dateInicio' and '$dateInicio 23:59:59' and a.status != 13 and a.status != 15";

        //$fecha.=$otros;
        switch($user->getUsuarioTipo()->getId()){
            case 1://tramitador
            case 3:
            case 4:
            case 8:
            case 13:
            
                $query=$cuotaRepository->findVencimiento(null,null,$compania,$filtro,null,true,$fecha,true,true);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;
            case 7://tramitador
                $query=$cuotaRepository->findVencimiento($user->getId(),null,$compania,$filtro,7,true,$fecha,true,true);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;
            case 6: //abogado
                $query=$cuotaRepository->findVencimiento($user->getId(),null,$compania,$filtro,6,true,$fecha,true,true);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;
            case 11://Administrativo

                //$query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha,true);
                $query=$cuotaRepository->findVencimiento(null,null,$compania,$filtro,null,true,$fecha,true,true);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
            break;
            case 12://Cobradores
                $lotes=array();
                foreach($user->getUsuarioLotes() as $usuarioLote){
                    $lotes[]=$usuarioLote->getLote()->getId();
                }
                if(count($lotes)>0){
                    $fecha.=" and co.idLote in (".implode(",",$lotes).") ";
                }else{
                    $fecha.=" and co.idLote is null ";
                }
                
                $query=$cuotaRepository->findVencimiento(null,null,null,$filtro,null,true,$fecha,true,true);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;

            default:
                //$query=$contratoRepository->findByPers(null,null,$compania,$filtro,null,$fecha,true);
                $query=$cuotaRepository->findVencimiento(null,null,null,$filtro,null,true,$fecha,true,true);
                $companias=$cuentaRepository->findByPers(null);
                
            break;
        }
        //$companias=$cuentaRepository->findByPers($user->getId());
        //$query=$contratoRepository->findAll();

        $spreadSheet=new Spreadsheet();

        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadSheet->getActiveSheet();
        $sheet->setCellValue('A1', 'folio');
        $sheet->setCellValue('B1', 'nombre');
        $sheet->setCellValue('C1', 'apellido');
        $sheet->setCellValue('D1', 'rut');
        $sheet->setCellValue('E1', 'email');
        $sheet->setCellValue('F1', 'fonocobra');
        $sheet->setCellValue('G1', 'telefono1');
        $sheet->setCellValue('H1', 'telefono2');
        $sheet->setCellValue('I1', 'tramitador');
        $sheet->setCellValue('J1', 'linea1');
        $sheet->setCellValue('K1', 'linea2');
        $sheet->setCellValue('L1', 'linea3');
        $sheet->setCellValue('M1', 'linea4');
        $sheet->setCellValue('N1', 'linea5');


/*
        $sheet->setCellValue('B1', 'Lote');
        
        
        $sheet->setCellValue('E1', 'Cobrador');
        
        
        $sheet->setCellValue('H1', 'Fecha Contrato');
        $sheet->setCellValue('I1', 'Vencimiento');
        $sheet->setCellValue('J1', 'En Mora');
        $sheet->setCellValue('K1', 'Último Pago');
        $sheet->setCellValue('L1', 'Ultima Respuesta');
        $sheet->setCellValue('M1', 'Fecha Compromiso');
        $sheet->setCellValue('N1', 'Q. Ges.');*/

        $sheet = $spreadSheet->getActiveSheet();
        $i=2;
        foreach($query as $cuota){


            $vencimiento=$vencimientoRepository->findAll();
            $endeuda="";
           // $otros=' DATEDIFF(\''. $dateInicio.'\',c.fechaPago)>='.$vencimiento[0]->getValMax();
            $otros=' DATEDIFF(now(),c.fechaPago)>'.$vencimiento[0]->getValMax();
            $cuotadeuda=$cuotaRepository->deudaTotal($cuota->getContrato(),$otros);
        
            if(count($cuotadeuda)>0){
                $endeuda= $cuotadeuda[0][1]-$cuotadeuda[0][2];

                $sheet->setCellValue("A$i",$cuota->getContrato()->getFolio());
                $lote="";
                if($cuota->getContrato()->getIdLote()){
                    $lote=$cuota->getContrato()->getIdLote()->getNombre();
                }

                //$sheet->setCellValue("B$i",$lote);
                $nombre=explode(" ",$cuota->getContrato()->getNombre());
                $sheet->setCellValue("B$i", $nombre[0]);
                $cobrador='';
                $correo_cobrador="";
                if($cuota->getContrato()->getIdLote()){
                    foreach( $cuota->getContrato()->getIdLote()->getUsuarioLotes() as $usuarioLote){
                        $cobrador =$usuarioLote->getUsuario()->getNombre();
                        $correo_cobrador=$usuarioLote->getUsuario()->getTelefono();
                    }
                }
                $sheet->setCellValue("D$i",'');
                $sheet->setCellValue("E$i", $cuota->getContrato()->getEmail());
                
                

                //$sheet->setCellValue("E$i", $cobrador);
                $sheet->setCellValue("F$i", $correo_cobrador);
                
                
                /*$sheet->setCellValue("H$i", $cuota->getContrato()->getFechaCreacion());
                $sheet->setCellValue("I$i", $cuota->getFechaPago());*/
                

                $sheet->setCellValue("K$i", "$".number_format($endeuda,0,",","."));
                $pago=$pagoRepository->findUPByContrato($cuota->getContrato());
                $ultimoPago='';
                $ultimoPagoLat="";
                if($pago){
                    $ultimoPago=$pago->getFechaPago()->format('Y-m-d')." ".$pago->getHoraPago()->format('H:i');
                    $ultimoPagoLat=$pago->getFechaPago()->format('d-m-Y');
                }
                
                $sheet->setCellValue("L$i","CONSULTE ALTERNATIVAS DE PAGO");
                $sheet->setCellValue("M$i", $cuota->getContrato()->getAgenda()->getCuenta()->getNombre());
                $sheet->setCellValue("J$i", date("d-m-Y",strtotime( $dateInicio)));
                /*$sheet->setCellValue("K$i", $ultimoPago);
                $sheet->setCellValue("L$i", $cuota->getContrato()->getUltimaFuncion());
                $sheet->setCellValue("N$i", $cuota->getContrato()->getQMov());*/
            
                $i++;
            }else{
                $endeuda= 0;
            
            
            }

        }
        $sheet->setTitle("Clientes Morosos");
 
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadSheet);
    
        // Create a Temporary file in the system
        $fileName = 'clientesMorosos.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
    
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
    
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);

    }
}
