<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/agregar_causas")
 */
class AgregarCausasController extends AbstractController
{
    /**
     * @Route("/", name="agregar_causas_index", methods={"GET","POST"})
     */
    public function index(): Response
    {
        return $this->render('agregar_causas/index.html.twig', [
            'controller_name' => 'AgregarCausasController',
        ]);
    }
}
