<?php

namespace App\Controller;

use App\Entity\ClientePotencial;
use App\Form\ClientePotencialType;
use App\Repository\ClientePotencialRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @Route("/cliente_potencial")
 */
class ClientePotencialController extends AbstractController
{
    /**
     * @Route("/", name="cliente_potencial_index", methods={"GET"})
     */
    public function index(ClientePotencialRepository $clientePotencialRepository, PaginatorInterface $paginator,Request $request): Response
    {
        $query=$clientePotencialRepository->findAll();
        $clientesPotenciales=$paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /*page number*/
        20 /*limit per page*/,
        array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));


        return $this->render('cliente_potencial/index.html.twig', [
            'clientes_potenciales' => $clientesPotenciales,
            'pagina'=>'Clientes Potenciales'
        ]);
    }

    /**
     * @Route("/new", name="cliente_potencial_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $clientePotencial = new ClientePotencial();
        $form = $this->createForm(ClientePotencialType::class, $clientePotencial);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($clientePotencial);
            $entityManager->flush();

            return $this->redirectToRoute('cliente_potencial_index');
        }

        return $this->render('cliente_potencial/new.html.twig', [
            'cliente_potencial' => $clientePotencial,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="cliente_potencial_show", methods={"GET"})
     */
    public function show(ClientePotencial $clientePotencial): Response
    {
        return $this->render('cliente_potencial/show.html.twig', [
            'cliente_potencial' => $clientePotencial,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="cliente_potencial_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ClientePotencial $clientePotencial): Response
    {
        $form = $this->createForm(ClientePotencialType::class, $clientePotencial);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('cliente_potencial_index');
        }

        return $this->render('cliente_potencial/edit.html.twig', [
            'cliente_potencial' => $clientePotencial,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="cliente_potencial_delete", methods={"DELETE"})
     */
    public function delete(Request $request, ClientePotencial $clientePotencial): Response
    {
        if ($this->isCsrfTokenValid('delete'.$clientePotencial->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($clientePotencial);
            $entityManager->flush();
        }

        return $this->redirectToRoute('cliente_potencial_index');
    }
}
