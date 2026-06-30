<?php

namespace App\Controller;

use App\Entity\UsuarioStatus;
use App\Form\UsuarioStatusType;
use App\Repository\UsuarioStatusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/usuario_status")
 */
class UsuarioStatusController extends AbstractController
{
    /**
     * @Route("/", name="usuario_status_index", methods={"GET"})
     */
    public function index(UsuarioStatusRepository $usuarioStatusRepository): Response
    {
        return $this->render('usuario_status/index.html.twig', [
            'usuario_statuses' => $usuarioStatusRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="usuario_status_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $usuarioStatus = new UsuarioStatus();
        $form = $this->createForm(UsuarioStatusType::class, $usuarioStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($usuarioStatus);
            $entityManager->flush();

            return $this->redirectToRoute('usuario_status_index');
        }

        return $this->render('usuario_status/new.html.twig', [
            'usuario_status' => $usuarioStatus,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="usuario_status_show", methods={"GET"})
     */
    public function show(UsuarioStatus $usuarioStatus): Response
    {
        return $this->render('usuario_status/show.html.twig', [
            'usuario_status' => $usuarioStatus,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="usuario_status_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, UsuarioStatus $usuarioStatus): Response
    {
        $form = $this->createForm(UsuarioStatusType::class, $usuarioStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('usuario_status_index');
        }

        return $this->render('usuario_status/edit.html.twig', [
            'usuario_status' => $usuarioStatus,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="usuario_status_delete", methods={"DELETE"})
     */
    public function delete(Request $request, UsuarioStatus $usuarioStatus): Response
    {
        if ($this->isCsrfTokenValid('delete'.$usuarioStatus->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($usuarioStatus);
            $entityManager->flush();
        }

        return $this->redirectToRoute('usuario_status_index');
    }
}
