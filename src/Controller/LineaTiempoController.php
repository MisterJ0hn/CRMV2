<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/linea_tiempo")
 */
class LineaTiempoController extends AbstractController
{
    /**
     * @Route("/", name="linea_tiempo_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('linea_tiempo/index.html.twig', [
            'controller_name' => 'LineaTiempoController',
        ]);
    }

    /**
     * @Route("/{id}/list", name="linea_tiempo_list", methods={"GET"})
     */

     public function list(): Response
     {
        return $this->render("linea_tiempo/list.html.twig",[

        ]);
     }

}
