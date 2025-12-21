<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CampaniaController extends AbstractController
{
    /**
     * @Route("/campania", name="campania")
     */
    public function index(): Response
    {
        return $this->render('campania/index.html.twig', [
            'controller_name' => 'CampaniaController',
        ]);
    }
}
