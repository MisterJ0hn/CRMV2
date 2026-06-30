<?php

namespace App\Controller;

use App\Entity\Escritura;
use App\Entity\EstrategiaJuridica;
use App\Entity\MateriaEstrategia;
use App\Form\EscrituraType;
use App\Repository\EscrituraRepository;
use App\Repository\MeeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/escritura")
 */
class EscrituraController extends AbstractController
{
    /**
     * @Route("/", name="escritura_index", methods={"GET"})
     */
    public function index(EscrituraRepository $escrituraRepository): Response
    {
        return $this->render('escritura/index.html.twig', [
            'escrituras' => $escrituraRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="escritura_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $escritura = new Escritura();
        $form = $this->createForm(EscrituraType::class, $escritura);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($escritura);
            $entityManager->flush();

            return $this->redirectToRoute('escritura_index');
        }

        return $this->render('escritura/new.html.twig', [
            'escritura' => $escritura,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="escritura_show", methods={"GET"})
     */
    public function show(Escritura $escritura): Response
    {
        return $this->render('escritura/show.html.twig', [
            'escritura' => $escritura,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="escritura_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Escritura $escritura): Response
    {
        $form = $this->createForm(EscrituraType::class, $escritura);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('escritura_index');
        }

        return $this->render('escritura/edit.html.twig', [
            'escritura' => $escritura,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/combo", name="escritura_combo", methods={"GET","POST"})
     */
    public function combo(MateriaEstrategia $materiaEstrategia, MeeRepository $meeRepository): Response
    {
        return $this->render('escritura/combo.html.twig', [
            'mees' => $meeRepository->findBy(['materiaEstrategia'=>$materiaEstrategia->getId()]) 
            
        ]);
    }


    /**
     * @Route("/{id}", name="escritura_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Escritura $escritura): Response
    {
        if ($this->isCsrfTokenValid('delete'.$escritura->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($escritura);
            $entityManager->flush();
        }

        return $this->redirectToRoute('escritura_index');
    }
}
