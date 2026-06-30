<?php

namespace App\Controller;

use App\Entity\CobranzaFuncion;
use App\Form\CobranzaFuncionType;
use App\Repository\CobranzaFuncionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cobranza_funcion")
 */
class CobranzaFuncionController extends AbstractController
{
    /**
     * @Route("/", name="cobranza_funcion_index", methods={"GET"})
     */
    public function index(CobranzaFuncionRepository $cobranzaFuncionRepository): Response
    {
        return $this->render('cobranza_funcion/index.html.twig', [
            'cobranza_funcions' => $cobranzaFuncionRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="cobranza_funcion_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $cobranzaFuncion = new CobranzaFuncion();
        $form = $this->createForm(CobranzaFuncionType::class, $cobranzaFuncion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($cobranzaFuncion);
            $entityManager->flush();

            return $this->redirectToRoute('cobranza_funcion_index');
        }

        return $this->render('cobranza_funcion/new.html.twig', [
            'cobranza_funcion' => $cobranzaFuncion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="cobranza_funcion_show", methods={"GET"})
     */
    public function show(CobranzaFuncion $cobranzaFuncion): Response
    {
        return $this->render('cobranza_funcion/show.html.twig', [
            'cobranza_funcion' => $cobranzaFuncion,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="cobranza_funcion_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, CobranzaFuncion $cobranzaFuncion): Response
    {
        $form = $this->createForm(CobranzaFuncionType::class, $cobranzaFuncion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('cobranza_funcion_index');
        }

        return $this->render('cobranza_funcion/edit.html.twig', [
            'cobranza_funcion' => $cobranzaFuncion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="cobranza_funcion_delete", methods={"DELETE"})
     */
    public function delete(Request $request, CobranzaFuncion $cobranzaFuncion): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cobranzaFuncion->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($cobranzaFuncion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('cobranza_funcion_index');
    }
}
