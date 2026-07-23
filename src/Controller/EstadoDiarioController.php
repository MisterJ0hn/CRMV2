<?php

namespace App\Controller;

use App\Entity\EstadoDiario;
use App\Entity\EstadoDiarioAgenda;
use App\Entity\EstadoDiarioOrigen;
use App\Form\EstadoDiarioOrigenType;
use App\Repository\EstadoDiarioOrigenRepository;
use App\Repository\EstadoDiarioRepository;
use App\Repository\JurisdiccionRepository;
use App\Repository\ModuloPerRepository;
use App\Service\EstadoDiarioImportService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/estado_diario")
 */
class EstadoDiarioController extends AbstractController
{
    /**
     * @Route("/", name="estado_diario_index", methods={"GET"})
     */
    public function index(Request $request, EstadoDiarioOrigenRepository $estadoDiarioOrigenRepository, ModuloPerRepository $moduloPerRepository, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('view', 'estado_diario');
        $user = $this->getUser();
        $pagina = $moduloPerRepository->findOneByName('estado_diario', $user->getEmpresaActual());

        $query = $estadoDiarioOrigenRepository->findBy([]);
        $origenes = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20,
            ['defaultSortFieldName' => 'fecha', 'defaultSortDirection' => 'desc']
        );

        return $this->render('estado_diario/index.html.twig', [
            'origenes' => $origenes,
            'pagina' => $pagina->getNombre(),
        ]);
    }

    /**
     * @Route("/new", name="estado_diario_new", methods={"GET","POST"})
     */
    public function new(Request $request, ModuloPerRepository $moduloPerRepository, EstadoDiarioImportService $importService): Response
    {
        $this->denyAccessUnlessGranted('create', 'estado_diario');
        $user = $this->getUser();
        $pagina = $moduloPerRepository->findOneByName('estado_diario', $user->getEmpresaActual());

        $origen = new EstadoDiarioOrigen();
        $form = $this->createForm(EstadoDiarioOrigenType::class, $origen);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $archivo */
            $archivo = $form->get('url')->getData();

            if ($archivo) {
                $nombreOriginalSinExtension = pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME);
                $datosNombre = $importService->parseNombreArchivo($nombreOriginalSinExtension);

                if (!$datosNombre['rut'] || !$datosNombre['fecha']) {
                    return $this->render('estado_diario/new.html.twig', [
                        'form' => $form->createView(),
                        'pagina' => $pagina->getNombre(),
                        'error' => 'El nombre del archivo no corresponde al formato esperado: EstadoDiario{RUT}_{DD}_{MM}_{AAAA}.xlsx (el sufijo -{guid} es opcional).',
                    ]);
                }

                $nuevoNombre = $nombreOriginalSinExtension . '-' . uniqid() . '.' . $archivo->guessExtension();

                try {
                    $archivo->move($this->getParameter('estado_diario_importacion'), $nuevoNombre);

                    $origen->setNombreArchivo($nombreOriginalSinExtension);
                    $origen->setUrl($this->getParameter('estado_diario_importacion') . $nuevoNombre);
                    $origen->setRut($datosNombre['rut']);
                    $origen->setFecha($datosNombre['fecha']);
                    $origen->setGuid($datosNombre['guid']);
                    $origen->setUsuarioCarga($user);
                    $origen->setFechaCarga(new \DateTime());

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($origen);
                    $entityManager->flush();

                    $importService->importar($origen->getUrl(), $origen);
                } catch (FileException $e) {
                    return $this->render('estado_diario/new.html.twig', [
                        'form' => $form->createView(),
                        'pagina' => $pagina->getNombre(),
                        'error' => 'Ocurrió un error al cargar el archivo. Favor intente nuevamente.',
                    ]);
                }
            }

            return $this->redirectToRoute('estado_diario_show', ['id' => $origen->getId()]);
        }

        return $this->render('estado_diario/new.html.twig', [
            'form' => $form->createView(),
            'pagina' => $pagina->getNombre(),
        ]);
    }

    /**
     * @Route("/movimientos", name="estado_diario_movimientos", methods={"GET"})
     */
    public function movimientos(Request $request, ModuloPerRepository $moduloPerRepository, JurisdiccionRepository $jurisdiccionRepository): Response
    {
        $this->denyAccessUnlessGranted('view', 'estado_diario');
        $user = $this->getUser();
        $pagina = $moduloPerRepository->findOneByName('estado_diario', $user->getEmpresaActual());

        return $this->render('estado_diario/movimientos.html.twig', [
            'pagina' => $pagina->getNombre(),
            'jurisdicciones' => $jurisdiccionRepository->findBy([], ['nombre' => 'ASC']),
            'tabInicial' => $request->query->get('tab', 'no-leidos'),
            'fechaDefault' => date('Y-m-d', strtotime('-1 day')),
        ]);
    }

    /**
     * @Route("/movimientos/obtenerContenido", name="estado_diario_movimientos_obtener_contenido", methods={"GET"})
     */
    public function obtenerContenidoMovimientos(Request $request, EstadoDiarioRepository $estadoDiarioRepository, PaginatorInterface $paginator): JsonResponse
    {
        $this->denyAccessUnlessGranted('view', 'estado_diario');

        try {
            $tab = $request->query->get('tab', 'no-leidos');

            $jurisdiccion = $request->query->get('bJurisdiccion') ?: null;
            $fecha = $request->query->get('bFecha') ?: date('Y-m-d', strtotime('-1 day'));
            $rut = $request->query->get('bRut') ?: null;
            $jurisdiccionId = $jurisdiccion ? (int) $jurisdiccion : null;

            $query = $estadoDiarioRepository->findConFiltro($jurisdiccionId, $fecha, $rut, $tab);

            $movimientos = $paginator->paginate(
                $query,
                $request->query->getInt('page', 1),
                30
            );

            $html = $this->renderView('estado_diario/_tablaMovimientos.html.twig', [
                'movimientos' => $movimientos,
                'tab' => $tab,
            ]);

            return new JsonResponse([
                'html' => $html,
                'total' => $movimientos->getTotalItemCount(),
                'totalNoLeidos' => $estadoDiarioRepository->contarPorFiltro($jurisdiccionId, $fecha, $rut, 'no-leidos'),
                'totalPendiente' => $estadoDiarioRepository->contarPorFiltro($jurisdiccionId, $fecha, $rut, 'pendiente'),
                'totalResuelto' => $estadoDiarioRepository->contarPorFiltro($jurisdiccionId, $fecha, $rut, 'resuelto'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()]);
        }
    }

    /**
     * @Route("/movimientos/{id}/leido", name="estado_diario_movimientos_leido", methods={"POST"})
     */
    public function marcarLeido(Request $request, EstadoDiario $estadoDiario): JsonResponse
    {
        $this->denyAccessUnlessGranted('view', 'estado_diario');

        if (!$this->isCsrfTokenValid('estado_diario_leido', $request->request->get('_token'))) {
            return new JsonResponse(['exito' => false, 'mensaje' => 'Token inválido'], 400);
        }

        $estadoDiario->setLeido(true);
        $estadoDiario->setFechaLeido(new \DateTime());
        $estadoDiario->setUsuarioLeido($this->getUser());

        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(['exito' => true]);
    }

    /**
     * @Route("/movimientos/{id}/pendiente", name="estado_diario_movimientos_pendiente", methods={"POST"})
     */
    public function marcarPendiente(Request $request, EstadoDiario $estadoDiario, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('view', 'estado_diario');

        if (!$this->isCsrfTokenValid('estado_diario_pendiente', $request->request->get('_token'))) {
            return new JsonResponse(['exito' => false, 'mensaje' => 'Token inválido'], 400);
        }

        $nivel = $request->request->get('nivel');

        if (!in_array($nivel, ['bajo', 'medio', 'alto'], true)) {
            return new JsonResponse(['exito' => false, 'mensaje' => 'Nivel inválido'], 400);
        }

        $estadoDiario->setPendiente(true);
        $estadoDiario->setNivelPendiente($nivel);
        $estadoDiario->setFechaPendiente(new \DateTime());
        $estadoDiario->setUsuarioPendiente($this->getUser());

        $recordatorioDetalle = trim((string) $request->request->get('recordatorio_detalle'));
        $recordatorioFechaHora = $request->request->get('recordatorio_fecha_hora');

        if ($recordatorioDetalle !== '' && $recordatorioFechaHora) {
            $fechaHora = \DateTime::createFromFormat('Y-m-d\TH:i', $recordatorioFechaHora) ?: new \DateTime($recordatorioFechaHora);

            $agenda = new EstadoDiarioAgenda();
            $agenda->setEstadoDiario($estadoDiario);
            $agenda->setDetalle($recordatorioDetalle);
            $agenda->setFechaHora($fechaHora);
            $agenda->setUsuarioRegistro($this->getUser());
            $agenda->setFechaHoraRegistro(new \DateTime());

            $em->persist($agenda);
        }

        $em->flush();

        return new JsonResponse(['exito' => true]);
    }

    /**
     * @Route("/{id}", name="estado_diario_show", methods={"GET"})
     */
    public function show(Request $request, EstadoDiarioOrigen $estadoDiarioOrigen, EstadoDiarioRepository $estadoDiarioRepository, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('view', 'estado_diario');

        $query = $estadoDiarioRepository->findBy(['estadoDiarioOrigen' => $estadoDiarioOrigen]);
        $detalles = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            30,
            ['defaultSortFieldName' => 'id', 'defaultSortDirection' => 'asc']
        );

        return $this->render('estado_diario/show.html.twig', [
            'estadoDiarioOrigen' => $estadoDiarioOrigen,
            'detalles' => $detalles,
        ]);
    }
}
