<?php

namespace App\Controller;

use App\Repository\UserActivityLogRepository;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        Request $request,
        UserActivityLogRepository $logRepository,
        UsuarioRepository $usuarioRepository,
        PaginatorInterface $paginator
    ): Response {
       $filtros = [
            'desde'    => $request->query->get('desde', date('Y-m-d')),
            'hasta'    => $request->query->get('hasta', date('Y-m-d')),
            'usuario'  => $request->query->get('usuario'),
            'modulo'   => $request->query->get('modulo'),
        ];

         $query    = $logRepository->findByFiltros(
            $filtros['usuario'] ? (int) $filtros['usuario'] : null,
            $filtros['desde'],
            $filtros['hasta'],
            $filtros['modulo']
        );
       
        $usuarios = $usuarioRepository->findBy(['estado' => true, 'usuarioTipo'=>[1,2,3,4,5,6,7,8,10,11,12,13]], ['nombre' => 'ASC']);
        $modulos  = $logRepository->getModulosDistintos();

         $logs=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/,
            array('defaultSortFieldName' => 'fechaRegistro', 'defaultSortDirection' => 'desc'));
      
        return $this->render('user_activity_log/index.html.twig', [
            'logs'     => $logs,
            'usuarios' => $usuarios,
            'modulos'  => $modulos,
            'filtros'  => $filtros,
        ]);
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
