<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteTicketsController extends AbstractController
{
    /**
     * @Route("/reporte_tickets", name="app_reporte_tickets")
     */
    public function index(): Response
    {
        return $this->render('reporte_tickets/index.html.twig', [
            'controller_name' => 'ReporteTicketsController',
        ]);
    }

    /**
     * @Route("/reporte_tickets_export", name="app_reporte_tickets_export")
     */
    public function exportTickets(Request $request,TicketRepository $ticketRepository): Response
    {
        // Obtener las fechas de inicio y fin desde la solicitud
        $fechaInicio = new \DateTime($request->query->get('fechaInicio'));
        $fechaFin = new \DateTime($request->query->get('fechaFin'));

        // Obtener los datos desde el repositorio
        $tickets = $ticketRepository->findTicketsByFechaRange($fechaInicio, $fechaFin);
        // Crear un nuevo archivo Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Agregar encabezados
        $sheet->setCellValue('A1', 'Folio')
            ->setCellValue('B1', 'Agenda ID')
            ->setCellValue('C1', 'Folio sac')
            ->setCellValue('D1', 'Estado')
            ->setCellValue('E1', 'Usuario Origen')
            ->setCellValue('F1', 'Perfil Origen')
            ->setCellValue('G1', 'Encargado')
            ->setCellValue('H1', 'Urgencia')
            ->setCellValue('I1', 'Fecha Ingreso')
            ->setCellValue('J1', 'Ult. Gestion')
            ->setCellValue('K1', 'Motivo');

        // Agregar datos
        $row = 2;
        foreach ($tickets as $ticket) {
            $sheet->setCellValue('A' . $row, $ticket->getContrato()->getFolio())
                ->setCellValue('B' . $row,  $ticket->getContrato()->getAgenda()->getId())
                ->setCellValue('C' . $row, $ticket->getFolioSac())
                ->setCellValue('D' . $row, $ticket->getEstado()->getNombre())
                ->setCellValue('E' . $row, $ticket->getOrigen()->getNombre())
                ->setCellValue('F' . $row, $ticket->getOrigen()->getUsuarioTipo()->getNombre())
                ->setCellValue('G' . $row, $ticket->getEncargado()->getNombre())
                ->setCellValue('H' . $row, $ticket->getImportancia()->getUrgencia())
                ->setCellValue('I' . $row, $ticket->getFechaNuevo()->format('Y-m-d'))
                ->setCellValue('J' . $row, $ticket->getFechaUltimaGestion() ? $ticket->getFechaUltimaGestion()->format('Y-m-d') : '')
                ->setCellValue('K' . $row, $ticket->getMotivo());

            $row++;
        }

        // Crear el archivo Excel para descargar
        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        // Configurar los encabezados de la respuesta
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="reporte_tickets'.date('Ymd-His').'.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
