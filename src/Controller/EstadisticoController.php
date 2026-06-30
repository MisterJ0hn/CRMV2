<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/estadistico")
 */

class EstadisticoController extends AbstractController
{
    /**
     * @Route("/", name="estadistico_index")
     */
    public function index(): Response
    {
        return $this->render('estadistico/index.html.twig', [
            'controller_name' => 'EstadisticoController',
        ]);
    }
    /**
     * @Route("/marketing", name="estadistico_marketing")
     */
    public function marketing(): Response
    {
        return $this->render('estadistico/estadistico_marketing.html.twig', [
            'controller_name' => 'EstadisticoMarketingController',
        ]);
    }
}
