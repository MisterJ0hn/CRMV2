<?php

namespace App\Controller;

use App\Entity\Configuracion;
use App\Repository\ConsultasRepository;
use Nick\SecureSpreadsheet\Encrypt;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/consultas")
 */
class ConsultasController extends AbstractController
{
    /**
     * $colKeyMap format: ['A' => ['Header label', 'dqlAlias'], ...]
     */
    private function buildExcel(array $rows, string $sheetTitle, array $colKeyMap): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetTitle);

        foreach ($colKeyMap as $col => [$label]) {
            $sheet->setCellValue("{$col}1", $label);
        }

        $i = 2;
        foreach ($rows as $row) {
            foreach ($colKeyMap as $col => [, $key]) {
                $val = $row[$key] ?? '';
                if ($val instanceof \DateTimeInterface) {
                    $val = $val->format('Y-m-d');
                }
                $sheet->setCellValue("{$col}{$i}", $val);
            }
            $i++;
        }
        $configuracion = $this->getDoctrine()->getRepository(Configuracion::class)->find(1);

        $writer = new Xlsx($spreadsheet);
        $fileName = strtolower($sheetTitle)." ".date('Ymd_His') . '.xlsx';
        $fileName_protegido = strtolower($sheetTitle)." ".date('Ymd_His') . '_protegido.xlsx';
        $temp = tempnam(sys_get_temp_dir(), $fileName);
         $temp_file_protegido = tempnam(sys_get_temp_dir(), $fileName_protegido);
        $writer->save($temp);
        $encryptor = new Encrypt();
        $encryptor->input($temp)
        ->password($configuracion->getClaveEncriptacionDescargas())
        ->output( $temp_file_protegido);
        $temp = $temp_file_protegido; 

        return $this->file($temp, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

    private const COLS_BASE = [
        'A' => ['Materia',           'materia'],
        'B' => ['Folio',             'folio'],
        'C' => ['Agenda_id',        'agenda_id'],
        'D' => ['Fecha Contrato',    'fechaContrato'],
        'E' => ['Fecha Últ. Anexo',  'fechaUltAnexo'],
        'F' => ['Cliente',           'cliente'],
        'G' => ['Cerrador',          'nombre_cerrador'],
        'H' => ['Tramitador',        'nombre_tramitador'],
        'I' => ['Teléfono Cliente',  'telefono_cliente']
    ];

    /**
     * @Route("/activos", name="consultas_activos")
     */
    public function activos(): Response
    {
       
        $user=$this->getUser();
        return $this->render('consultas/activos.html.twig');
    }

    /**
     * @Route("/activos/excel", name="consultas_activos_excel", methods={"GET"})
     */
    public function activosExcel(ConsultasRepository $repo): Response
    {
        return $this->buildExcel($repo->findActivos(), 'Activos', self::COLS_BASE);
    }

    /**
     * @Route("/abandonados", name="consultas_abandonados")
     */
    public function abandonados(): Response
    {
        return $this->render('consultas/abandonados.html.twig');
    }

    /**
     * @Route("/abandonados/excel", name="consultas_abandonados_excel", methods={"GET"})
     */
    public function abandonadosExcel(ConsultasRepository $repo): Response
    {
        return $this->buildExcel($repo->findAbandonados(), 'Abandonados', self::COLS_BASE);
       
    }

    /**
     * @Route("/completados", name="consultas_completados")
     */
    public function completados(): Response
    {
        return $this->render('consultas/completados.html.twig');
    }

    /**
     * @Route("/completados/excel", name="consultas_completados_excel", methods={"GET"})
     */
    public function completadosExcel(ConsultasRepository $repo): Response
    {
        $cols = self::COLS_BASE + ['H' => ['Meses en sistema', 'mesesSistema']];
        return $this->buildExcel($repo->findCompletados(), 'Completados', $cols);
    }

    /**
     * @Route("/fantasmas", name="consultas_fantasmas")
     */
    public function fantasmas(): Response
    {
        return $this->render('consultas/fantasmas.html.twig');
    }

    /**
     * @Route("/fantasmas/excel", name="consultas_fantasmas_excel", methods={"GET"})
     */
    public function fantasmasExcel(ConsultasRepository $repo): Response
    {
        return $this->buildExcel($repo->findFantasmas(), 'Fantasmas', self::COLS_BASE);
    }
}
