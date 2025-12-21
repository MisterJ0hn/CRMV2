<?php

namespace App\Controller;
use App\Entity\Usuario;
use App\Entity\UsuarioNoDisponible;
use App\Form\UsuarioNoDisponibleType;
use App\Repository\UsuarioNoDisponibleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/usuario_no_disponible")
 */
class UsuarioNoDisponibleController extends AbstractController
{
    /**
     * @Route("/{id}", name="usuario_no_disponible_index", methods={"GET","POST"})
     */
    public function index(Usuario $usuario, UsuarioNoDisponibleRepository $usuarioNoDisponibleRepository): Response
    {
        return $this->render('usuario_no_disponible/index.html.twig', [
            'usuario_no_disponibles' => $usuarioNoDisponibleRepository->findBy(['usuario'=>$usuario->getId()]),
        ]);
    }

    /**
     * @Route("/{id}/new", name="usuario_no_disponible_new", methods={"GET","POST"})
     */
    public function new(Usuario $usuario,Request $request, UsuarioNoDisponibleRepository $usuarioNoDisponibleRepository): Response
    {
        $horaInicio=$request->query->get('horaInicio');
        $horaFin=$request->query->get('horaFin');
        $fechaInicio=$request->query->get('fecha_inicio')==""? null :$request->query->get('fecha_inicio');
        $fechaFin=$request->query->get('fecha_fin')==""?null :$request->query->get('fecha_fin');
        $concepto=$request->query->get('concepto');
        $anio=$request->query->get('anios');
        $mes=$request->query->get('mes');
        $dias=$request->query->get('dias');
        
        $usuarioNoDisponible = new UsuarioNoDisponible();

            $entityManager = $this->getDoctrine()->getManager();
            $usuarioNoDisponible->setUsuario($usuario);
            $usuarioNoDisponible->setFecha(new \DateTime(date("Y-m-d")));
            if($request->query->get('fecha_inicio')!='')
                $usuarioNoDisponible->setFechaInicio(new \DateTime(date("Y-m-d",strtotime($fechaInicio))));
            if ($request->query->get('fecha_fin')!='')
                $usuarioNoDisponible->setFechaFin(new \DateTime(date("Y-m-d",strtotime($fechaFin))));
            $usuarioNoDisponible->setMes(intval($mes));
            $usuarioNoDisponible->setAnio(intval($anio));
            $usuarioNoDisponible->setDia(intval($dias));
            $usuarioNoDisponible->setConcepto($concepto);
            $usuarioNoDisponible->setHoraInicio(new \DateTime(date("H:i",strtotime(date("Y-m-d")." ".$horaInicio))));
            $usuarioNoDisponible->setHoraFin(new \DateTime(date("H:i",strtotime(date("Y-m-d")." ".$horaFin))));
            $entityManager->persist($usuarioNoDisponible);
            $entityManager->flush();

            //return $this->redirectToRoute('usuario_no_disponible_index');
       

        return $this->render('usuario_no_disponible/index.html.twig', [
           
            'usuario_no_disponibles' => $usuarioNoDisponibleRepository->findBy(['usuario'=>$usuario->getId()]),
        ]);
    }

    /**
     * @Route("/{id}/show", name="usuario_no_disponible_show", methods={"GET"})
     */
    public function show(UsuarioNoDisponible $usuarioNoDisponible): Response
    {
        return $this->render('usuario_no_disponible/show.html.twig', [
            'usuario_no_disponible' => $usuarioNoDisponible,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="usuario_no_disponible_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, UsuarioNoDisponible $usuarioNoDisponible): Response
    {
        $form = $this->createForm(UsuarioNoDisponibleType::class, $usuarioNoDisponible);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('usuario_no_disponible_index');
        }

        return $this->render('usuario_no_disponible/edit.html.twig', [
            'usuario_no_disponible' => $usuarioNoDisponible,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="usuario_no_disponible_delete", methods={"GET","POST"})
     */
    public function delete(Request $request, UsuarioNoDisponible $usuarioNoDisponible, UsuarioNoDisponibleRepository $usuarioNoDisponibleRepository): Response
    {
            $entityManager = $this->getDoctrine()->getManager();
            $usuario=$usuarioNoDisponible->getUsuario();
            $entityManager->remove($usuarioNoDisponible);
            $entityManager->flush();

        return $this->render('usuario_no_disponible/index.html.twig', [
            'usuario_no_disponible' => $usuarioNoDisponible,

            'usuario_no_disponibles' => $usuarioNoDisponibleRepository->findBy(['usuario'=>$usuario->getId()]),
        ]);
    }
    
}
