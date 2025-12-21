<?php

namespace App\Controller;

use App\Entity\AgendaSubStatus;
use App\Form\AgendaSubStatusType;
use App\Repository\AgendaSubStatusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/agenda_sub_status")
 */
class AgendaSubStatusController extends AbstractController
{
    /**
     * @Route("/", name="agenda_sub_status_index", methods={"GET"})
     */
    public function index(AgendaSubStatusRepository $agendaSubStatusRepository): Response
    {
        return $this->render('agenda_sub_status/index.html.twig', [
            'agenda_sub_statuses' => $agendaSubStatusRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="agenda_sub_status_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $agendaSubStatus = new AgendaSubStatus();
        $form = $this->createForm(AgendaSubStatusType::class, $agendaSubStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($agendaSubStatus);
            $entityManager->flush();

            return $this->redirectToRoute('agenda_sub_status_index');
        }

        return $this->render('agenda_sub_status/new.html.twig', [
            'agenda_sub_status' => $agendaSubStatus,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="agenda_sub_status_show", methods={"GET"})
     */
    public function show(AgendaSubStatus $agendaSubStatus): Response
    {
        return $this->render('agenda_sub_status/show.html.twig', [
            'agenda_sub_status' => $agendaSubStatus,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="agenda_sub_status_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, AgendaSubStatus $agendaSubStatus): Response
    {
        $form = $this->createForm(AgendaSubStatusType::class, $agendaSubStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('agenda_sub_status_index');
        }

        return $this->render('agenda_sub_status/edit.html.twig', [
            'agenda_sub_status' => $agendaSubStatus,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="agenda_sub_status_delete", methods={"DELETE"})
     */
    public function delete(Request $request, AgendaSubStatus $agendaSubStatus): Response
    {
        if ($this->isCsrfTokenValid('delete'.$agendaSubStatus->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($agendaSubStatus);
            $entityManager->flush();
        }

        return $this->redirectToRoute('agenda_sub_status_index');
    }
}
