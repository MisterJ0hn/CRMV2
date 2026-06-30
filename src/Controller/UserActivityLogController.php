<?php

namespace App\Controller;

use App\Repository\UserActivityLogRepository;
use App\Repository\UsuarioRepository;
use App\Repository\UsuarioTipoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user-activity-log")
 */
class UserActivityLogController extends AbstractController
{
    /**
     * @Route("/", name="user_activity_log_index", methods={"GET","POST"})
     */
    public function index(
        UserActivityLogRepository $logRepository,
        UsuarioRepository $usuarioRepository,
        UsuarioTipoRepository $usuarioTipoRepository
    ): Response {
        $this->denyAccessUnlessGranted('view', 'Actividad_Usuarios');

        $usuarios = $usuarioRepository->findBy(['estado' => true, 'usuarioTipo' => [1,2,3,4,5,6,7,10,11,12,13]], ['nombre' => 'ASC']);
        $perfiles = $usuarioTipoRepository->findBy(['id' => [1,2,3,4,5,6,7,10,11,12,13]], ['nombre' => 'ASC']);
        $modulos  = $logRepository->getModulosDistintos();

        return $this->render('user_activity_log/index.html.twig', [
            'usuarios' => $usuarios,
            'perfiles' => $perfiles,
            'modulos'  => $modulos,
        ]);
    }

    /**
     * @Route("/data", name="user_activity_log_data", methods={"GET"})
     */
    public function data(
        Request $request,
        UserActivityLogRepository $logRepository,
        PaginatorInterface $paginator
    ): Response {
        $this->denyAccessUnlessGranted('view', 'Actividad_Usuarios');

        $filtros = [
            'desde'   => $request->query->get('desde', date('Y-m-d')),
            'hasta'   => $request->query->get('hasta', date('Y-m-d')),
            'usuario' => $request->query->get('usuario'),
            'modulo'  => $request->query->get('modulo'),
            'perfil'  => $request->query->get('perfil'),
            'accion'  => $request->query->get('accion'),
        ];

        $query = $logRepository->findByFiltros(
            $filtros['usuario'] ? (int) $filtros['usuario'] : null,
            $filtros['desde'],
            $filtros['hasta'],
            $filtros['modulo'],
            $filtros['perfil'] ? (int) $filtros['perfil'] : null,
            $filtros['accion'] ?: null
        );

        $logs = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20,
            ['defaultSortFieldName' => 'l.fechaRegistro', 'defaultSortDirection' => 'desc']
        );

        return $this->render('user_activity_log/_tabla.html.twig', [
            'logs'    => $logs,
            'filtros' => $filtros,
        ]);
    }

    /**
     * Descarga el listado filtrado como archivo Excel (.xlsx).
     *
     * @Route("/excel", name="user_activity_log_excel", methods={"GET"})
     */
    public function excel(Request $request, UserActivityLogRepository $logRepository): StreamedResponse
    {
        $this->denyAccessUnlessGranted('view', 'Actividad_Usuarios_excel');

        $filtros = [
            'desde'   => $request->query->get('desde', date('Y-m-d')),
            'hasta'   => $request->query->get('hasta', date('Y-m-d')),
            'usuario' => $request->query->get('usuario'),
            'modulo'  => $request->query->get('modulo'),
            'perfil'  => $request->query->get('perfil'),
            'accion'  => $request->query->get('accion'),
        ];

        $logs = $logRepository->findByFiltros(
            $filtros['usuario'] ? (int) $filtros['usuario'] : null,
            $filtros['desde'],
            $filtros['hasta'],
            $filtros['modulo'],
            $filtros['perfil'] ? (int) $filtros['perfil'] : null,
            $filtros['accion'] ?: null
        )->getResult();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Log de Actividad');

        // ── Encabezados ──
        $headers = ['Fecha / Hora', 'Usuario', 'Perfil', 'Método', 'Ruta', 'Módulo', 'Acción', 'Estado HTTP', 'IP', 'Ciudad', 'País'];
        foreach ($headers as $col => $titulo) {
            $cell = chr(65 + $col) . '1';
            $sheet->setCellValue($cell, $titulo);
        }

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F497D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

        // ── Datos ──
        $row = 2;
        foreach ($logs as $log) {
            $ctrl   = $log->getControlador() ?? '';
            $modulo = '';
            $accion = '';
            if (preg_match('#\\\\(\w+Controller)::(\w+)$#', $ctrl, $m)) {
                $modulo = str_replace('Controller', '', $m[1]);
                $accion = $m[2];
            }

            $usuario = $log->getUsuario();

            $sheet->setCellValue('A' . $row, $log->getFechaRegistro() ? $log->getFechaRegistro()->format('d/m/Y H:i:s') : '');
            $sheet->setCellValue('B' . $row, $usuario ? $usuario->getNombre() . ' (' . $usuario->getUsername() . ')' : '');
            $sheet->setCellValue('C' . $row, $usuario && $usuario->getUsuarioTipo() ? $usuario->getUsuarioTipo()->getNombre() : '');
            $sheet->setCellValue('D' . $row, $log->getMetodo() ?? '');
            $sheet->setCellValue('E' . $row, $log->getRuta() ?? '');
            $sheet->setCellValue('F' . $row, $modulo);
            $sheet->setCellValue('G' . $row, $accion);
            $sheet->setCellValue('H' . $row, $log->getStatusCode() ?? '');
            $sheet->setCellValue('I' . $row, $log->getIp() ?? '');
            $sheet->setCellValue('J' . $row, $log->getCiudad() ?? '');
            $sheet->setCellValue('K' . $row, $log->getPais() ?? '');
            $row++;
        }

        // Autosize columnas
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'log_actividad_' . date('Ymd_His') . '.xlsx';

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    /**
     * Devuelve las acciones (métodos) registradas para un módulo dado.
     * Se llama vía AJAX cuando el usuario selecciona un módulo en el filtro.
     *
     * @Route("/acciones", name="user_activity_log_acciones", methods={"GET"})
     */
    public function acciones(Request $request, UserActivityLogRepository $logRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('view', 'Actividad_Usuarios');

        $modulo = trim($request->query->get('modulo', ''));
        if (!$modulo) {
            return new JsonResponse([]);
        }

        $acciones = $logRepository->getAccionesDistintos($modulo);

        return new JsonResponse($acciones);
    }

    /**
     * Recibe lat/lng desde el browser y actualiza el último log del usuario.
     *
     * @Route("/geo", name="user_activity_log_geo", methods={"POST"})
     */
    public function geo(Request $request, UserActivityLogRepository $logRepository, EntityManagerInterface $em): JsonResponse
    {
        $usuario = $this->getUser();
        if (!$usuario) {
            return new JsonResponse(['ok' => false], 401);
        }

        $data = json_decode($request->getContent(), true);
        $lat  = isset($data['lat'])  ? round((float) $data['lat'],  7) : null;
        $lng  = isset($data['lng'])  ? round((float) $data['lng'],  7) : null;
        $ciudad = isset($data['ciudad']) ? substr($data['ciudad'], 0, 100) : null;
        $pais   = isset($data['pais'])   ? substr($data['pais'],   0, 100) : null;

        if (!$lat || !$lng) {
            return new JsonResponse(['ok' => false, 'msg' => 'Coordenadas inválidas'], 400);
        }

        // Actualizar los últimos 5 logs del usuario que no tengan geo
        $logs = $logRepository->findUltimossinGeo($usuario, 5);
        foreach ($logs as $log) {
            $log->setLatitud((string) $lat);
            $log->setLongitud((string) $lng);
            $log->setCiudad($ciudad);
            $log->setPais($pais);
        }
        $em->flush();

        return new JsonResponse(['ok' => true]);
    }
}
