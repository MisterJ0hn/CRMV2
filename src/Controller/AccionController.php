<?php

namespace App\Controller;

use App\Entity\Accion;
use App\Form\AccionType;
use App\Repository\AccionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/accion")
 */
class AccionController extends AbstractController
{
    /**
     * @Route("/", name="accion_index", methods={"GET"})
     */
    public function index(AccionRepository $accionRepository): Response
    {
        return $this->render('accion/index.html.twig', [
            'accions' => $accionRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="accion_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $accion = new Accion();
        $form = $this->createForm(AccionType::class, $accion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($accion);
            $entityManager->flush();

            return $this->redirectToRoute('accion_index');
        }

        return $this->render('accion/new.html.twig', [
            'accion' => $accion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="accion_show", methods={"GET"})
     */
    public function show(Accion $accion): Response
    {
        return $this->render('accion/show.html.twig', [
            'accion' => $accion,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="accion_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Accion $accion): Response
    {
        $form = $this->createForm(AccionType::class, $accion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('accion_index');
        }

        return $this->render('accion/edit.html.twig', [
            'accion' => $accion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="accion_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Accion $accion): Response
    {
        if ($this->isCsrfTokenValid('delete'.$accion->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($accion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('accion_index');
    }
}
