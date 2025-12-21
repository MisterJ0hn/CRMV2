<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/extender_vigencias")
 */
class ExtenderVigenciasController extends AbstractController
{
    /**
     * @Route("/", name="extender_vigencias_index", methods={"GET","POST"})
     */
    public function index(): Response
    {
        return $this->render('extender_vigencias/index.html.twig', [
            'controller_name' => 'ExtenderVigenciasController',
        ]);
    }
}
