<?php

namespace App\Controller;

use App\Entity\EstadoDiario;
use App\Entity\EstadoDiarioOrigen;
use App\Form\EstadoDiarioOrigenType;
use App\Repository\EstadoDiarioOrigenRepository;
use App\Repository\EstadoDiarioRepository;
use App\Repository\JurisdiccionRepository;
use App\Repository\ModuloPerRepository;
use App\Service\EstadoDiarioImportService;
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
            ['defaultSortFieldName' => 'fechaCarga', 'defaultSortDirection' => 'desc']
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

                if (!$datosNombre['guid']) {
                    return $this->render('estado_diario/new.html.twig', [
                        'form' => $form->createView(),
                        'pagina' => $pagina->getNombre(),
                        'error' => 'El nombre del archivo no corresponde al formato esperado: EstadoDiario{RUT}_{DD}_{MM}_{AAAA}-{guid}.xlsx',
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
            $leido = ($tab === 'leidos');

            $jurisdiccion = $request->query->get('bJurisdiccion') ?: null;
            $fecha = $request->query->get('bFecha') ?: date('Y-m-d', strtotime('-1 day'));
            $rut = $request->query->get('bRut') ?: null;

            $query = $estadoDiarioRepository->findConFiltro(
                $jurisdiccion ? (int) $jurisdiccion : null,
                $fecha,
                $rut,
                $leido
            );

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
