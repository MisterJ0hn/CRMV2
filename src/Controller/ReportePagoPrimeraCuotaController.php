<?php

namespace App\Controller;

use App\Repository\ConfiguracionRepository;
use App\Repository\VwPrimeraCuotaDeContratoRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class ReportePagoPrimeraCuotaController extends AbstractController
{
    /**
     * @Route("/reporte_pago_primera_cuota", name="app_reporte_pago_primera_cuota")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('view','exportar_primera_cuota');
        return $this->render('reporte_pago_primera_cuota/index.html.twig', [
            'controller_name' => 'ReportePagoPrimeraCuotaController',
        ]);
    }
    /**
     * @Route("/reporte_pago_primera_cuota_export", name="app_reporte_pago_primera_cuota_export")
     */
    public function exportar(Request $request, VwPrimeraCuotaDeContratoRepository $primeraCuotaContratoRepository, ConfiguracionRepository $configuracionRepository): Response
    {

        // Obtener las fechas de inicio y fin desde la solicitud
        $fechaInicio = $request->query->get('fechaInicio')." 00:00:00";
        $fechaFin = $request->query->get('fechaFin')." 23:59:59";

        // Obtener los datos desde el repositorio
        $cuotas = $primeraCuotaContratoRepository->findByFechas($fechaInicio,$fechaFin);


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Agregar encabezados
        $sheet->setCellValue('A1', 'Folio')
            ->setCellValue('B1', 'Agenda ID')
            ->setCellValue('C1', 'fecha_contrato')
            ->setCellValue('D1', 'MontoContrato')
            ->setCellValue('E1', 'Cerrador')
            
            ->setCellValue('F1', 'fecha_vcto_1eraCuota')
            ->setCellValue('G1', 'Monto_vcto_1eraCuota')
            ->setCellValue('H1', 'Monto_pago_1eraCuota')
            ->setCellValue('I1', 'Fecha_pago_1eraCuota')
            ->setCellValue('J1', 'Status');

        // Agregar datos
        $row = 2;
        $configuracion=$configuracionRepository->find(1);
        $deudaMinima=$configuracion->getDeudaminima();

        foreach ($cuotas as $cuota) {
           
            $status="";
            if($cuota->getMonto()<=($cuota->getPagado()+$deudaMinima)){
                $status="Pagado";
            }else{
                $status="Pendiente";
            }
            if(!is_null($cuota->getContrato()->getFechaDesiste())){
                $status="Desistido";
            }
            $sheet->setCellValue('A' . $row, $cuota->getContrato()->getFolio())
                    ->setCellValue('B' . $row,  $cuota->getContrato()->getAgenda()->getId())
                    ->setCellValue('C' . $row, is_null($cuota->getContrato()->getFechaCreacion())?"":$cuota->getContrato()->getFechaCreacion()->format('Y-m-d H:i:s'))
                    ->setCellValue('D' . $row,  $cuota->getContrato()->getMontoContrato())
                    ->setCellValue('E' . $row, $cuota->getContrato()->getAgenda()->getAbogado()->getNombre())
                    
                    ->setCellValue('F' . $row, is_null($cuota->getFechaVencimiento())?"":$cuota->getFechaVencimiento()->format('Y-m-d H:i:s'))
                    ->setCellValue('G' . $row, $cuota->getMonto())
                    ->setCellValue('H' . $row, $cuota->getPagado())
                    ->setCellValue('I' . $row,  is_null($cuota->getFechaPago())?"":$cuota->getFechaPago()->format('Y-m-d H:i:s'))
                    ->setCellValue('J' . $row, $status);
            $row++;
        }

        // Crear el archivo Excel para descargar
        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        // Configurar los encabezados de la respuesta
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="reporte_pago_primera_cuota'.date('Ymd-His').'.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
