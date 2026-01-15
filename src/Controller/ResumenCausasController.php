<?php

namespace App\Controller;

use App\Repository\AgendaRepository;
use App\Repository\CuentaRepository;
use App\Repository\UsuarioRepository;
use App\Repository\VwCausasActivasFinalRepository;
use App\Repository\VwClientesActivosFinalRepository;
use App\Repository\VwResumenCausasRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @Route("/resumen_causas")
 */
class ResumenCausasController extends AbstractController
{
    /**
     * @Route("/", name="resumen_causas_index")
     */
    public function index(Request $request,
                        VwResumenCausasRepository $vwResumenCausasRepository,
                        VwCausasActivasFinalRepository $vwCausasActivasFinalRepository,
                        VwClientesActivosFinalRepository $vwClientesActivosFinalRepository,
                        PaginatorInterface $paginator,
                        CuentaRepository $cuentaRepository): Response
    {
        $this->denyAccessUnlessGranted('view','Resumen_Causas_Familia');
        $user=$this->getUser();
        $companias="7";
        $bTipoCuenta="";
       $listado=[];
        if(null!=$request->query->get('bTipoCuenta') && $request->query->get('bTipoCuenta')!=""){
            $bTipoCuenta=$request->query->get('bTipoCuenta');
        }
       
        $resumen = $vwResumenCausasRepository->findGroupByTodo($companias);

/*
        
        switch ($bTipoCuenta) {
            case 'alDia':
               $listado =$vwCausasActivasFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'clientesActivos':
                $listado =$vwClientesActivosFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'clientesAlDia':
                $listado =$vwClientesActivosFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'clientesMorosos':
                $listado =$vwClientesActivosFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'clientesActivosVIP':
                $listado =$vwClientesActivosFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'clientesAlDiaVIP':
                $listado =$vwClientesActivosFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'conRol':
                $listado =$vwCausasActivasFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'sinRol':
                $listado =$vwCausasActivasFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'finalizadas':
                $listado =$vwCausasActivasFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            default:
                $listado =$vwCausasActivasFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            }*/
         $causasActivasFinal=$paginator->paginate(
            $listado, 
            $request->query->getInt('page', 1),
            20 ,
            array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));
           
        
        
        return $this->render('resumen_causas/index.html.twig', [
            'pagina' => 'Resumen Causas Familia',
            'resumen'=>$resumen,
            'listado'=>$causasActivasFinal,
            "tipoFiltro"=>"resumenCausas",
            "companias"=>$companias,
            "bTipoCuenta"=>$bTipoCuenta,
            "bMateria"=>"familia",
            
        ]);
    }

    /**
     * @Route("/civil", name="resumen_causas_civil")
     */
    public function resumencivil(Request $request,
                        VwResumenCausasRepository $vwResumenCausasRepository,
                        VwCausasActivasFinalRepository $vwCausasActivasFinalRepository,
                        VwClientesActivosFinalRepository $vwClientesActivosFinalRepository,
                        PaginatorInterface $paginator,
                        CuentaRepository $cuentaRepository): Response
    {
        $this->denyAccessUnlessGranted('view','Resumen_Causas_Civil');
        $user=$this->getUser();
        $companias="1,2,3,10";
        $bTipoCuenta="";
        $listado=[];
        if(null!=$request->query->get('bTipoCuenta') && $request->query->get('bTipoCuenta')!=""){
            $bTipoCuenta=$request->query->get('bTipoCuenta');
        }
       
        $resumen = $vwResumenCausasRepository->findGroupByTodo($companias);


        
        /*switch ($bTipoCuenta) {
            case 'alDia':
               $listado =$vwCausasActivasFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'clientesActivos':
                $listado =$vwClientesActivosFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'clientesAlDia':
                $listado =$vwClientesActivosFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'clientesMorosos':
                $listado =$vwClientesActivosFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'clientesActivosVIP':
                $listado =$vwClientesActivosFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'clientesAlDiaVIP':
                $listado =$vwClientesActivosFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'conRol':
                $listado =$vwCausasActivasFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'sinRol':
                $listado =$vwCausasActivasFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'finalizadas':
                $listado =$vwCausasActivasFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            default:
                $listado =$vwCausasActivasFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            }*/
         $causasActivasFinal=$paginator->paginate(
            $listado, 
            $request->query->getInt('page', 1),
            20 ,
            array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));
           
        
        
        return $this->render('resumen_causas/index.html.twig', [
            'pagina' => 'Resumen Causas Civil',
            'resumen'=>$resumen,
            'listado'=>$causasActivasFinal,
            "tipoFiltro"=>"resumenCausas",
            "companias"=>$companias,
            "bTipoCuenta"=>$bTipoCuenta,
            "bMateria"=>"civil",
            
            
        ]);
    }

    /**
     * @Route("/tributaria", name="resumen_causas_tributaria")
     */
    public function resumentributaria(Request $request,
                        VwResumenCausasRepository $vwResumenCausasRepository,
                        VwCausasActivasFinalRepository $vwCausasActivasFinalRepository,
                        VwClientesActivosFinalRepository $vwClientesActivosFinalRepository,
                        PaginatorInterface $paginator,
                        CuentaRepository $cuentaRepository): Response
    {
         $this->denyAccessUnlessGranted('view','resumen_causas_tributaria');
        $user=$this->getUser();
        $companias="4,6";
        $bTipoCuenta="";
       $listado=[];
        if(null!=$request->query->get('bTipoCuenta') && $request->query->get('bTipoCuenta')!=""){
            $bTipoCuenta=$request->query->get('bTipoCuenta');
        }
       
        $resumen = $vwResumenCausasRepository->findGroupByTodo($companias);


        /*
        switch ($bTipoCuenta) {
            case 'alDia':
               $listado =$vwCausasActivasFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'clientesActivos':
                $listado =$vwClientesActivosFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'clientesAlDia':
                $listado =$vwClientesActivosFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'clientesMorosos':
                $listado =$vwClientesActivosFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'clientesActivosVIP':
                $listado =$vwClientesActivosFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'clientesAlDiaVIP':
                $listado =$vwClientesActivosFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'conRol':
                $listado =$vwCausasActivasFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'sinRol':
                $listado =$vwCausasActivasFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            case 'finalizadas':
                $listado =$vwCausasActivasFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            default:
                $listado =$vwCausasActivasFinalRepository->findByCuentas($companias, $bTipoCuenta);
                break;
            }*/
         $causasActivasFinal=$paginator->paginate(
            $listado, 
            $request->query->getInt('page', 1),
            20 ,
            array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));
           
        
        
        return $this->render('resumen_causas/index.html.twig', [
            'pagina' => 'Resumen Causas Tributaria',
            'resumen'=>$resumen,
            'listado'=>$causasActivasFinal,
            "tipoFiltro"=>"resumenCausas",
            "companias"=>$companias,
            "bTipoCuenta"=>$bTipoCuenta,
            "bMateria"=>"tributaria",
            
            
        ]);
    }

    /**
     * @Route("/resumencausastramitadores", name="resumen_causas_tramitadores", methods={"GET","POST"})
     */
    public function resumencausastramitadores(Request $request, $cuentasId, $tipoCausa, $total, VwCausasActivasFinalRepository $vwCausasActivasFinalRepository,VwClientesActivosFinalRepository $vwClientesActivosFinalRepository): Response
    {
        $user=$this->getUser();

        
        $nombre_status="";
        switch ($tipoCausa) {
            case 'alDia':
                $nombre_status="Causas al dia";
                $queryresumen=$vwCausasActivasFinalRepository->groupByCerrador($cuentasId,$tipoCausa);
                break;
            case 'clientesActivos':
                $nombre_status="Clientes Activos";
                 $queryresumen=$vwClientesActivosFinalRepository->groupByCerrador($cuentasId,$tipoCausa);
                break;
            case 'clientesAlDia':
                $nombre_status="Clientes al dia";
                 $queryresumen=$vwClientesActivosFinalRepository->groupByCerrador($cuentasId,$tipoCausa);
                break;
            case 'clientesMorosos':
                $nombre_status="Clientes Morosos";
                 $queryresumen=$vwClientesActivosFinalRepository->groupByCerrador($cuentasId,$tipoCausa);
                break;
            case 'clientesActivosVIP':
                $nombre_status="Clientes Activos VIP";
                 $queryresumen=$vwClientesActivosFinalRepository->groupByCerrador($cuentasId,$tipoCausa);
                break;
            case 'clientesAlDiaVIP':
                $nombre_status="Clientes al dia VIP";
                 $queryresumen=$vwClientesActivosFinalRepository->groupByCerrador($cuentasId,$tipoCausa);
                break;
            case 'conRol':
                $nombre_status="Causas con rol";
                $queryresumen=$vwCausasActivasFinalRepository->groupByCerrador($cuentasId,$tipoCausa);
                break;
            case 'sinRol':
                $nombre_status="Causas sin rol";
                $queryresumen=$vwCausasActivasFinalRepository->groupByCerrador($cuentasId,$tipoCausa);
                break;
            case 'finalizadas':
                $nombre_status="Causas finalizadas";
                $queryresumen=$vwCausasActivasFinalRepository->groupByCerrador($cuentasId,$tipoCausa);
                break;
            default:
                $nombre_status="Causas";
                $queryresumen=$vwCausasActivasFinalRepository->groupByCerrador($cuentasId,$tipoCausa);
                break;
            }
        


        return $this->render('resumen_causas/_resumencausastramitadores.html.twig',[
            'tramitadores'=>$queryresumen,           
            'nombre_status'=>$nombre_status,
            'total'=>$total,
        ]);
    }

    /**
     * @Route("/excel", name="resumen_causas_excel", methods={"GET","POST"})
     */
    public function excel(Request $request,
                        VwCausasActivasFinalRepository $vwCausasActivasFinalRepository,
                        VwClientesActivosFinalRepository $vwClientesActivosFinalRepository, AgendaRepository $agendaRepository ): Response
    {
        
        $this->denyAccessUnlessGranted('view','resumen_causas_excel');
        
        $materia="";
        $bTipoCuenta="";
       
        if(null!=$request->query->get('bTipoCuenta') && $request->query->get('bTipoCuenta')!=""){
            $bTipoCuenta=$request->query->get('bTipoCuenta');
        }
        if(null!=$request->query->get('bMateria') && $request->query->get('bMateria')!=""){
            $materia=$request->query->get('bMateria');
        }
        switch ($materia) {
            case 'civil':
                $cuentasId="1,2,3,10";
                $fileName="ResumenCivil.xlsx";
                $titulo = "Resumen Causas Civil ";
                break;
            case 'tributaria':
                $cuentasId="4,6";
                $fileName="ResumenTributaria.xlsx";
                $titulo = "Resumen Causas Tributaria ";
                break;
            case 'familia':
                $cuentasId="7";
                $fileName="ResumenFamilia.xlsx";
                $titulo = "Resumen Causas Familia ";
                break;
        }

         
        switch ($bTipoCuenta) {
            case 'alDia':
                $nombre_status="Causas al dia";
                $queryresumen=$vwCausasActivasFinalRepository->findByCuentas($cuentasId,$bTipoCuenta);
                break;
            case 'clientesActivos':
                $nombre_status="Clientes Activos";
                 $queryresumen=$vwClientesActivosFinalRepository->findByCuentas($cuentasId,$bTipoCuenta);
                break;
            case 'clientesAlDia':
                $nombre_status="Clientes al dia";
                 $queryresumen=$vwClientesActivosFinalRepository->findByCuentas($cuentasId,$bTipoCuenta);
                break;
            case 'clientesMorosos':
                $nombre_status="Clientes Morosos";
                 $queryresumen=$vwClientesActivosFinalRepository->findByCuentas($cuentasId,$bTipoCuenta);
                break;
            case 'clientesActivosVIP':
                $nombre_status="Clientes Activos VIP";
                 $queryresumen=$vwClientesActivosFinalRepository->findByCuentas($cuentasId,$bTipoCuenta);
                break;
            case 'clientesAlDiaVIP':
                $nombre_status="Clientes al dia VIP";
                 $queryresumen=$vwClientesActivosFinalRepository->findByCuentas($cuentasId,$bTipoCuenta);
                break;
            case 'conRol':
                $nombre_status="Causas con rol";
                $queryresumen=$vwCausasActivasFinalRepository->findByCuentas($cuentasId,$bTipoCuenta);
                break;
            case 'sinRol':
                $nombre_status="Causas sin rol";
                $queryresumen=$vwCausasActivasFinalRepository->findByCuentas($cuentasId,$bTipoCuenta);
                break;
            case 'finalizadas':
                $nombre_status="Causas finalizadas";
                $queryresumen=$vwCausasActivasFinalRepository->findByCuentas($cuentasId,$bTipoCuenta);
                break;
            default:
                $nombre_status="Causas";
                $queryresumen=$vwCausasActivasFinalRepository->findByCuentas($cuentasId,$bTipoCuenta);
                break;
            }
        $spreadSheet=new Spreadsheet();

        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadSheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Materia');
        $sheet->setCellValue('B1', 'AgendaId');
        $sheet->setCellValue('C1', 'Folio');
        $sheet->setCellValue('D1', 'FechaCto');
        $sheet->setCellValue('E1', 'Tramitador');
        $sheet->setCellValue('F1', 'Cerrador');
        $sheet->setCellValue('G1', 'Activo');
        $sheet->setCellValue('H1', 'Moroso');
        $sheet->setCellValue('I1', 'VIP');
        $sheet->setCellValue('J1', 'Rol');
        $sheet->setCellValue('K1', 'U.ObservaciónCausa');
        $sheet->setCellValue('L1', 'C.Finalizada');
        $sheet->setCellValue('M1', 'Telefono Cliente');
        $sheet->setCellValue('N1', 'U.ObservacionCliente');

        $sheet = $spreadSheet->getActiveSheet();
        $i=2;
        foreach($queryresumen as $causa){
           
            $sheet->setCellValue('A'.$i, $causa->getCuentaNombre());
            $sheet->setCellValue('B'.$i, $causa->getAgendaId());
            $sheet->setCellValue('C'.$i, $causa->getFolio());
            $sheet->setCellValue('D'.$i, $causa->getFechaCto());
            $sheet->setCellValue('E'.$i, $causa->getTramitador());
            $sheet->setCellValue('F'.$i, $causa->getCerrador());
            $sheet->setCellValue('G'.$i, $causa->getActivo());
            $sheet->setCellValue('H'.$i, $causa->getMoroso());
            $sheet->setCellValue('I'.$i, $causa->getVip());
            $sheet->setCellValue('J'.$i, $causa->getRol());
            $sheet->setCellValue('K'.$i, $causa->getFechaRegistroObservacion()?$causa->getFechaRegistroObservacion():"");
            $sheet->setCellValue('L'.$i, $causa->getCausaFinalizada());
            if($bTipoCuenta=="clientesActivosVIP" || $bTipoCuenta=="clientesAlDiaVIP"){
                $agenda=$agendaRepository->find($causa->getAgendaId());
           
                $sheet->setCellValue('M'.$i, $agenda?$agenda->getTelefonoCliente():"");
            }
            $sheet->setCellValue('N'.$i, $causa->getFechaObservacionCliente()?$causa->getFechaObservacionCliente():"");
            $i++;
        }

        $sheet->setTitle($titulo);
 
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadSheet);
        /*$writer->setDelimiter(',');
        $writer->setEnclosure('');*/
            
        // Create a Temporary file in the system
        
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
    
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
    
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        
    }

}
