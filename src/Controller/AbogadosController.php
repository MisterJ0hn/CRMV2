<?php

namespace App\Controller;
use App\Entity\Usuario;
use App\Entity\UsuarioCategoria;
use App\Entity\Empresa;
use App\Entity\UsuarioCuenta;
use App\Entity\Cuenta;
use App\Entity\UsuarioStatus;
use App\Entity\Privilegio;
use App\Entity\PrivilegioTipousuario;
use App\Form\UsuarioType;
use App\Repository\UsuarioRepository;
use App\Repository\UsuarioTipoRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\UsuarioNoDisponibleRepository;
use App\Repository\UsuarioTipoDocumentoRepository;
use App\Repository\PrivilegioTipousuarioRepository;
use App\Repository\PrivilegioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Knp\Component\Pager\PaginatorInterface;
/**
 * @Route("/abogados")
 */
class AbogadosController extends AbstractController
{
    /**
     * @Route("/", name="abogados_index",methods={"GET"})
     */
    public function index(UsuarioRepository $usuarioRepository,
                    ModuloPerRepository $moduloPerRepository,
                    PaginatorInterface $paginator,
                    Request $request): Response
    {
        $this->denyAccessUnlessGranted('view','abogados');
        $user=$this->getUser();
        $modo=1;
        if($request->query->get('modo')=='trash'){
            $modo=0;
            
        }
      
        $pagina=$moduloPerRepository->findOneByName('abogados',$user->getEmpresaActual());
        $query=$usuarioRepository->findByEmpresa($user->getEmpresaActual(),['usuarioTipo'=>6,'estado'=>$modo]);
        $usuarios=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/,
            array('defaultSortFieldName' => 'nombre', 'defaultSortDirection' => 'asc'));

        return $this->render('abogados/index.html.twig', [
            'usuarios' => $usuarios,
            'pagina'=>$pagina->getNombre(),
            'modo'=>$modo,
        ]);
    }

    /**
     * @Route("/new", name="abogados_new", methods={"GET","POST"})
     */
    public function new(Request $request,
                        UserPasswordEncoderInterface $encoder,
                        UsuarioTipoRepository $usuarioTipoRepository,
                        ModuloPerRepository $moduloPerRepository,
                        UsuarioTipoDocumentoRepository $tipoDocumento,
                        PrivilegioTipousuarioRepository $privilegioTipousuarioRepository,
                        PrivilegioRepository $privilegioRepository,
                        UsuarioNoDisponibleRepository $suarioNoDisponibleRepository): Response
    {
        $this->denyAccessUnlessGranted('create','abogados');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('abogados',$user->getEmpresaActual());
        $usuario = new Usuario();
        $usuario->setEstado(1);
        $empresa=$this->getDoctrine()->getRepository(Empresa::class)->find($user->getEmpresaActual());
        
        $cuentas=$empresa->getCuentas();
        $choices= array();
        
        
        $usuario->setUsuarioTipo($usuarioTipoRepository->find(6));
        $usuario->setFechaActivacion(new \DateTime(date('Y-m-d H:i:s')));
        //$usuario->setFechaIngreso(new \DateTime(date('Y-m-d H:i:s')));

        $form = $this->createForm(UsuarioType::class, $usuario);
        $form->add('whatsapp',TextType::class);
        $form->add('color',TextType::class);
        

        $form->add('sexo',ChoiceType::class,[
            'choices' =>[
                'Masculino'=>'Masculino',
                'Femenino'=>'Femenino'
            ],
        ]
        );
        $form->add("sobrecupo");
        $form->add('lunes');
        $form->add('lunesStart',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('lunesEnd',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('martes');
        $form->add('martesStart',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('martesEnd',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('miercoles');
        $form->add('miercolesStart',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('miercolesEnd',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('jueves');
        $form->add('juevesStart',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('juevesEnd',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('viernes');
        $form->add('viernesStart',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('viernesEnd',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('sabado');
        $form->add('sabadoStart',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('sabadoEnd',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('domingo');
        $form->add('domingoStart',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('domingoEnd',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);

        $form->add("password", TextType::class);
        $form->add("passwordAnt", TextType::class,[
            'required'   => false,
            'attr'=>[
                'style'=>'display:none'
            ],
        ]);

        $horaInicio=$suarioNoDisponibleRepository->getHoras();
        $horaFin=$suarioNoDisponibleRepository->getHoras();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $password=$usuario->getPassword();
            $encoded=$encoder->encodePassword($usuario,$password);
            $usuario->setPassword($encoded);

            $usuario->setFechaNacimiento(new \DateTime(date('Y-m-d H:i',strtotime($request->request->get('fecha_nacimiento')))));
            $usuario->setFechaActivacion(new \DateTime(date('Y-m-d H:i',strtotime($request->request->get('fecha_ingreso')))));

            $usuario->setTipoDocumento($tipoDocumento->find($request->request->get('cboTipoDocumento')));

            
            $entityManager->persist($usuario);
            $entityManager->flush();

            $getcuentas=$_POST['cboEmpresa'];
         
            foreach($getcuentas as $getcuenta){
                $cuenta=$this->getDoctrine()->getRepository(Cuenta::class)->find($getcuenta);
                
                $usuarioCuenta=new UsuarioCuenta();

                $usuarioCuenta->setCuenta($cuenta);
                $usuarioCuenta->setUsuario($usuario);
                
                $entityManager->persist($usuarioCuenta);
                $entityManager->flush();
                $usuario->setEmpresaActual($cuenta->getEmpresa()->getId());
                $entityManager->persist($usuario);
                $entityManager->flush();
            }


            $privilegioTipousuarios=$privilegioTipousuarioRepository->findBy(['tipousuario'=>$usuario->getUsuarioTipo()->getId()]);
            foreach($privilegioTipousuarios as $privilegioTipousuario){
                $privilegio=$privilegioRepository->findBy(["moduloPer"=>$privilegioTipousuario->getModuloPer()->getId(),"usuario"=>$usuario->getId()]);
                if(!$privilegio){
    
                    $privilegioNew=new Privilegio();
                    $privilegioNew->setUsuario($usuario);
                    $privilegioNew->setModuloPer($privilegioTipousuario->getModuloPer());
                    $privilegioNew->setAccion($privilegioTipousuario->getAccion());
    
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($privilegioNew);
                    $entityManager->flush();
    
                }
            }
            return $this->redirectToRoute('abogados_index');
        }

        return $this->render('abogados/new.html.twig', [
            'usuario' => $usuario,
            'form' => $form->createView(),
            'pagina'=>$pagina->getNombre(),
            'cuentas'=>$cuentas,
            
            'tipo_documentos'=>$tipoDocumento->findAll(),
            'hora_inicio'=>$horaInicio,
            'hora_fin'=>$horaFin
        ]);
    }

    /**
     * @Route("/{id}", name="abogados_show", methods={"GET"})
     */
    public function show(Usuario $usuario,ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','abogados');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('abogados',$user->getEmpresaActual());
        return $this->render('usuario/show.html.twig', [
            'usuario' => $usuario,
            'pagina'=>$pagina->getNombre(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="abogados_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, 
                        Usuario $usuario,
                        UsuarioTipoRepository $usuarioTipoRepository,
                        ModuloPerRepository $moduloPerRepository,
                        UsuarioTipoDocumentoRepository $tipoDocumento,
                        UserPasswordEncoderInterface $encoder,
                        
                        UsuarioNoDisponibleRepository $usuarioNoDisponibleRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','abogados');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('abogados',$user->getEmpresaActual());
        $empresa=$this->getDoctrine()->getRepository(Empresa::class)->find($user->getEmpresaActual());
        $usuarioCuenta=$this->getDoctrine()->getRepository(UsuarioCuenta::class)->findOneBy(['usuario'=>$usuario->getId()]);

        $cuentas=$empresa->getCuentas();

        $statues=$this->getDoctrine()->getRepository(UsuarioStatus::class)->findBy(['id'=>[1,2]]);
        $form = $this->createForm(UsuarioType::class, $usuario);

        $form->add("password", TextType::class,[
            'required'   => false,
            'attr'=>[
                'style'=>'display:none'
            ],

        ]);
        $form->add("sobrecupo");
        $form->add("passwordAnt", TextType::class,[
            'required'   => false,
        ]);
        $form->add('sexo',ChoiceType::class,[
            'choices' =>[
                'Masculino'=>'Masculino',
                'Femenino'=>'Femenino'
            ],
        ]
        );
        $form->add('color',TextType::class);
        $form->add('lunes');
        $form->add('lunesStart',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('lunesEnd',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('martes');
        $form->add('martesStart',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('martesEnd',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('miercoles');
        $form->add('miercolesStart',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('miercolesEnd',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('jueves');
        $form->add('juevesStart',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('juevesEnd',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('viernes');
        $form->add('viernesStart',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('viernesEnd',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('sabado');
        $form->add('sabadoStart',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('sabadoEnd',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('domingo');
        $form->add('domingoStart',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->add('domingoEnd',TimeType::class,[
            'placeholder' => [
                'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo',
            ],
        ]);
        $form->handleRequest($request);

        $horaInicio=$usuarioNoDisponibleRepository->getHoras();
        $horaFin=$usuarioNoDisponibleRepository->getHoras();
        if ($form->isSubmitted() && $form->isValid()) {
            if($usuario->getPasswordAnt()!=""){
                $password=$usuario->getPasswordAnt();
                $encoded=$encoder->encodePassword($usuario,$password);
                $usuario->setPassword($encoded);
                $usuario->setPasswordAnt("");
            }
            
            $this->getDoctrine()->getManager()->flush();
            
            $entityManager = $this->getDoctrine()->getManager();

            
            $usuario->setFechaNacimiento(new \DateTime(date('Y-m-d H:i',strtotime($request->request->get('fecha_nacimiento')))));
            $usuario->setFechaActivacion(new \DateTime(date('Y-m-d H:i',strtotime($request->request->get('fecha_ingreso')))));
            
            $usuario->setTipoDocumento($tipoDocumento->find($request->request->get('cboTipoDocumento')));

            
            $usuarioCuentas=$usuario->getUsuarioCuentas();
            foreach($usuarioCuentas as $usuarioCuenta){
                $usuario->removeUsuarioCuenta($usuarioCuenta);
            }
            $getcuentas=$_POST['cboEmpresa'];
         
            foreach($getcuentas as $getcuenta){
                $cuenta=$this->getDoctrine()->getRepository(Cuenta::class)->find($getcuenta);
                
                $usuarioCuenta=new UsuarioCuenta();

                $usuarioCuenta->setCuenta($cuenta);
                $usuarioCuenta->setUsuario($usuario);
                 
                $entityManager->persist($usuarioCuenta);
                $entityManager->flush();
                if(is_null($usuario->getEmpresaActual())){
                    $usuario->setEmpresaActual($cuenta->getEmpresa()->getId());

                }
            }
            $entityManager->persist($usuario);
            $entityManager->flush();

            return $this->redirectToRoute('abogados_index');
        }

        return $this->render('abogados/edit.html.twig', [
            'usuario' => $usuario,
            'form' => $form->createView(),
            'pagina'=>$pagina->getNombre(),
            'cuentas'=>$cuentas,
            'cuentas_sel'=>$usuario->getUsuarioCuentas(),
            'tipo_documentos'=>$tipoDocumento->findAll(),
            'hora_inicio'=>$horaInicio,
            'hora_fin'=>$horaFin,
        ]);
    }
    /**
     * @Route("/{id}/restore", name="abogados_restore", methods={"GET"})
     */
    public function restore(Request $request, Usuario $usuario): Response
    {
        $this->denyAccessUnlessGranted('full','abogados');
      
            $entityManager = $this->getDoctrine()->getManager();
            $usuario->setEstado(1);
            $entityManager->persist($usuario);
            $entityManager->flush();
     

        return $this->redirectToRoute('abogados_index');
    }

    /**
     * @Route("/{id}", name="abogados_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Usuario $usuario): Response
    {
        $this->denyAccessUnlessGranted('full','abogados');
        if ($this->isCsrfTokenValid('delete'.$usuario->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $usuario->setEstado(0);
           

            $entityManager->persist($usuario);
            $entityManager->flush();
        }

        return $this->redirectToRoute('abogados_index');
    }
}
