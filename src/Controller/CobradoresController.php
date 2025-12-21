<?php

namespace App\Controller;
use App\Entity\Usuario;
use App\Entity\UsuarioCategoria;
use App\Entity\Empresa;
use App\Entity\UsuarioCuenta;
use App\Entity\Cuenta;
use App\Entity\UsuarioStatus;
use App\Entity\UsuarioLote;
use App\Entity\Privilegio;
use App\Entity\PrivilegioTipousuario;
use App\Form\UsuarioType;
use App\Repository\PrivilegioTipousuarioRepository;
use App\Repository\PrivilegioRepository;
use App\Repository\UsuarioRepository;
use App\Repository\UsuarioTipoRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\LotesRepository;
use App\Repository\UsuarioTipoDocumentoRepository;
use App\Repository\UsuarioNoDisponibleRepository;
use App\Repository\ConfiguracionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Knp\Component\Pager\PaginatorInterface;
/**
 * @Route("/cobradores")
 */
class CobradoresController extends AbstractController
{
    /**
     * @Route("/", name="cobradores_index",methods={"GET"})
     */
    public function index(UsuarioRepository $usuarioRepository,
                    ModuloPerRepository $moduloPerRepository,
                    PaginatorInterface $paginator,
                    Request $request): Response
    {
        $this->denyAccessUnlessGranted('view','cobradores');
        $user=$this->getUser();

        $modo=1;
        if($request->query->get('modo')=='trash'){
            $modo=0;
            
        }
        $pagina=$moduloPerRepository->findOneByName('cobradores',$user->getEmpresaActual());
        $query=$usuarioRepository->findByEmpresa($user->getEmpresaActual(),['usuarioTipo'=>12,'estado'=>$modo]);
        $usuarios=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/,
            array('defaultSortFieldName' => 'nombre', 'defaultSortDirection' => 'asc'));

        return $this->render('cobradores/index.html.twig', [
            'usuarios' => $usuarios,
            'pagina'=>$pagina->getNombre(),
            'modo'=>$modo,
        ]);
    }

    /**
     * @Route("/new", name="cobradores_new", methods={"GET","POST"})
     */
    public function new(Request $request,
                        UserPasswordEncoderInterface $encoder,
                        UsuarioTipoRepository $usuarioTipoRepository,
                        ModuloPerRepository $moduloPerRepository,
                        PrivilegioTipousuarioRepository $privilegioTipousuarioRepository,
                        PrivilegioRepository $privilegioRepository,
                        LotesRepository $lotesRepository,
                        ConfiguracionRepository $configuracionRepository,
                        UsuarioTipoDocumentoRepository $tipoDocumento): Response
    {
        $this->denyAccessUnlessGranted('create','cobradores');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('cobradores',$user->getEmpresaActual());
        $configuracion=$configuracionRepository->find(1);

        $usuario = new Usuario();
        $usuario->setEstado(1);
        $empresa=$this->getDoctrine()->getRepository(Empresa::class)->find($user->getEmpresaActual());
        $statues=$this->getDoctrine()->getRepository(UsuarioStatus::class)->findBy(['id'=>[1,2]]);
        
        $cuentas=$empresa->getCuentas();
       
        $statusChoices=array();
        foreach($statues as $status){
            $statusChoices[$status->getNombre()]=$status->getId();
        }
        
        $usuario->setUsuarioTipo($usuarioTipoRepository->find(12));
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
        $form->add("password", TextType::class);
        $form->add("passwordAnt", TextType::class,[
            'required'   => false,
            'attr'=>[
                'style'=>'display:none'
            ],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $password=$usuario->getPassword();
            $encoded=$encoder->encodePassword($usuario,$password);
            $usuario->setPassword($encoded);

           
            $usuarioCuenta=new UsuarioCuenta();
            $usuario->setTipoDocumento($tipoDocumento->find($request->request->get('cboTipoDocumento')));

            
            $usuario->setFechaNacimiento(new \DateTime(date('Y-m-d H:i',strtotime($request->request->get('fecha_nacimiento')))));
            $usuario->setFechaActivacion(new \DateTime(date('Y-m-d H:i',strtotime($request->request->get('fecha_ingreso')))));

           /* $status=$this->getDoctrine()->getRepository(UsuarioStatus::class)->find($request->request->get('cboStatues'));
            $usuario->setStatus($status);*/
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
            $lotes=$_POST['cboLotes'];
            
            
            foreach($lotes as $lote){
                
                $usuarioLote=new UsuarioLote();            
                $usuarioLote->setUsuario($usuario);
                $usuarioLote->setLote($lotesRepository->find($lote));

                $entityManager->persist($usuarioLote);
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
   

            return $this->redirectToRoute('cobradores_index');
        }

        return $this->render('cobradores/new.html.twig', [
            'usuario' => $usuario,
            'form' => $form->createView(),
            'pagina'=>$pagina->getNombre(),
            'cuentas'=>$cuentas,
            'lotes'=>$lotesRepository->findHabilitados(),
            'statues'=>$statues,
            'tipo_documentos'=>$tipoDocumento->findAll(),
        ]);
    }

    /**
     * @Route("/{id}", name="cobradores_show", methods={"GET"})
     */
    public function show(Usuario $usuario,ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','cobradores');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('cobradores',$user->getEmpresaActual());
        return $this->render('usuario/show.html.twig', [
            'usuario' => $usuario,
            'pagina'=>$pagina->getNombre(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="cobradores_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, 
                        Usuario $usuario,
                        UsuarioTipoRepository $usuarioTipoRepository,
                        ModuloPerRepository $moduloPerRepository,
                        UsuarioTipoDocumentoRepository $tipoDocumento,
                        UserPasswordEncoderInterface $encoder,
                        LotesRepository $lotesRepository,
                        ConfiguracionRepository $configuracionRepository,
                        UsuarioNoDisponibleRepository $usuarioNoDisponibleRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','cobradores');
        $user=$this->getUser();
        $configuracion=$configuracionRepository->find(1);
        $pagina=$moduloPerRepository->findOneByName('cobradores',$user->getEmpresaActual());
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


            $usuarioLotes=$usuario->getUsuarioLotes();
            foreach($usuarioLotes as $usuarioLote){
                
                $entityManager->remove($usuarioLote);
                $entityManager->flush();
            }
            $lotes=$_POST['cboLotes'];
            
            
            foreach($lotes as $lote){
                
                $usuarioLote=new UsuarioLote();            
                $usuarioLote->setUsuario($usuario);
                $usuarioLote->setLote($lotesRepository->find($lote));

                $entityManager->persist($usuarioLote);
                $entityManager->flush();
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
            
            return $this->redirectToRoute('cobradores_index');
        }

        return $this->render('cobradores/edit.html.twig', [
            'usuario' => $usuario,
            'form' => $form->createView(),
            'pagina'=>$pagina->getNombre(),
            'cuentas'=>$cuentas,
            'lotes'=>$lotesRepository->findHabilitados(),
            'statues'=>$statues,
            'id_cuenta'=>$usuarioCuenta->getCuenta()->getId(),
            'cuentas_sel'=>$usuario->getUsuarioCuentas(),
            'tipo_documentos'=>$tipoDocumento->findAll(),
            'hora_inicio'=>$horaInicio,
            'hora_fin'=>$horaFin
            
        ]);
    }
    /**
     * @Route("/{id}/restore", name="cobradores_restore", methods={"GET"})
     */
    public function restore(Request $request, Usuario $usuario): Response
    {
        $this->denyAccessUnlessGranted('full','cobradores');
      
            $entityManager = $this->getDoctrine()->getManager();
            $usuario->setEstado(1);
            $entityManager->persist($usuario);
            $entityManager->flush();
     

        return $this->redirectToRoute('cobradores_index');
    }

    /**
     * @Route("/{id}", name="cobradores_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Usuario $usuario): Response
    {
        $this->denyAccessUnlessGranted('full','cobradores');
        if ($this->isCsrfTokenValid('delete'.$usuario->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $usuario->setEstado(0);
            $usuarioLotes=$usuario->getUsuarioLotes();
            foreach($usuarioLotes as $usuarioLote){
                $entityManager->remove($usuarioLote);
               // $entityManager->flush();
            }
            $entityManager->persist($usuario);
            $entityManager->flush();
        }

        return $this->redirectToRoute('cobradores_index');
    }
}
