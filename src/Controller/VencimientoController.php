<?php

namespace App\Controller;

use App\Entity\EquipoTrabajoVencimiento;
use App\Entity\Vencimiento;
use App\Form\VencimientoType;
use App\Repository\EquipoTrabajoRepository;
use App\Repository\EquipoTrabajoVencimientoRepository;
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
    public function new(Request $request,EquipoTrabajoRepository $equipoTrabajoRepository): Response
    {
        $user = $this->getUser();
        $vencimiento = new Vencimiento();
        $vencimiento->setEmpresa($user->getEmpresaActual());
        $form = $this->createForm(VencimientoType::class, $vencimiento);
        $form->handleRequest($request);
        $equipo =null;
        $equipos = $equipoTrabajoRepository->findAll();
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($vencimiento);
            $entityManager->flush();

            return $this->redirectToRoute('vencimiento_index');
        }

        return $this->render('vencimiento/new.html.twig', [
            'vencimiento' => $vencimiento,
            'form' => $form->createView(),
            'equipos'=>$equipos,
            'equipo_actual'=>$equipo
        ]);
    }

    /**
     * @Route("/{id}", name="vencimiento_show", methods={"GET"})
     */
    public function show(Vencimiento $vencimiento): Response
    {
        return $this->render('vencimiento/show.html.twig', [
            'vencimiento' => $vencimiento]);
    }

    /**
     * @Route("/{id}/edit", name="vencimiento_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, 
                        Vencimiento $vencimiento, 
                        EquipoTrabajoVencimientoRepository $equipoTrabajoVencimientoRepository,
                        EquipoTrabajoRepository $equipoTrabajoRepository): Response
    {
        $user = $this->getUser();

        
        if($vencimiento->getEmpresa()->getId() !== $user->getEmpresaActual()){
            throw $this->createAccessDeniedException("No tienes permiso para editar este vencimiento");
        }
        $form = $this->createForm(VencimientoType::class, $vencimiento);
        $form->add('montoMax');
        $form->add('soloPorAdmin',CheckboxType::class,["required"=>false]);
        
        $form->handleRequest($request);

        $equipoTrabajoVencimiento = $equipoTrabajoVencimientoRepository->findOneBy(['vencimiento'=>$vencimiento]);
        $equipo = null;
        if($equipoTrabajoVencimiento!=null)
            $equipo = $equipoTrabajoVencimiento->getEquipoTrabajo();

        $equipos = $equipoTrabajoRepository->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            if($request->request->get('cboEquipo')!=null && $request->request->get('cboEquipo')!=''){
               
                $this->getDoctrine()->getManager()->flush();
                if($equipoTrabajoVencimiento!=null)
                    $equipoTrabajoVencimientoRepository->remove($equipoTrabajoVencimiento);
                $nuevoEquipoTrabajoVencimiento = new EquipoTrabajoVencimiento();
                $nuevoEquipoTrabajoVencimiento->setEquipoTrabajo($equipoTrabajoRepository->find($request->request->get('cboEquipo')));
                $nuevoEquipoTrabajoVencimiento->setVencimiento($vencimiento);
                $equipoTrabajoVencimientoRepository->add($nuevoEquipoTrabajoVencimiento);


                return $this->redirectToRoute('vencimiento_index');
                 
            }else{ 
                $this->addFlash('error', 'Debe seleccionar un equipo');
            }
        }

        return $this->render('vencimiento/edit.html.twig', [
            'pagina'=> 'Editar semáforo cobranza',
            'vencimiento' => $vencimiento,
            'form' => $form->createView(),
            'equipos'=>$equipos,
            'equipo_actual'=>$equipo
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
