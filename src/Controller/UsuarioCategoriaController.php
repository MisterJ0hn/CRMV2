<?php

namespace App\Controller;

use App\Entity\UsuarioCategoria;
use App\Form\UsuarioCategoriaType;
use App\Repository\EmpresaRepository;
use App\Repository\UsuarioCategoriaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/usuario_categoria")
 */
class UsuarioCategoriaController extends AbstractController
{
    /**
     * @Route("/", name="usuario_categoria_index", methods={"GET"})
     */
    public function index(UsuarioCategoriaRepository $usuarioCategoriaRepository): Response
    {
        $user=$this->getUser();
        return $this->render('usuario_categoria/index.html.twig', [
            'usuario_categorias' => $usuarioCategoriaRepository->findBy(['empresa'=>$user->getEmpresaActual()]),
        ]);
    }

    /**
     * @Route("/new", name="usuario_categoria_new", methods={"GET","POST"})
     */
    public function new(Request $request,EmpresaRepository $empresaRepository): Response
    {
        $user=$this->getUser();
        $usuarioCategorium = new UsuarioCategoria();
        $usuarioCategorium->setEmpresa($empresaRepository->find($user->getEmpresaActual()));
        $form = $this->createForm(UsuarioCategoriaType::class, $usuarioCategorium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($usuarioCategorium);
            $entityManager->flush();

            return $this->redirectToRoute('usuario_categoria_index');
        }

        return $this->render('usuario_categoria/new.html.twig', [
            'usuario_categorium' => $usuarioCategorium,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="usuario_categoria_show", methods={"GET"})
     */
    public function show(UsuarioCategoria $usuarioCategorium): Response
    {
        return $this->render('usuario_categoria/show.html.twig', [
            'usuario_categorium' => $usuarioCategorium,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="usuario_categoria_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, UsuarioCategoria $usuarioCategorium): Response
    {
        $form = $this->createForm(UsuarioCategoriaType::class, $usuarioCategorium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('usuario_categoria_index');
        }

        return $this->render('usuario_categoria/edit.html.twig', [
            'usuario_categorium' => $usuarioCategorium,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="usuario_categoria_delete", methods={"DELETE"})
     */
    public function delete(Request $request, UsuarioCategoria $usuarioCategorium): Response
    {
        if ($this->isCsrfTokenValid('delete'.$usuarioCategorium->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($usuarioCategorium);
            $entityManager->flush();
        }

        return $this->redirectToRoute('usuario_categoria_index');
    }
}
