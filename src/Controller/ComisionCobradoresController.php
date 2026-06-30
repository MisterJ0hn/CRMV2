<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ComisionCobradoresController extends AbstractController
{
    /**
     * @Route("/comision/cobradores", name="app_comision_cobradores")
     */
    public function index(): Response
    {
        return $this->render('comision_cobradores/index.html.twig', [
            'controller_name' => 'ComisionCobradoresController',
        ]);
    }
}
