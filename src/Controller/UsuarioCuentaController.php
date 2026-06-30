<?php

namespace App\Controller;

use App\Entity\UsuarioCuenta;
use App\Entity\Usuario;
use App\Form\UsuarioCuentaType;
use App\Repository\UsuarioCuentaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/usuario_cuenta")
 */
class UsuarioCuentaController extends AbstractController
{
    /**
     * @Route("/", name="usuario_cuenta_index", methods={"GET"})
     */
    public function index(UsuarioCuentaRepository $usuarioCuentaRepository): Response
    {
        $this->denyAccessUnlessGranted('view','usuario_cuenta');
        return $this->render('usuario_cuenta/index.html.twig', [
            'usuario_cuentas' => $usuarioCuentaRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="usuario_cuenta_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('create','usuario_cuenta');
        $usuarioCuentum = new UsuarioCuenta();
        $form = $this->createForm(UsuarioCuentaType::class, $usuarioCuentum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($usuarioCuentum);
            $entityManager->flush();

            $usuario=$this->getDoctrine()->getRepository(Usuario::class)->find($usuarioCuentum->getUsuario()->getId());
            $usuario->setEmpresaActual($usuarioCuentum->getCuenta()->getEmpresa()->getId());
            $entityManager->persist($usuario);
            $entityManager->flush();

            return $this->redirectToRoute('usuario_cuenta_index');
        }

        return $this->render('usuario_cuenta/new.html.twig', [
            'usuario_cuentum' => $usuarioCuentum,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="usuario_cuenta_show", methods={"GET"})
     */
    public function show(UsuarioCuenta $usuarioCuentum): Response
    {
        $this->denyAccessUnlessGranted('view','usuario_cuenta');
        return $this->render('usuario_cuenta/show.html.twig', [
            'usuario_cuentum' => $usuarioCuentum,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="usuario_cuenta_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, UsuarioCuenta $usuarioCuentum): Response
    {
        $this->denyAccessUnlessGranted('edit','usuario_cuenta');
        $form = $this->createForm(UsuarioCuentaType::class, $usuarioCuentum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $entityManager = $this->getDoctrine()->getManager();
            $usuario=$this->getDoctrine()->getRepository(Usuario::class)->find($usuarioCuentum->getUsuario()->getId());
            $usuario->setEmpresaActual($usuarioCuentum->getCuenta()->getEmpresa()->getId());
            $entityManager->persist($usuario);
            $entityManager->flush();

            return $this->redirectToRoute('usuario_cuenta_index');
        }

        return $this->render('usuario_cuenta/edit.html.twig', [
            'usuario_cuentum' => $usuarioCuentum,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="usuario_cuenta_delete", methods={"DELETE"})
     */
    public function delete(Request $request, UsuarioCuenta $usuarioCuentum): Response
    {
        $this->denyAccessUnlessGranted('full','usuario_cuenta');
        if ($this->isCsrfTokenValid('delete'.$usuarioCuentum->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($usuarioCuentum);
            $entityManager->flush();
        }

        return $this->redirectToRoute('usuario_cuenta_index');
    }
}
