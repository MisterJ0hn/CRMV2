<?php

namespace App\Controller;

use App\Entity\PagoTipo;
use App\Form\PagoTipoType;
use App\Repository\PagoTipoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/pago_tipo")
 */
class PagoTipoController extends AbstractController
{
    /**
     * @Route("/", name="pago_tipo_index", methods={"GET"})
     */
    public function index(PagoTipoRepository $pagoTipoRepository): Response
    {
        return $this->render('pago_tipo/index.html.twig', [
            'pago_tipos' => $pagoTipoRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="pago_tipo_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $pagoTipo = new PagoTipo();
        $form = $this->createForm(PagoTipoType::class, $pagoTipo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($pagoTipo);
            $entityManager->flush();

            return $this->redirectToRoute('pago_tipo_index');
        }

        return $this->render('pago_tipo/new.html.twig', [
            'pago_tipo' => $pagoTipo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="pago_tipo_show", methods={"GET"})
     */
    public function show(PagoTipo $pagoTipo): Response
    {
        return $this->render('pago_tipo/show.html.twig', [
            'pago_tipo' => $pagoTipo,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="pago_tipo_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, PagoTipo $pagoTipo): Response
    {
        $form = $this->createForm(PagoTipoType::class, $pagoTipo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('pago_tipo_index');
        }

        return $this->render('pago_tipo/edit.html.twig', [
            'pago_tipo' => $pagoTipo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="pago_tipo_delete", methods={"DELETE"})
     */
    public function delete(Request $request, PagoTipo $pagoTipo): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pagoTipo->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($pagoTipo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('pago_tipo_index');
    }
}
