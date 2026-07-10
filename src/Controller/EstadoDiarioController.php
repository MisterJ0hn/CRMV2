<?php

namespace App\Controller;

use App\Entity\EstadoDiarioOrigen;
use App\Form\EstadoDiarioOrigenType;
use App\Repository\EstadoDiarioOrigenRepository;
use App\Repository\EstadoDiarioRepository;
use App\Repository\ModuloPerRepository;
use App\Service\EstadoDiarioImportService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
