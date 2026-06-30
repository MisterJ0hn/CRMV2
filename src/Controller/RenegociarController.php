<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

    /**
     * @Route("/renegociar")
     */
class RenegociarController extends AbstractController
{
    /**
     * @Route("/", name="renegociar_index", methods={"GET","POST"})
     */
    public function index(): Response
    {
        return $this->render('renegociar/index.html.twig', [
            'controller_name' => 'RenegociarController',
        ]);
    }
}
