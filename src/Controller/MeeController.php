<?php

namespace App\Controller;

use App\Entity\EstrategiaJuridica;
use App\Entity\MateriaEstrategia;
use App\Entity\Mee;
use App\Form\MeeType;
use App\Repository\MeeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mee")
 */
class MeeController extends AbstractController
{
    /**
     * @Route("/", name="mee_index", methods={"GET"})
     */
    public function index(MeeRepository $meeRepository): Response
    {
        return $this->render('mee/index.html.twig', [
            'mees' => $meeRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}/new", name="mee_new", methods={"GET","POST"})
     */
    public function new(Request $request,MateriaEstrategia $materiaEstrategia, MeeRepository $meeRepository): Response
    {

        $mee = new Mee();
        $mee->setMateriaEstrategia($materiaEstrategia);
        $form = $this->createForm(MeeType::class, $mee);
        $form->handleRequest($request);
        $error_toast="";
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($mee);
            $entityManager->flush();

            $error_toast="Toast.fire({
                icon: 'success',
                title: 'Registro grabado con exito'
            })";
            //return $this->redirectToRoute('mee_index');
        }

        return $this->render('mee/new.html.twig', [
            'mee' => $mee,
            'form' => $form->createView(),
            'error_toast'=>$error_toast,
            'mees' => $meeRepository->findBy(['materiaEstrategia'=>$materiaEstrategia->getId()]),
        ]);
    }

    /**
     * @Route("/{id}", name="mee_show", methods={"GET"})
     */
    public function show(Mee $mee): Response
    {
        return $this->render('mee/show.html.twig', [
            'mee' => $mee,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="mee_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Mee $mee): Response
    {
        $form = $this->createForm(MeeType::class, $mee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('mee_index');
        }

        return $this->render('mee/edit.html.twig', [
            'mee' => $mee,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/list", name="mee_list", methods={"GET","POST"})
     */
    public function combo(Mee $mee): Response
    {
        return $this->render('mee/list.html.twig', [
            'mee' => $mee,
            
        ]);
    }
    /**
     * @Route("/{id}", name="mee_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Mee $mee): Response
    {
        if ($this->isCsrfTokenValid('delete'.$mee->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($mee);
            $entityManager->flush();
        }

        return $this->redirectToRoute('mee_index');
    }
}
