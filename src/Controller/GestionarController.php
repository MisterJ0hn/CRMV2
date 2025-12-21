<?php

namespace App\Controller;

use App\Entity\Gestionar;
use App\Form\GestionarType;
use App\Repository\GestionarRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/gestionar")
 */
class GestionarController extends AbstractController
{
    /**
     * @Route("/", name="gestionar_index", methods={"GET"})
     */
    public function index(GestionarRepository $gestionarRepository): Response
    {
        return $this->render('gestionar/index.html.twig', [
            'gestionars' => $gestionarRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="gestionar_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $gestionar = new Gestionar();
        $form = $this->createForm(GestionarType::class, $gestionar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($gestionar);
            $entityManager->flush();

            return $this->redirectToRoute('gestionar_index');
        }

        return $this->render('gestionar/new.html.twig', [
            'gestionar' => $gestionar,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gestionar_show", methods={"GET"})
     */
    public function show(Gestionar $gestionar): Response
    {
        return $this->render('gestionar/show.html.twig', [
            'gestionar' => $gestionar,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="gestionar_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Gestionar $gestionar): Response
    {
        $form = $this->createForm(GestionarType::class, $gestionar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('gestionar_index');
        }

        return $this->render('gestionar/edit.html.twig', [
            'gestionar' => $gestionar,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gestionar_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Gestionar $gestionar): Response
    {
        if ($this->isCsrfTokenValid('delete'.$gestionar->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($gestionar);
            $entityManager->flush();
        }

        return $this->redirectToRoute('gestionar_index');
    }
}
