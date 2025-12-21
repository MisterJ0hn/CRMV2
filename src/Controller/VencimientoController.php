<?php

namespace App\Controller;

use App\Entity\Vencimiento;
use App\Form\VencimientoType;
use App\Repository\VencimientoRepository;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/vencimiento")
 */
class VencimientoController extends AbstractController
{
    /**
     * @Route("/", name="vencimiento_index", methods={"GET"})
     */
    public function index(VencimientoRepository $vencimientoRepository): Response
    {
        return $this->render('vencimiento/index.html.twig', [
            'vencimientos' => $vencimientoRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="vencimiento_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $vencimiento = new Vencimiento();
        $form = $this->createForm(VencimientoType::class, $vencimiento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($vencimiento);
            $entityManager->flush();

            return $this->redirectToRoute('vencimiento_index');
        }

        return $this->render('vencimiento/new.html.twig', [
            'vencimiento' => $vencimiento,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="vencimiento_show", methods={"GET"})
     */
    public function show(Vencimiento $vencimiento): Response
    {
        return $this->render('vencimiento/show.html.twig', [
            'vencimiento' => $vencimiento,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="vencimiento_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Vencimiento $vencimiento): Response
    {
        $form = $this->createForm(VencimientoType::class, $vencimiento);
        $form->add('monto_max');
        $form->add('solo_por_admin',CheckboxType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('vencimiento_index');
        }

        return $this->render('vencimiento/edit.html.twig', [
            'vencimiento' => $vencimiento,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="vencimiento_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Vencimiento $vencimiento): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vencimiento->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($vencimiento);
            $entityManager->flush();
        }

        return $this->redirectToRoute('vencimiento_index');
    }
}
