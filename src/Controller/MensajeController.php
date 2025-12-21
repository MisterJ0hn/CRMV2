<?php

namespace App\Controller;

use App\Entity\Mensaje;
use App\Entity\MensajeTipo;
use App\Entity\Usuario;
use App\Form\MensajeType;
use App\Repository\MensajeRepository;
use Doctrine\ORM\EntityRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mensaje")
 */
class MensajeController extends AbstractController
{
    /**
     * @Route("/", name="mensaje_index", methods={"GET"})
     */
    public function index(Request $request, MensajeRepository $mensajeRepository, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('view','mensaje');

        $user=$this->getUser();
        $tipoMensaje=0;
        if(null !== $request->query->get('bFecha')){
            $aux_fecha=explode(" - ",$request->query->get('bFecha'));
            $dateInicio=$aux_fecha[0];
            $dateFin=$aux_fecha[1];
        }else{
            //$dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*7);
            $dateInicio=date('Y-m-d');
            
            $dateFin=date('Y-m-d');
        }
        if(null != $request->query->get('bTipoMensaje')){
            $tipoMensaje = $request->query->get('bTipoMensaje');
        }
        
        $query=$mensajeRepository->findConFitro($user->getId(),$tipoMensaje,$dateInicio,$dateFin);
        $mensajes=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/,
            array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));

        return $this->render('mensaje/index.html.twig', [
            'mensajes' => $mensajes,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'tipoMensaje'=>$tipoMensaje
        ]);
    }

    /**
     * @Route("/new", name="mensaje_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $mensaje = new Mensaje();
        $form = $this->createForm(MensajeType::class, $mensaje);
        $form->add('usuarioDestino' ,EntityType::class,[
            
            'class' => Usuario::class,
            'query_builder' => function (EntityRepository $er) {
                $user=$this->getUser();
                return $er->createQueryBuilder('u')->AndWhere('u.usuarioTipo = 7')->andWhere('u.estado=true');
            },
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($mensaje);
            $entityManager->flush();

            return $this->redirectToRoute('mensaje_index');
        }

        return $this->render('mensaje/new.html.twig', [
            'mensaje' => $mensaje,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/calendario", name="mensaje_calendario", methods={"GET","POST"})
     */
    public function calendario(Request $request, MensajeRepository $mensajeRepository): Response
    {

        $this->denyAccessUnlessGranted('view','mensaje_calendario');


        $user=$this->getUser();
        $mensaje = new Mensaje();
        $mensaje->setUsuarioRegistro($user);
        $mensaje->setUsuarioDestino($user);
        $mensaje->setFechaCreacion(new \DateTime(date("Y-m-d H:i:s")));
        $mensaje->setLeido(false);
        $form = $this->createForm(MensajeType::class, $mensaje);
        $form->add('observacion');
        $form->add('fechaAviso',DateType::class, [
            // renders it as a single text box
            'widget' => 'single_text',
        ]);
        
        $form->add('mensajeTipo',EntityType::class,[
            'class' => MensajeTipo::class,
            'query_builder' => function (EntityRepository $er) {
                $user=$this->getUser();
                return $er->createQueryBuilder('mt')->AndWhere('mt.id = 1');
            }
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($mensaje);
            $entityManager->flush();

            return $this->redirectToRoute('mensaje_calendario');
        }


        $mensajes = $mensajeRepository->findBy(['usuarioDestino'=>$user->getId()]);
        return $this->render('mensaje/calendario.html.twig', [
            'mensaje' => $mensaje,
            'mensajes'=>$mensajes,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/calendario_jefe", name="mensaje_calendario_jefe", methods={"GET","POST"})
     */
    public function calendarioJefe(Request $request, MensajeRepository $mensajeRepository): Response
    {
        $this->denyAccessUnlessGranted('view','mensaje_calendario_jefe');
        $user=$this->getUser();
        $mensaje = new Mensaje();
        $mensaje->setUsuarioRegistro($user);
        $mensaje->setFechaCreacion(new \DateTime(date("Y-m-d H:i:s")));
        $mensaje->setLeido(false);
        $form = $this->createForm(MensajeType::class, $mensaje);
        $form->add('observacion');
        $form->add('fechaAviso',DateType::class, [
            // renders it as a single text box
            'widget' => 'single_text',
        ]);
        $form->add('usuarioDestino' ,EntityType::class,[
            
            'class' => Usuario::class,
            'query_builder' => function (EntityRepository $er) {
                $user=$this->getUser();
                return $er->createQueryBuilder('u')->AndWhere('u.usuarioTipo = 7')->andWhere('u.estado=true');
            },
        ]);
        $form->add('mensajeTipo' ,EntityType::class,[
            
            'class' => MensajeTipo::class,
            'query_builder' => function (EntityRepository $er) {
                $user=$this->getUser();
                return $er->createQueryBuilder('m')->AndWhere('m.id=2');
            },
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($mensaje);
            $entityManager->flush();

            return $this->redirectToRoute('mensaje_calendario_jefe');
        }


        $mensajes = $mensajeRepository->findBy(['usuarioRegistro'=>$user->getId()]);
        return $this->render('mensaje/calendarioJefe.html.twig', [
            'mensaje' => $mensaje,
            'mensajes'=>$mensajes,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/avisos", name="mensaje_avisos", methods={"GET","POST"})
     */
    public function avisos(MensajeRepository $mensajeRepository): Response
    {
        $user=$this->getUser();
        $mensajes = $mensajeRepository->findByVencidas($user->getId(),date('Y-m-d'));
        $mensajesCount= $mensajeRepository->findByVencidasCount($user->getId(),date('Y-m-d'));
        return $this->render('mensaje/avisos.html.twig',[
            'pagina'=>'Mensajes',
            'mensajes'=>$mensajes,
            'mensajesCount'=>$mensajesCount
        ]);
    }


    /**
     * @Route("/{id}", name="mensaje_show", methods={"GET"})
     */
    public function show(Mensaje $mensaje): Response
    {
        return $this->render('mensaje/show.html.twig', [
            'mensaje' => $mensaje,
        ]);
    }
    /**
     * @Route("/{id}/leido", name="mensaje_leido", methods={"GET"})
     */
    public function leidoAgenda(Mensaje $mensaje): Response
    {
        
        $this->marcarLeido($mensaje);
        return $this->redirectToRoute('mensaje_index');
        
        
    }

    /**
     * @Route("/{id}/leido_list", name="mensaje_leido_list", methods={"GET"})
     */
    public function leidoList(Mensaje $mensaje): Response
    {
        
        $this->marcarLeido($mensaje);
        
        return $this->redirectToRoute('mensaje_index');
        
        
    }

    

    /**
     * @Route("/{id}/edit", name="mensaje_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Mensaje $mensaje): Response
    {
        $form = $this->createForm(MensajeType::class, $mensaje);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('mensaje_index');
        }

        return $this->render('mensaje/edit.html.twig', [
            'mensaje' => $mensaje,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="mensaje_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Mensaje $mensaje): Response
    {
        if ($this->isCsrfTokenValid('delete'.$mensaje->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($mensaje);
            $entityManager->flush();
        }

        return $this->redirectToRoute('mensaje_index');
    }

    public function marcarLeido(Mensaje $mensaje){
        $mensaje->setLeido(true);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($mensaje);
        $entityManager->flush();
    }
}
