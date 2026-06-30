<?php

namespace App\Controller;

use App\Entity\Mensaje;
use App\Entity\MensajeTipo;
use App\Entity\Usuario;
use App\Form\MensajeType;
use App\Repository\EmpresaRepository;
use App\Repository\MensajePrioridadRepository;
use App\Repository\MensajeRepository;
use App\Repository\MensajeTipoRepository;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function index(Request $request, 
                        MensajeRepository $mensajeRepository, 
                        PaginatorInterface $paginator, 
                        MensajePrioridadRepository $mensajePrioridadRepository,
                        EmpresaRepository $empresaRepository
                        ): Response
    {
        $this->denyAccessUnlessGranted('view','mensaje');

        $user=$this->getUser();$user=$this->getUser();
        
        $prioridad = null;
        $empresa = $empresaRepository->find($user->getEmpresaActual());
    

        $prioridad = null;
       $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*60);
        $dateFin=date('Y-m-d');
        
        return $this->render('mensaje/index.html.twig', [
            'pagina'=>'Resumen',           
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'bPrioridad'=>$prioridad,
            'prioridades'=>$mensajePrioridadRepository->findAll()
        ]);
    }
     /**
     * @Route("/obtenerContenido", name="mensaje_obtener_contenido", methods={"GET"})
     */
    public function obtenerContenido(
                        Request $request, 
                        MensajeRepository $mensajeRepository, 
                        PaginatorInterface $paginator, 
                        MensajePrioridadRepository $mensajePrioridadRepository,
                        EmpresaRepository $empresaRepository
        
    ): JsonResponse {

        try{
            $user=$this->getUser();
        
            $prioridad = null;
            $estado = null;
            $empresa = $empresaRepository->find($user->getEmpresaActual());

            if(null !== $request->query->get('bFecha')){
                $aux_fecha=explode(" - ",$request->query->get('bFecha'));
                $dateInicio=$aux_fecha[0];
                $dateFin=$aux_fecha[1];
            }else{
                //$dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*7);
                
                $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*60);
                $dateFin=date('Y-m-d');
            }
            if(null != $request->query->get('bPrioridad') && $request->query->get('bPrioridad') != 0){
                $prioridad = $request->query->get('bPrioridad');
            }
            if(null != $request->query->get('bEstado') && $request->query->get('bEstado') != 0){
                $estado = $request->query->get('bEstado');
            }
            $query=$mensajeRepository->findConFiltro($user->getId(),$dateInicio,$dateFin,$prioridad,$estado);
            $mensajes=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/,
            array('defaultSortFieldName' => 'm.id', 'defaultSortDirection' => 'desc'));

            $html = $this->renderView('mensaje/_tabla.html.twig', [
                'mensajes' => $mensajes
            ]);

            return new JsonResponse([
                'html'  => $html,
                'total' => $mensajes->getTotalItemCount()                
            
            ]);
        

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()]);
        }

    }
    /**
     * @Route("/estudio", name="mensaje_estudio_index", methods={"GET"})
     */
    public function indexEstudio(Request $request, 
                        MensajeRepository $mensajeRepository, 
                        PaginatorInterface $paginator, 
                        MensajePrioridadRepository $mensajePrioridadRepository,
                        EmpresaRepository $empresaRepository,
                        UsuarioRepository $usuarioRepository
                        ): Response
    {
        $this->denyAccessUnlessGranted('view','mensaje_estudio');

        $user=$this->getUser();
        $empresa = $empresaRepository->find($user->getEmpresaActual());
        $prioridad = null;     
        $usuarioDestino = null;  
        $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*60);
        $dateFin=date('Y-m-d');
      
        $usuarios = $usuarioRepository->findBy(['estado'=>true,'usuarioTipo'=>7]);
        
        return $this->render('mensaje/index_estudio.html.twig', [
            'pagina'=>'Resumen Estudio',
          
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'bPrioridad'=>$prioridad,
            'prioridades'=>$mensajePrioridadRepository->findAll(),
            'bUsuarioDestino'=>$usuarioDestino,
            'usuarios' => $usuarios
        ]);
    }
    /**
     * @Route("/obtenerContenidoEstudio", name="mensaje_obtener_contenido_estudio", methods={"GET"})
     */
    public function obtenerContenidoEstudio(
                        Request $request, 
                        MensajeRepository $mensajeRepository, 
                        PaginatorInterface $paginator, 
                        MensajePrioridadRepository $mensajePrioridadRepository,
                        EmpresaRepository $empresaRepository
        
    ): JsonResponse {

        try{
            $user=$this->getUser();
        
            $prioridad = null;
            $estado = null;
            $usuarioDestino = null;  
            $empresa = $empresaRepository->find($user->getEmpresaActual());

            if(null !== $request->query->get('bFecha')){
                $aux_fecha=explode(" - ",$request->query->get('bFecha'));
                $dateInicio=$aux_fecha[0];
                $dateFin=$aux_fecha[1];
            }else{

                $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*60);
                $dateFin=date('Y-m-d');
            }
            if(null != $request->query->get('bPrioridad') && $request->query->get('bPrioridad') != 0){
                $prioridad = $request->query->get('bPrioridad');
            }
            if(null != $request->query->get('bEstado') && $request->query->get('bEstado') != 0){
                $estado = $request->query->get('bEstado');
            }

            if(null != $request->query->get('bUsuarioDestino') && $request->query->get('bUsuarioDestino') != 0){
                $usuarioDestino = $request->query->get('bUsuarioDestino');
            }
            $query=$mensajeRepository->findConFiltro($usuarioDestino,$dateInicio,$dateFin,$prioridad,$estado,2,7);
            $mensajes=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/,
            array('defaultSortFieldName' => 'm.id', 'defaultSortDirection' => 'desc'));

            $html = $this->renderView('mensaje/_tabla_estudio.html.twig', [
                'mensajes' => $mensajes
            ]);

            return new JsonResponse([
                'html'  => $html,
                'total' => $mensajes->getTotalItemCount()                
            
            ]);
        

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()]);
        }

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
    public function calendario(Request $request, MensajeRepository $mensajeRepository,MensajeTipoRepository $mensajeTipoRepository): Response
    {

       
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $user=$this->getUser();
        $tipoAsignacion = 0;
        $mensaje = new Mensaje();
        $mensaje->setUsuarioRegistro($user);
        $mensaje->setUsuarioDestino($user);
        $mensaje->setFechaCreacion(new \DateTime(date("Y-m-d H:i:s")));
        $mensaje->setLeido(false);
        $mensaje->setMensajeTipo($mensajeTipoRepository->find(2));


        if(null != $request->query->get('bTipoAsignacion') && $request->query->get('bTipoAsignacion') != 0){
            $tipoAsignacion = $request->query->get('bTipoAsignacion');
        }


        $form = $this->createForm(MensajeType::class, $mensaje);
        $form->add('observacion');
        $form->add('fechaAviso',DateType::class, [
            // renders it as a single text box
            'widget' => 'single_text',
        ]);
        $form->add('mensajePrioridad',EntityType::class,[
            'class' => 'App\Entity\MensajePrioridad',
            'choice_label' => 'nombre',
            'placeholder' => 'Seleccione Prioridad',
            'required'=>true
        ]);
       
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $hora=$request->request->get("cboHora");
            $minuto=$request->request->get("cboMinuto");
           
            $mensaje->setFechaAviso(new \DateTime($mensaje->getFechaAviso()->format('Y-m-d')." ".$hora.":".$minuto));


            $entityManager->persist($mensaje);
            $entityManager->flush();

            return $this->redirectToRoute('mensaje_calendario');
        }

        $mensajes = $mensajeRepository->findConFiltroCalendario($user->getId(),$tipoAsignacion);

        return $this->render('mensaje/calendario.html.twig', [
            'pagina' => 'Calendario',
            'mensaje' => $mensaje,
            'mensajes'=>$mensajes,
            'form' => $form->createView(),
            'bTipoAsignacion' => $tipoAsignacion
        ]);
    }
    /**
     * @Route("/calendario_jefe", name="mensaje_calendario_jefe", methods={"GET","POST"})
     */
    public function calendarioJefe(Request $request, MensajeRepository $mensajeRepository,MensajeTipoRepository $mensajeTipoRepository, UsuarioRepository $usuarioRepository): Response
    {
        $this->denyAccessUnlessGranted('view','mensaje_calendario_jefe');
        $user=$this->getUser();
        $usuarioDestino = 0;
        $tipoAsignacion = 0;
        $mensaje = new Mensaje();
        $mensaje->setUsuarioRegistro($user);
        $mensaje->setFechaCreacion(new \DateTime(date("Y-m-d H:i:s")));
        $mensaje->setLeido(false);
        $mensaje->setMensajeTipo($mensajeTipoRepository->find(2));

         if(null != $request->query->get('bUsuarioDestino') && $request->query->get('bUsuarioDestino') != 0){
            $usuarioDestino = $request->query->get('bUsuarioDestino');
        }
        if(null != $request->query->get('bTipoAsignacion') && $request->query->get('bTipoAsignacion') != 0){
            $tipoAsignacion = $request->query->get('bTipoAsignacion');
        }


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
        $form->add('mensajePrioridad',EntityType::class,[
            'class' => 'App\Entity\MensajePrioridad',
            'choice_label' => 'nombre',
            'placeholder' => 'Seleccione Prioridad',
            'required'=>true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
             $hora=$request->request->get("cboHora");
            $minuto=$request->request->get("cboMinuto");
           
            $mensaje->setFechaAviso(new \DateTime($mensaje->getFechaAviso()->format('Y-m-d')." ".$hora.":".$minuto));
            $entityManager->persist($mensaje);
            $entityManager->flush();

            return $this->redirectToRoute('mensaje_calendario_jefe');
        }

        $usuarios = $usuarioRepository->findBy(['estado'=>true,'usuarioTipo'=>7]);

        $mensajes = $mensajeRepository->findConFiltroCalendario($usuarioDestino,$tipoAsignacion,7);

        return $this->render('mensaje/calendarioJefe.html.twig', [
            'pagina' => 'Calendario Estudio',
            'mensaje' => $mensaje,
            'mensajes'=>$mensajes,
            'form' => $form->createView(),
            'bUsuarioDestino'=>$usuarioDestino,
            'usuarios' => $usuarios,
            'bTipoAsignacion' => $tipoAsignacion
        ]);
    }
    /**
     * @Route("/estudio/{id}", name="mensaje_estudio_show", methods={"GET"})
     */
    public function showEstudio(Mensaje $mensaje): Response
    {
        $user = $this->getUser();
        
        $this->denyAccessUnlessGranted('view','mensaje');
        return $this->render('mensaje/show.html.twig', [
            'mensaje' => $mensaje,
        ]);
    }

    /**
     * @Route("/avisos", name="mensaje_avisos", methods={"GET","POST"})
     */
    public function avisos(MensajeRepository $mensajeRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return new Response('');
        }
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
        $user = $this->getUser();
        $permite_finalizar = false;
        $this->denyAccessUnlessGranted('view','mensaje');

        if($mensaje->getUsuarioDestino()->getId() == $user->getId()){
            $permite_finalizar = true;
        }
        if($mensaje->getLeido()){
           $permite_finalizar = false;
        }
        return $this->render('mensaje/show.html.twig', [
            'mensaje' => $mensaje,
            'permite_finalizar'=> $permite_finalizar

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
     * @Route("/{id}/obtener_mensaje", name="obtener_mensaje", methods={"GET"})
     */
    public function obtenerMensaje(Mensaje $mensaje, MensajeRepository $mensajeRepository): JsonResponse
    {
        if (!$mensaje) {
            return new JsonResponse(['error' => 'Mensaje no encontrado'], 404);
        }

        return new JsonResponse(['mensaje' => $mensaje->getObservacion()]);
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
