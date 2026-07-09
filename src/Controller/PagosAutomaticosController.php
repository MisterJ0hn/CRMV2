<?php

namespace App\Controller;

use App\Entity\Contrato;
use App\Repository\ContratoEstadoSuscripcionRepository;
use App\Repository\ContratoHistoricoSuscripcionRepository;
use App\Repository\CuotaRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\UsuarioRepository;
use App\Repository\VwContratoPagoAutomaticoRepository;
use App\Repository\VwCuotaConEquipoRepository;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/pagos_automaticos")
 */
class PagosAutomaticosController extends AbstractController
{
    /**
     * @Route("/", name="pagos_automaticos_index", methods={"GET"})
     */
    public function index(
        VwContratoPagoAutomaticoRepository $contratoPagoAutomaticoRepository,
        PaginatorInterface $paginator,
        ModuloPerRepository $moduloPerRepository,
        Request $request,
        UsuarioRepository   $usuarioRepository,
        ContratoEstadoSuscripcionRepository $contratoEstadoSuscripcionRepository
    ): Response {
        $this->denyAccessUnlessGranted('view', 'pagos_automaticos');
        $user = $this->getUser();
        $pagina = $moduloPerRepository->findOneByName('pagos_automaticos', $user->getEmpresaActual());

        $cerradores = $usuarioRepository->findBy(["usuarioTipo"=>6,"estado"=>1]);
        $estados = $contratoEstadoSuscripcionRepository->findAll();

        [$filtro, $folio, $estado, $fecha, $cerrador, $dateInicio, $dateFin] = $this->buildFiltros($request);

        $query = $contratoPagoAutomaticoRepository->findBySuscripcion($filtro, $folio, $estado, $fecha,$cerrador);
        $total = $contratoPagoAutomaticoRepository->findBySuscripcionTotal($filtro, $folio, $estado, $fecha,$cerrador);
        $totalEstados = $contratoPagoAutomaticoRepository->totalPorEstado($filtro, $folio, $estado, $fecha,$cerrador);
        $totalAbogados = $contratoPagoAutomaticoRepository->totalPorAbogado($filtro, $folio, $estado, $fecha,$cerrador);
      
        $contratos = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20,
            []
        );

        return $this->render('pagos_automaticos/index.html.twig', [
            'contratos'      => $contratos,
            'bFiltro'     => $filtro,
            'bFolio'      => $folio,
            'dateInicio'  => $dateInicio,
            'dateFin'     => $dateFin,
            'total'       => $total['total'] ?? 0,
            'pagina'      => $pagina ? $pagina->getNombre() : 'Pagos Automáticos',
            'bCerrador'   => $cerrador,
            'cerradores'  => $cerradores,
            'totalEstados'=>$totalEstados,
            'totalAbogados'=>$totalAbogados,
            'estados' => $estados,
            'bEstado' => $estado
        ]);
    }

    /**
     * @Route("/historico_suscripcion/{id}", name="pagos_automaticos_historico_suscripcion", methods={"GET"})
     */
    public function historicoSuscripcion(Contrato $contrato,
        ContratoHistoricoSuscripcionRepository $contratoHistoricoSuscripcionRepository,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('view', 'pagos_automaticos');
        $suscripciones = $contratoHistoricoSuscripcionRepository->findBy(['contrato' => $contrato->getId()]);

        return $this->render('pagos_automaticos/_historico_suscripcion.html.twig', [
            'suscripciones' => $suscripciones,
            'contrato'     => $contrato
        ]);
    }

    /**
     * @Route("/excel", name="pagos_automaticos_excel", methods={"GET"})
     */
    public function excel(
        VwContratoPagoAutomaticoRepository $contratoPagoAutomaticoRepository,
        CuotaRepository $cuotaRepository,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('view', 'pagos_automaticos');

        [$filtro, $folio, $estado, $fecha, $cerrador] = $this->buildFiltros($request);

        $registros = $contratoPagoAutomaticoRepository->findBySuscripcion($filtro, $folio, $estado, $fecha, $cerrador);

        $spreadSheet = new Spreadsheet();
        $sheet = $spreadSheet->getActiveSheet();

        $encabezados = [
            'A' => 'Folio',
            'B' => 'AgendaId',
            'C' => 'SuscripcionId',
            'D' => 'Cliente',
            'E' => 'Abogado',
            'F' => 'Cto/Anexo',
            'G' => 'Estado',
            'H' => 'Cuotas No Anuladas',
            'I' => 'Cuota',
            'J' => 'ValorCuota',
            'K' => 'VctoCuota',
            'L' => 'PagoCuota',
            'M' => 'MontoPagado',
            'N' => 'ProximoPago',
            'O' => 'EstadoSuscripcionContrato',
        ];
        foreach ($encabezados as $columna => $titulo) {
            $sheet->setCellValue($columna . '1', $titulo);
        }

        $i = 2;
        foreach ($registros as $vwContrato) {
            $contrato = $vwContrato->getContrato();

            $estadoSuscripcionContrato = '';
            if ($contrato->getAceptaSuscripcion()) {
                if ($contrato->getEstadoSuscripcion() !== null) {
                    $estadoSuscripcionContrato = $contrato->getEstadoSuscripcion() === 'ACTIVA' ? 'Suscripción Activa' : 'Suscripción Fallida';
                } else {
                    $estadoSuscripcionContrato = 'Suscripción en proceso';
                }
            }

            $proximoPago = $cuotaRepository->findProximaFechaPago($contrato->getId());

            $sheet->setCellValue("A$i", $vwContrato->getFolio());
            $sheet->setCellValue("B$i", $contrato->getAgenda() ? $contrato->getAgenda()->getId() : null);
            $sheet->setCellValue("C$i", $contrato->getSuscripcionId());
            $sheet->setCellValue("D$i", $contrato->getCliente()->getNombre());
            $sheet->setCellValue("E$i", $contrato->getAgenda() && $contrato->getAgenda()->getAbogado() ? $contrato->getAgenda()->getAbogado()->getNombre() : '');
            $sheet->setCellValue("F$i", $vwContrato->getFechaCreacion() ? $vwContrato->getFechaCreacion()->format('Y-m-d H:i') : null);
            $sheet->setCellValue("G$i", $vwContrato->getEstadoSuscripcion());
            $sheet->setCellValue("H$i", $contrato->getCuotasNoAnuladas());
            $sheet->setCellValue("I$i", $vwContrato->getNumeroCuota());
            $sheet->setCellValue("J$i", $vwContrato->getMontoCuota());
            $sheet->setCellValue("K$i", $vwContrato->getFechaVencimiento() ? $vwContrato->getFechaVencimiento()->format('Y-m-d') : null);
            $sheet->setCellValue("L$i", $vwContrato->getFechaPagado() ? $vwContrato->getFechaPagado()->format('Y-m-d H:i:s') : null);
            $sheet->setCellValue("M$i", $vwContrato->getMontoPagado());
            $sheet->setCellValue("N$i", $proximoPago ? $proximoPago['fechaPago'] : null);
            $sheet->setCellValue("O$i", $estadoSuscripcionContrato);

            $i++;
        }

        $sheet->setTitle('Pagos Automaticos');

        $writer = new Xlsx($spreadSheet);
        $fileName = 'pagos_automaticos-' . date('Ymd-Him') . '.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);

        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

    private function buildFiltros(Request $request): array
    {
        $filtro = null;
        $folio  = null;
        $estado = null;
        $cerrador = null;

        if (null !== $request->query->get('bFolio') && $request->query->get('bFolio') != '') {
            $folio = $request->query->get('bFolio');
            $dateInicio = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 60 * 60 * 24 * 30);
            $dateFin    = date('Y-m-d');
            $fecha = null;
        } else {
            if (null !== $request->query->get('bFiltro') && $request->query->get('bFiltro') != '') {
                $filtro = $request->query->get('bFiltro');
            }
            if (null !== $request->query->get('bEstado') && $request->query->get('bEstado') != '') {
                $estado = $request->query->get('bEstado');
            }
            if (null !== $request->query->get('bFecha')) {
                $aux_fecha  = explode(' - ', $request->query->get('bFecha'));
                $dateInicio = $aux_fecha[0];
                $dateFin    = $aux_fecha[1];
            } else {
                $dateInicio = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 60 * 60 * 24 * 7);
                $dateFin    = date('Y-m-d');
            }
            if (null !== $request->query->get('bCerrador')) {
                $cerrador = $request->query->get('bCerrador');
            }
            $fecha = "c.fechaCreacion between '$dateInicio' and '$dateFin 23:59:59'";
        }

        return [$filtro, $folio, $estado, $fecha, $cerrador, $dateInicio, $dateFin];
    }
}
