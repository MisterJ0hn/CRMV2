<?php

namespace App\Controller;

use App\Entity\CobranzaRespuesta;
use App\Form\CobranzaRespuestaType;
use App\Repository\CobranzaRespuestaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cobranza_respuesta")
 */
class CobranzaRespuestaController extends AbstractController
{
    /**
     * @Route("/", name="cobranza_respuesta_index", methods={"GET"})
     */
    public function index(CobranzaRespuestaRepository $cobranzaRespuestaRepository): Response
    {
        return $this->render('cobranza_respuesta/index.html.twig', [
            'cobranza_respuestas' => $cobranzaRespuestaRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="cobranza_respuesta_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $cobranzaRespuestum = new CobranzaRespuesta();
        $form = $this->createForm(CobranzaRespuestaType::class, $cobranzaRespuestum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($cobranzaRespuestum);
            $entityManager->flush();

            return $this->redirectToRoute('cobranza_respuesta_index');
        }

        return $this->render('cobranza_respuesta/new.html.twig', [
            'cobranza_respuestum' => $cobranzaRespuestum,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="cobranza_respuesta_show", methods={"GET"})
     */
    public function show(CobranzaRespuesta $cobranzaRespuestum): Response
    {
        return $this->render('cobranza_respuesta/show.html.twig', [
            'cobranza_respuestum' => $cobranzaRespuestum,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="cobranza_respuesta_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, CobranzaRespuesta $cobranzaRespuestum): Response
    {
        $form = $this->createForm(CobranzaRespuestaType::class, $cobranzaRespuestum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('cobranza_respuesta_index');
        }

        return $this->render('cobranza_respuesta/edit.html.twig', [
            'cobranza_respuestum' => $cobranzaRespuestum,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="cobranza_respuesta_delete", methods={"DELETE"})
     */
    public function delete(Request $request, CobranzaRespuesta $cobranzaRespuestum): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cobranzaRespuestum->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($cobranzaRespuestum);
            $entityManager->flush();
        }

        return $this->redirectToRoute('cobranza_respuesta_index');
    }
}
