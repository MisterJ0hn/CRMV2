<?php

namespace App\Controller;

use App\Entity\Lotes;
use App\Form\LotesType;
use App\Repository\LotesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/lotes")
 */
class LotesController extends AbstractController
{
    /**
     * @Route("/", name="lotes_index", methods={"GET"})
     */
    public function index(LotesRepository $lotesRepository): Response
    {
        return $this->render('lotes/index.html.twig', [
            'lotes' => $lotesRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="lotes_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $lote = new Lotes();
        $form = $this->createForm(LotesType::class, $lote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($lote);
            $entityManager->flush();

            return $this->redirectToRoute('lotes_index');
        }

        return $this->render('lotes/new.html.twig', [
            'lote' => $lote,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="lotes_show", methods={"GET"})
     */
    public function show(Lotes $lote): Response
    {
        return $this->render('lotes/show.html.twig', [
            'lote' => $lote,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="lotes_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Lotes $lote): Response
    {
        $form = $this->createForm(LotesType::class, $lote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('lotes_index');
        }

        return $this->render('lotes/edit.html.twig', [
            'lote' => $lote,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="lotes_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Lotes $lote): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lote->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($lote);
            $entityManager->flush();
        }

        return $this->redirectToRoute('lotes_index');
    }
}
