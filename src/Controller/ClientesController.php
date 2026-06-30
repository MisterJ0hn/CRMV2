<?php

namespace App\Controller;
use App\Entity\Usuario;
use App\Entity\UsuarioCategoria;
use App\Entity\Empresa;
use App\Entity\UsuarioCuenta;
use App\Entity\Cuenta;
use App\Entity\UsuarioStatus;
use App\Form\UsuarioType;
use App\Repository\UsuarioRepository;
use App\Repository\UsuarioTipoRepository;
use App\Repository\ModuloPerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Knp\Component\Pager\PaginatorInterface;
/**
 * @Route("/clientes")
 */
class ClientesController extends AbstractController
{
    /**
     * @Route("/", name="clientes_index",methods={"GET"})
     */
    public function index(UsuarioRepository $usuarioRepository,
                    ModuloPerRepository $moduloPerRepository,
                    PaginatorInterface $paginator,
                    Request $request): Response
    {
        $this->denyAccessUnlessGranted('view','clientes');
        $user=$this->getUser();

        $modo=1;
        if($request->query->get('modo')=='trash'){
            $modo=0;
            
        }

        $pagina=$moduloPerRepository->findOneByName('clientes',$user->getEmpresaActual());
        $query=$usuarioRepository->findBy(['usuarioTipo'=>9,'estado'=>$modo]);
        $usuarios=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/,
            array('defaultSortFieldName' => 'nombre', 'defaultSortDirection' => 'asc'));

        return $this->render('clientes/index.html.twig', [
            'usuarios' => $usuarios,
            'pagina'=>$pagina->getNombre(),
            'modo'=>$modo,
        ]);
    }

    /**
     * @Route("/new", name="clientes_new", methods={"GET","POST"})
     */
    public function new(Request $request,UserPasswordEncoderInterface $encoder,UsuarioTipoRepository $usuarioTipoRepository,ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('create','clientes');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('clientes',$user->getEmpresaActual());
        $usuario = new Usuario();
        $usuario->setEstado(1);
        $empresa=$this->getDoctrine()->getRepository(Empresa::class)->find($user->getEmpresaActual());
        $usuarioCategorias=$empresa->getUsuarioCategorias();
        $cuentas=$empresa->getCuentas();
        $choices= array();
        
        $statusChoices=array();
        foreach($statues as $status){
            $statusChoices[$status->getNombre()]=$status->getId();
        }
        
        $usuario->setUsuarioTipo($usuarioTipoRepository->find(9));
        $usuario->setFechaActivacion(new \DateTime(date('Y-m-d H:i:s')));
        
        $form = $this->createForm(UsuarioType::class, $usuario);
        $form->add('whatsapp',TextType::class);
        
        $form->add('sexo',ChoiceType::class,[
            'choices' =>[
                'Masculino'=>'Masculino',
                'Femenino'=>'Femenino'
            ],
        ]
        );
        

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $password=$usuario->getPassword();
            $encoded=$encoder->encodePassword($usuario,$password);
            $usuario->setPassword($encoded);


            $cuenta=$this->getDoctrine()->getRepository(Cuenta::class)->find($getcuenta);
            $usuarioCuenta=new UsuarioCuenta();
        
            

            
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
            }

            return $this->redirectToRoute('clientes_index');
        }

        return $this->render('clientes/new.html.twig', [
            'agendador' => $usuario,
            'form' => $form->createView(),
            'pagina'=>$pagina->getNombre(),
            'cuentas'=>$cuentas,
        ]);
    }

    /**
     * @Route("/{id}", name="clientes_show", methods={"GET"})
     */
    public function show(Usuario $usuario,ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','clientes');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('clientes',$user->getEmpresaActual());
        return $this->render('usuario/show.html.twig', [
            'usuario' => $usuario,
            'pagina'=>$pagina->getNombre(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="clientes_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, 
                        Usuario $usuario,
                        UsuarioTipoRepository $usuarioTipoRepository,
                        UserPasswordEncoderInterface $encoder,
                        ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','clientes');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('clientes',$user->getEmpresaActual());
        $empresa=$this->getDoctrine()->getRepository(Empresa::class)->find($user->getEmpresaActual());
        $usuarioCuenta=$this->getDoctrine()->getRepository(UsuarioCuenta::class)->findOneBy(['usuario'=>$usuario->getId()]);
   
        $usuarioCategorias=$empresa->getUsuarioCategorias();
        $cuentas=$empresa->getCuentas();

        $form = $this->createForm(UsuarioType::class, $usuario);

        $form->add("password", TextType::class,[
            'attr'=>[
                'style'=>'display:none'
            ],
        ]);
        $form->add('sexo',ChoiceType::class,[
            'choices' =>[
                'Masculino'=>'Masculino',
                'Femenino'=>'Femenino'
            ],
        ]
        );
        $form->add("passwordAnt", TextType::class,[
            'required'   => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            
            $this->getDoctrine()->getManager()->flush();
            $entityManager = $this->getDoctrine()->getManager();
            $getcuentas=$_POST['cboEmpresa'];
            
            foreach($getcuentas as $getcuenta){

                $cuenta=$this->getDoctrine()->getRepository(Cuenta::class)->find($getcuenta);
                
                $usuarioCuenta=new UsuarioCuenta();

                $usuarioCuenta->setCuenta($cuenta);
                $usuarioCuenta->setUsuario($usuario);
                
                $entityManager->persist($usuarioCuenta);
                $entityManager->flush();
            }
           
            if($usuario->getPasswordAnt()!=""){
                $password=$usuario->getPasswordAnt();
                $encoded=$encoder->encodePassword($usuario,$password);
                $usuario->setPassword($encoded);
                $usuario->setPasswordAnt("");
            }
            $entityManager->persist($usuario);
            $entityManager->flush();
            
            return $this->redirectToRoute('clientes_index');
        }

        return $this->render('clientes/edit.html.twig', [
            'usuario' => $usuario,
            'form' => $form->createView(),
            'pagina'=>$pagina->getNombre(),
            'cuentas'=>$cuentas,
            'cuentas_sel'=>$usuario->getUsuarioCuentas(),
        ]);
    }
    /**
     * @Route("/{id}/restore", name="clientes_restore", methods={"GET"})
     */
    public function restore(Request $request, Usuario $usuario): Response
    {
        $this->denyAccessUnlessGranted('full','clientes');
      
            $entityManager = $this->getDoctrine()->getManager();
            $usuario->setEstado(1);
            $entityManager->persist($usuario);
            $entityManager->flush();
     

        return $this->redirectToRoute('clientes_index');
    }

    /**
     * @Route("/{id}", name="clientes_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Usuario $usuario): Response
    {
        $this->denyAccessUnlessGranted('full','clientes');
        if ($this->isCsrfTokenValid('delete'.$usuario->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $usuario->setEstado(0);
            $entityManager->persist($usuario);
            $entityManager->flush();
        }

        return $this->redirectToRoute('clientes_index');
    }
}
