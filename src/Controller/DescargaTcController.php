<?php

namespace App\Controller;

use App\Entity\ErrorSistemaLog;
use App\Repository\DescargaTcRepository;
use Nick\SecureSpreadsheet\Encrypt;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class DescargaTcController extends AbstractController
{
    /**
     * @Route("/descarga_tc", name="app_descarga_tc")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('view','app_descarga_tc');

        return $this->render('descarga_tc/index.html.twig', [
            'pagina'=>'TC',
            'controller_name' => 'DescargaTcController',
        ]);
    }

    /**
     * @Route("/descarga_tc/export", name="app_descarga_tc_export")
     */
    public function exportar(DescargaTcRepository $descargaTcRepository): Response
    {
        $this->denyAccessUnlessGranted('view','app_descarga_tc');

        try {
            $registros = $descargaTcRepository->findTodos();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'Folio')
                ->setCellValue('B1', 'Fecha Contrato/Anexo')
                ->setCellValue('C1', 'Año pago')
                ->setCellValue('D1', 'Mes pago')
                ->setCellValue('E1', 'Fecha pago')
                ->setCellValue('F1', 'Total cuotas')
                ->setCellValue('G1', 'Total Mes pago')
                ->setCellValue('H1', 'Suma Cuotas Futuras No pagadas')
                ->setCellValue('I1', 'Monto Contrato')
                ->setCellValue('J1', 'Tramitador')
                ->setCellValue('K1', 'Materia')
                ->setCellValue('L1', 'Fecha Vencimiento Última Cuota no pagada');

            $row = 2;
            foreach ($registros as $d) {
                $sheet->setCellValue('A' . $row, $d->getFolio())
                    ->setCellValue('B' . $row, $d->getFechaContratoAnexo() ? $d->getFechaContratoAnexo()->format('Y-m-d H:i:s') : '')
                    ->setCellValue('C' . $row, $d->getAnioPagado())
                    ->setCellValue('D' . $row, $d->getMesPagado())
                    ->setCellValue('E' . $row, $d->getFechaPago() ? $d->getFechaPago()->format('Y-m-d H:i:s') : '')
                    ->setCellValue('F' . $row, $d->getTotalCuota())
                    ->setCellValue('G' . $row, $d->getTotalMesPago())
                    ->setCellValue('H' . $row, $d->getSumaCuotFuturNopagadas())
                    ->setCellValue('I' . $row, $d->getMontoContrato())
                    ->setCellValue('J' . $row, $d->getTramitador())
                    ->setCellValue('K' . $row, $d->getMateria())
                    ->setCellValue('L' . $row, ($d->getVencimientoUltCuotaNopagada() && $d->getVencimientoUltCuotaNopagada()->format('Y') > 1) ? $d->getVencimientoUltCuotaNopagada()->format('Y-m-d') : '');
                $row++;
            }

            $writer = new Xlsx($spreadsheet);

            $fileName = "TC_".date('Ymd_His') . '.xlsx';
            $fileName_protegido = "TC_".date('Ymd_His') . '_protegido.xlsx';
            $temp = tempnam(sys_get_temp_dir(), $fileName);
            $temp_file_protegido = tempnam(sys_get_temp_dir(), $fileName_protegido);
            $writer->save($temp);

            $encryptor = new Encrypt();
            $encryptor->input($temp)
                ->password("wug79YAIF")
                ->output($temp_file_protegido);

            return $this->file($temp_file_protegido, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        } catch (\Exception $e) {
            $error = new ErrorSistemaLog();
            $error->setFecha(new \DateTime(date('Y-m-d H:i:s')));
            $error->setModulo('TC');
            $error->setMensaje($e->getMessage());

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($error);
            $manager->flush();

            return new Response('Ocurrió un error al generar el reporte.: '.$e->getMessage(), 500);
        }
    }
}
