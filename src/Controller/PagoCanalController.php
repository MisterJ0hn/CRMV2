<?php

namespace App\Controller;

use App\Entity\PagoCanal;
use App\Form\PagoCanalType;
use App\Repository\PagoCanalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/pago_canal")
 */
class PagoCanalController extends AbstractController
{
    /**
     * @Route("/", name="pago_canal_index", methods={"GET"})
     */
    public function index(PagoCanalRepository $pagoCanalRepository): Response
    {
        return $this->render('pago_canal/index.html.twig', [
            'pago_canals' => $pagoCanalRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="pago_canal_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $pagoCanal = new PagoCanal();
        $form = $this->createForm(PagoCanalType::class, $pagoCanal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($pagoCanal);
            $entityManager->flush();

            return $this->redirectToRoute('pago_canal_index');
        }

        return $this->render('pago_canal/new.html.twig', [
            'pago_canal' => $pagoCanal,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="pago_canal_show", methods={"GET"})
     */
    public function show(PagoCanal $pagoCanal): Response
    {
        return $this->render('pago_canal/show.html.twig', [
            'pago_canal' => $pagoCanal,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="pago_canal_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, PagoCanal $pagoCanal): Response
    {
        $form = $this->createForm(PagoCanalType::class, $pagoCanal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('pago_canal_index');
        }

        return $this->render('pago_canal/edit.html.twig', [
            'pago_canal' => $pagoCanal,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="pago_canal_delete", methods={"DELETE"})
     */
    public function delete(Request $request, PagoCanal $pagoCanal): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pagoCanal->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($pagoCanal);
            $entityManager->flush();
        }

        return $this->redirectToRoute('pago_canal_index');
    }
}
