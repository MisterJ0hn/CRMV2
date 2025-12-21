<?php

namespace App\Controller;

use App\Entity\ContratoRol;
use App\Form\ContratoRolType;
use App\Repository\ContratoRolRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contrato/rol")
 */
class ContratoRolController extends AbstractController
{
    /**
     * @Route("/", name="contrato_rol_index", methods={"GET"})
     */
    public function index(ContratoRolRepository $contratoRolRepository): Response
    {
        return $this->render('contrato_rol/index.html.twig', [
            'contrato_rols' => $contratoRolRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="contrato_rol_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $contratoRol = new ContratoRol();
        $form = $this->createForm(ContratoRolType::class, $contratoRol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contratoRol);
            $entityManager->flush();

            return $this->redirectToRoute('contrato_rol_index');
        }

        return $this->render('contrato_rol/new.html.twig', [
            'contrato_rol' => $contratoRol,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="contrato_rol_show", methods={"GET"})
     */
    public function show(ContratoRol $contratoRol): Response
    {
        return $this->render('contrato_rol/show.html.twig', [
            'contrato_rol' => $contratoRol,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="contrato_rol_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ContratoRol $contratoRol): Response
    {
        $form = $this->createForm(ContratoRolType::class, $contratoRol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('contrato_rol_index');
        }

        return $this->render('contrato_rol/edit.html.twig', [
            'contrato_rol' => $contratoRol,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="contrato_rol_delete", methods={"DELETE"})
     */
    public function delete(Request $request, ContratoRol $contratoRol): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contratoRol->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($contratoRol);
            $entityManager->flush();
        }

        return $this->redirectToRoute('contrato_rol_index');
    }
}
