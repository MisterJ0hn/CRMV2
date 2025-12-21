<?php

namespace App\Controller;

use App\Entity\Sucursal;
use App\Entity\Cuenta;
use App\Form\SucursalType;
use App\Repository\SucursalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sucursal")
 */
class SucursalController extends AbstractController
{
    /**
     * @Route("/{id}", name="sucursal_index", methods={"GET"})
     */
    public function index(Cuenta $cuenta,SucursalRepository $sucursalRepository): Response
    {
        return $this->render('sucursal/index.html.twig', [
            'sucursals' => $sucursalRepository->findBy(['cuenta'=>$cuenta->getId()]),
            'cuentum'=>$cuenta,
        ]);
    }

    /**
     * @Route("/{id}/new", name="sucursal_new", methods={"GET","POST"})
     */
    public function new(Cuenta $cuenta,Request $request): Response
    {
        $sucursal = new Sucursal();
        $sucursal->setCuenta($cuenta);
        $form = $this->createForm(SucursalType::class, $sucursal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($sucursal);
            $entityManager->flush();

            return $this->redirectToRoute('sucursal_index',['id'=>$cuenta->getId()]);
        }

        return $this->render('sucursal/new.html.twig', [
            'sucursal' => $sucursal,
            'form' => $form->createView(),
            'cuentum'=>$cuenta,
        ]);
    }



    /**
     * @Route("/{id}/edit", name="sucursal_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Sucursal $sucursal): Response
    {
        $form = $this->createForm(SucursalType::class, $sucursal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('sucursal_index',['id'=>$sucursal->getCuenta()->getId()]);
        }

        return $this->render('sucursal/edit.html.twig', [
            'sucursal' => $sucursal,
            'form' => $form->createView(),
            'cuentum'=>$sucursal->getCuenta(),

        ]);
    }

    /**
     * @Route("/{id}", name="sucursal_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Sucursal $sucursal): Response
    {

        $cuenta=$sucursal->getCuenta();
        if ($this->isCsrfTokenValid('delete'.$sucursal->getId(), $request->request->get('_token'))) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($sucursal);
            $entityManager->flush();
        }

        return $this->redirectToRoute('sucursal_index',['id'=>$cuenta->getId()]);
    }
}
