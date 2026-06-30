<?php

namespace App\Controller;

use App\Entity\Colaborador;
use App\Repository\ColaboradorRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\UsuarioRepository;
use App\Repository\UsuarioTipoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mantenedor_colaboradores")
 */
class MantenedorColaboradoresController extends AbstractController
{
    /**
     * @Route("/", name="mantenedor_colaboradores_index", methods={"GET"})
     */
    public function index(
        ColaboradorRepository $colaboradorRepository,
        UsuarioTipoRepository $usuarioTipoRepository,
        ModuloPerRepository $moduloPerRepository
    ): Response {
        $this->denyAccessUnlessGranted('view', 'mantenedor_colaboradores');
        $user = $this->getUser();
        $pagina = $moduloPerRepository->findOneByName('mantenedor_colaboradores', $user->getEmpresaActual());

        
        return $this->render('mantenedor_colaboradores/index.html.twig', [
            'colaboradores' => $colaboradorRepository->findAll(),
            'perfiles'      => $usuarioTipoRepository->findBy(['id' => [1,3,4,5,6,7,10,11,12,13]], ['nombre' => 'ASC']),
            'pagina'        => $pagina->getNombre(),
        ]);
    }

    /**
     * @Route("/usuarios_por_perfil/{id}", name="mantenedor_colaboradores_usuarios_por_perfil", methods={"GET"})
     */
    public function usuariosPorPerfil(int $id, UsuarioRepository $usuarioRepository): JsonResponse
    {
        $usuarios = $usuarioRepository->findBy(
            ['usuarioTipo' => $id, 'estado' => true],
            ['nombre' => 'ASC']
        );

        $data = array_map(fn($u) => ['id' => $u->getId(), 'nombre' => $u->getNombre()], $usuarios);

        return $this->json($data);
    }

    /**
     * @Route("/agregar", name="mantenedor_colaboradores_agregar", methods={"POST"})
     */
    public function agregar(Request $request, UsuarioRepository $usuarioRepository, ColaboradorRepository $colaboradorRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('create', 'mantenedor_colaboradores');

        $usuarioId = $request->request->get('usuario_id');
        if (!$usuarioId) {
            return $this->json(['error' => 'Debe seleccionar un usuario.'], 400);
        }

        $usuario = $usuarioRepository->find($usuarioId);
        if (!$usuario) {
            return $this->json(['error' => 'Usuario no encontrado.'], 404);
        }

        $existe = $colaboradorRepository->findOneBy(['usuario' => $usuario]);
        if ($existe) {
            return $this->json(['error' => 'El usuario ya es colaborador.'], 409);
        }

        $colaborador = new Colaborador();
        $colaborador->setUsuario($usuario);

        $em = $this->getDoctrine()->getManager();
        $em->persist($colaborador);
        $em->flush();

        return $this->json([
            'id'     => $colaborador->getId(),
            'nombre' => $usuario->getNombre(),
            'perfil' => $usuario->getUsuarioTipo()->getNombre(),
        ]);
    }

    /**
     * @Route("/{id}", name="mantenedor_colaboradores_eliminar", methods={"DELETE"})
     */
    public function eliminar(int $id, ColaboradorRepository $colaboradorRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('edit', 'mantenedor_colaboradores');

        $colaborador = $colaboradorRepository->find($id);
        if (!$colaborador) {
            return $this->json(['error' => 'Colaborador no encontrado.'], 404);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($colaborador);
        $em->flush();

        return $this->json(['success' => true]);
    }
}
