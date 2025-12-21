<?php

namespace App\Controller;

use App\Repository\EncuestaPreguntasRepository;
use App\Repository\EncuestaRepository;
use App\Repository\VwContratoRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Request;


class ReporteEncuestasController extends AbstractController
{
    /**
     * @Route("/reporte_encuestas", name="app_reporte_encuestas")
     */
    public function index(): Response
    {
        return $this->render('reporte_encuestas/index.html.twig', [
            'controller_name' => 'ReporteEncuestasController',
        ]);
    }
    /**
     * @Route("/reporte_gestiones", name="app_reporte_gestiones")
     */
    public function gestiones(): Response
    {
        return $this->render('reporte_encuestas/gestiones.html.twig', [
            'controller_name' => 'ReporteGestionesController',
        ]);
    }

    /**
     * @Route("/reporte_encuestas/export", name="app_reporte_encuestas_export")
     */
    public function exportEncuestas(Request $request, EncuestaPreguntasRepository $encuestaRepository): StreamedResponse
    {
         $user=$this->getUser();
        $compania=null;
        $filtro=null;
        $status=1;
        // Obtener las fechas de inicio y fin desde la solicitud
        $fechaInicio =new DateTime($request->query->get('fechaInicio'));
        $fechaFin =new DateTime($request->query->get('fechaFin'));

        // Obtener los datos desde el repositorio

        //$fecha="c.FechaEncuesta between '$fechaInicio' and '$fechaFin 23:59:59' and a.status in (7,14) and e.id=2" ;
                    
        // Obtener los datos desde el repositorio
        $encuestas = $encuestaRepository->findEncuestasByFechaRange($fechaInicio, $fechaFin);
       //$encuestas = $contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha,false,0);
        
        // Crear un nuevo archivo Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Agregar encabezados
        $sheet->setCellValue('A1', 'Folio')
            ->setCellValue('B1', 'Agenda ID')
            ->setCellValue('C1', 'Compañia')
            ->setCellValue('D1', 'Calidad')
            ->setCellValue('E1', 'Fecha_encuesta')
            ->setCellValue('F1', 'Fecha_cierre')
            ->setCellValue('G1', 'Fecha contrato')
            ->setCellValue('H1', 'Tramitador')
            ->setCellValue('I1', 'Nota')
            ->setCellValue('J1', 'Respuesta abierta')
            ->setCellValue('K1', 'Pregunta')
            ->setCellValue('L1', 'Observación');

        // Agregar datos
        $row = 2;
        foreach ($encuestas as $encuesta) {
            foreach ($encuesta->getEncuesta()->getContrato()->getGrupo()->getUsuariogrupos() as $usuarioGrupo) {
                $usuario = $usuarioGrupo->getUsuario()->getNombre();
            }
            $sheet->setCellValue('A' . $row, $encuesta->getEncuesta()->getContrato()->getFolio())
                ->setCellValue('B' . $row,  $encuesta->getEncuesta()->getContrato()->getAgenda()->getId())
                ->setCellValue('C' . $row, $encuesta->getEncuesta()->getContrato()->getAgenda()->getCuenta()->getNombre())
                ->setCellValue('D' . $row, $usuario)
                ->setCellValue('E' . $row, is_null($encuesta->getEncuesta()->getFechaCreacion())?"":$encuesta->getEncuesta()->getFechaCreacion()->format('Y-m-d'))
                ->setCellValue('F' . $row, is_null($encuesta->getEncuesta()->getFechaCierre())?"":$encuesta->getEncuesta()->getFechaCierre()->format('Y-m-d'))
                ->setCellValue('G' . $row, $encuesta->getEncuesta()->getContrato()->getFechaCreacion()->format('Y-m-d'))
                ->setCellValue('H' . $row, is_null($encuesta->getEncuesta()->getContrato()->getTramitador())?'':$encuesta->getEncuesta()->getContrato()->getTramitador()->getNombre())
                ->setCellValue('I' . $row, $encuesta->getNota())
                ->setCellValue('J' . $row, $encuesta->getRespuestaAbierta())
                ->setCellValue('K' . $row, $encuesta->getPregunta())
                ->setCellValue('L' . $row, $encuesta->getEncuesta()->getObservacion());
            $row++;
        }

        // Crear el archivo Excel para descargar
        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        // Configurar los encabezados de la respuesta
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="reporte_encuestas'.date('Ymd-His').'.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    /**
     * @Route("/reporte_gestiones/export", name="app_reporte_gestiones_export")
     */
    public function exportGestiones(Request $request, EncuestaRepository $encuestaRepository): StreamedResponse
    {
        $user=$this->getUser();
        $compania=null;
        $filtro=null;
        $status=1;
        // Obtener las fechas de inicio y fin desde la solicitud
        $fechaInicio = new DateTime($request->query->get('fechaInicio'));
        $fechaFin =new DateTime($request->query->get('fechaFin'));

        //$fecha="c.FechaGestion between '$fechaInicio' and '$fechaFin 23:59:59' and a.status in (7,14) and e.id=2" ;
                    
        // Obtener los datos desde el repositorio
        $gestiones = $encuestaRepository->findGestionesByFechaRange($fechaInicio, $fechaFin);
       //$gestiones = $contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha,false,$status);
        
        
        // Crear un nuevo archivo Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Agregar encabezados
        $sheet->setCellValue('A1', 'Folio')
        ->setCellValue('B1', 'Agenda ID')
        ->setCellValue('C1', 'Grupo ID')
        ->setCellValue('D1', 'Usuario')
        ->setCellValue('E1', 'Fecha Encuesta')
        ->setCellValue('F1', 'Función Encuesta')
        ->setCellValue('G1', 'Función Respuesta')
        ->setCellValue('H1', 'Observación');

        // Agregar datos
        $row = 2;
        foreach ($gestiones as $encuesta) {
        
            foreach ($encuesta->getContrato()->getGrupo()->getUsuariogrupos() as $usuarioGrupo) {
                $usuario = $usuarioGrupo->getUsuario()->getNombre();
            }
            $sheet->setCellValue('A' . $row, $encuesta->getContrato()->getFolio())
                ->setCellValue('B' . $row,  $encuesta->getContrato()->getAgenda()->getId())
                ->setCellValue('C' . $row, is_null($encuesta->getContrato()->getGrupo())?'':$encuesta->getContrato()->getGrupo()->getId())   
                ->setCellValue('D' . $row, $encuesta->getUsuarioCreacion()->getNombre())
                ->setCellValue('E' . $row, $encuesta->getFechaCreacion()->format('Y-m-d'))
                ->setCellValue('F' . $row, is_null($encuesta->getFuncionEncuesta())?'':  $encuesta->getFuncionEncuesta()->getNombre())
                ->setCellValue('G' . $row, is_null($encuesta->getFuncionRespuesta()) ? '': $encuesta->getFuncionRespuesta()->getNombre())
                ->setCellValue('H' . $row, $encuesta->getObservacion());
            /*$sheet->setCellValue('A' . $row, $encuesta->getFolio())
                ->setCellValue('B' . $row,  $encuesta->getAgenda()->getId())
                ->setCellValue('C' . $row, is_null($encuesta->getGrupo())?'':$encuesta->getGrupo()->getId())   
                ->setCellValue('D' . $row, $usuario)
                ->setCellValue('E' . $row, $encuesta->getFechaCreacion()->format('Y-m-d'))
                ->setCellValue('F' . $row, $encuesta->getGestionFuncionEncuesta())
                ->setCellValue('G' . $row, $encuesta->getGestionFuncionRespuesta())
                ->setCellValue('H' . $row, $encuesta->getGestionObservacion());*/
            $row++;
        }
        // Crear el archivo Excel para descargar
        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        // Configurar los encabezados de la respuesta
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="reporte_gestiones'.date('Ymd-His').'.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;

    }
}
