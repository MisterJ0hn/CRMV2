<?php

namespace App\Controller;

use App\Entity\Empresa;
use App\Entity\Cuenta;
use App\Entity\Accion;
use App\Entity\MenuCabezera;
use App\Entity\Menu;
use App\Entity\Privilegio;
use App\Entity\PrivilegioTipousuario;
use App\Entity\ModuloPer;
use App\Entity\UsuarioTipo;
use App\Entity\UsuarioCuenta;
use App\Entity\UsuarioCategoria;
use App\Repository\AccionRepository;
use App\Repository\CuentaRepository;
use App\Repository\ModuloRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\UsuarioRepository;
use App\Repository\UsuarioTipoRepository;
use App\Form\EmpresaType;
use App\Repository\EmpresaRepository;
use App\Repository\MenuRepository;
use App\Repository\PrivilegioTipousuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
/**
 * @Route("/empresa")
 */
class EmpresaController extends AbstractController
{
    /**
     * @Route("/", name="empresa_index", methods={"GET","POST"})
     */
    public function index(EmpresaRepository $empresaRepository,AccionRepository $accionRepository, ModuloPerRepository $moduloPerRepository,Request $request): Response
    {
        $this->denyAccessUnlessGranted('view','empresa');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('empresa',$user->getEmpresaActual());
        $fechas="";
        $json_criterio='{';
        if($request->request->get('txtFechaBusqueda')){
            $fechas=explode(" - ",$request->request->get('txtFechaBusqueda'));
            $json_criterio.='"fechaIngreso >=":"'.date("Y-m-d",strtotime($fechas[0])).'","fechaIngreso <=":"'.date("Y-m-d",strtotime($fechas[1])).'",';
        }
        if($request->request->get('txtFechaVigencia')){
            $fechas=explode(" - ",$request->request->get('txtFechaVigencia'));
            $json_criterio.='"fechaVigencia >=":"'.date("Y-m-d",strtotime($fechas[0])).'","fechaVigencia <=":"'.date("Y-m-d",strtotime($fechas[1])).'",';
        }
        
        $json_criterio.='"nombre like ":"%'.$request->request->get('txtNombre').'%"';
        $json_criterio.='}';
    
        return $this->render('empresa/index.html.twig', [
            'empresas' => $empresaRepository->findByBusqueda($json_criterio),
            'pagina'=>$pagina->getNombre(),
            'rngFechaIngreso'=>$request->request->get('txtFechaBusqueda'),
            'rngFechaVigencia'=>$request->request->get('txtFechaVigencia'),
            'nombre'=>$request->request->get('txtNombre'),
        ]);
    }

    /**
     * @Route("/new", name="empresa_new", methods={"GET","POST"})
     */
    public function new(Request $request,
                    AccionRepository $accionRepository,
                    CuentaRepository $cuentaRepository, 
                    ModuloRepository $moduloRepository,
                    ModuloPerRepository $moduloPerRepository,
                    UsuarioRepository $usuarioRepository,
                    UsuarioTipoRepository $usuarioTipoRepository,
                   
                    MenuRepository $menuRepository,
                    PrivilegioTipousuarioRepository $privilegioTipousuarioRepository): Response
    {
        $this->denyAccessUnlessGranted('create','empresa');
        $empresa = new Empresa();
        $empresa->setFechaIngreso(new \DateTime(date('Y-m-d H:i:s')));
        $form = $this->createForm(EmpresaType::class, $empresa);
        $form->add('fechaVigencia',DateType::class,array('widget'=>'single_text','html5'=>false));
        $form->handleRequest($request);
        
        

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Logo $logoFile */
            $logoFile = $form['logo']->getData();
            //$brochureFile = $_FILES['file']['name'][0];
            
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
           // if ($brochureFile) {


                /*$fichero_subido = $this->getParameter('url_root').
                $this->getParameter('logos_empresa') . basename($_FILES['file']['name'][0]);*/
            
            // This condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($logoFile) {
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                // This is needed to safely include the file name as part of the URL
                // Enable "Intl" extension in "php.ini"
                // https://stackoverflow.com/questions/33869521/how-can-i-enable-php-extension-intl
                //$safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$logoFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $logoFile->move(
                        $this->getParameter('logos_empresa'),
                        $newFilename
                    );

                  
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    echo $e->getMessage();
                    exit;
                }

                // Updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents

                /*$source=$_FILES['file']['tmp_name'][0];
                $imgInfo = getimagesize($source); 
                
                $mime = $imgInfo['mime']; 
                
                // Creamos una imagen
                switch($mime){ 
                    case 'image/jpeg': 
                        $image = imagecreatefromjpeg($source); 
                        break; 
                    case 'image/png': 
                        $image = imagecreatefrompng($source); 
                        break; 
                    case 'image/gif': 
                        $image = imagecreatefromgif($source); 
                        break; 
                    default: 
                        $image = imagecreatefromjpeg($source); 
                } 

                $quality=100;
                if(filesize($_FILES['file']['tmp_name'][0])>1000000){
                    $quality=75;
                }
                if(filesize($_FILES['file']['tmp_name'][0])>2000000){
                    $quality=60;
                }
                if(filesize($_FILES['file']['tmp_name'][0])>3000000){
                    $quality=50;
                }
                if(filesize($_FILES['file']['tmp_name'][0])>4000000){
                    $quality=40;
                }
                // Guardamos la imagen
                imagejpeg($image, $fichero_subido, $quality); */
                $empresa->setLogo($newFilename);
            }


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($empresa);
            $entityManager->flush();

            //Creando acciones
            $accion=new Accion();
            $accion->setNombre('Ver');
            $accion->setAccion('view');
            $accion->setEmpresa($empresa);
            
            $entityManager->persist($accion);
            $entityManager->flush();

            $accion=new Accion();
            $accion->setNombre('Editar');
            $accion->setAccion('edit');
            $accion->setEmpresa($empresa);
            $entityManager->persist($accion);
            $entityManager->flush();

            $accion=new Accion();
            $accion->setNombre('Crear');
            $accion->setAccion('create');
            $accion->setEmpresa($empresa);
            $entityManager->persist($accion);
            $entityManager->flush();

            $accion=new Accion();
            $accion->setNombre('Todo');
            $accion->setAccion('Full');
            $accion->setEmpresa($empresa);
            $entityManager->persist($accion);
            $entityManager->flush();

            $accion=new Accion();
            $accion->setNombre('Eliminar');
            $accion->setAccion('none');
            $accion->setEmpresa($empresa);
            $entityManager->persist($accion);
            $entityManager->flush();

            $cuenta=new Cuenta();
            $cuenta->setNombre('Predeterminada');
            $cuenta->setFechaCreacion(new \DateTime(date("Y-m-d h:i:s")));
            $cuenta->setEmpresa($empresa);

            $entityManager->persist($cuenta);
            $entityManager->flush();

            $usuarioCategoria=new UsuarioCategoria();
            $usuarioCategoria->setEmpresa($empresa);
            $usuarioCategoria->setNombre('Nivel 1');
            $usuarioCategoria->setNLeads(1);
            
            $entityManager->persist($usuarioCategoria);
            $entityManager->flush();


            
            // //creacion de perfiles standar
            // $usuarioTipo=new UsuarioTipo();
            // $usuarioTipo->setNombre('Administracion de empresa');
            // $usuarioTipo->setNombreInterno('managed_company');
            // $usuarioTipo->setFijar(1);
            // $usuarioTipo->setMenuCabezera($menuCabezera);
            // $usuarioTipo->setEmpresa($empresa);

            // $entityManager->persist($usuarioTipo);
            // $entityManager->flush();

            // $usuarioTipo=new UsuarioTipo();
            // $usuarioTipo->setNombre('Administracion de cuenta');
            // $usuarioTipo->setNombreInterno('managed_account');
            // $usuarioTipo->setFijar(1);
            // $usuarioTipo->setEmpresa($empresa);

            // $entityManager->persist($usuarioTipo);
            // $entityManager->flush();

            // $usuarioTipo=new UsuarioTipo();
            // $usuarioTipo->setNombre('Jefe de proceso');
            // $usuarioTipo->setNombreInterno('process_manager');
            // $usuarioTipo->setFijar(1);
            // $usuarioTipo->setEmpresa($empresa);

            // $entityManager->persist($usuarioTipo);
            // $entityManager->flush();

            // $usuarioTipo=new UsuarioTipo();
            // $usuarioTipo->setNombre('Jefe de abogado');
            // $usuarioTipo->setNombreInterno('chief_lawyer');
            // $usuarioTipo->setFijar(1);
            // $usuarioTipo->setEmpresa($empresa);

            // $entityManager->persist($usuarioTipo);
            // $entityManager->flush();

            // $usuarioTipo=new UsuarioTipo();
            // $usuarioTipo->setNombre('Agendador');
            // $usuarioTipo->setNombreInterno('scheduler');
            // $usuarioTipo->setFijar(1);
            // $usuarioTipo->setEmpresa($empresa);

            // $entityManager->persist($usuarioTipo);
            // $entityManager->flush();

            // $usuarioTipo=new UsuarioTipo();
            // $usuarioTipo->setNombre('Abogado');
            // $usuarioTipo->setNombreInterno('lawyer');
            // $usuarioTipo->setFijar(1);
            // $usuarioTipo->setEmpresa($empresa);

            // $entityManager->persist($usuarioTipo);
            // $entityManager->flush();

            // $usuarioTipo=new UsuarioTipo();
            // $usuarioTipo->setNombre('Cliente');
            // $usuarioTipo->setNombreInterno('client');
            // $usuarioTipo->setFijar(1);
            // $usuarioTipo->setEmpresa($empresa);

            // $entityManager->persist($usuarioTipo);
            // $entityManager->flush();

            $usuarioTipo_managedcompany=$usuarioTipoRepository->findOneBy(['nombreInterno'=>'sys_admin']);
            $usuario=$usuarioRepository->find(1);
            $usuario->setUsuarioTipo($usuarioTipo_managedcompany);
            $entityManager->persist($usuario);
            $entityManager->flush();
            

            $usuarioCuenta=new UsuarioCuenta();
            $usuarioCuenta->setCuenta($cuenta);
            $usuarioCuenta->setUsuario($usuario);

            $entityManager->persist($usuarioCuenta);
            $entityManager->flush();

            //Creacion de Modulos
            $modulos=$moduloRepository->findAll();

            foreach($modulos as $modulo){
                $moduloPer=$moduloPerRepository->findOneBy(['empresa'=>$empresa->getId(),'modulo'=>$modulo->getId()]);
                if(!$moduloPer){
                    $moduloNew = new ModuloPer();
                    $moduloNew->setEmpresa($empresa);
                    $moduloNew->setModulo($modulo);
                    $moduloNew->setNombre($modulo->getNombreAlt());
                    $moduloNew->setDescripcion($modulo->getDescripcion());
                    
                    $entityManager->persist($moduloNew);
                    $entityManager->flush();

                    $privilegioTipousuario = new PrivilegioTipousuario();
                    $selModulo=$request->request->get('selModulo');
                   
                    $accion=$accionRepository->findOneBy(['empresa'=>$empresa->getId(),'accion'=>"full"]);
                    $privilegioTipousuario->setModuloPer($moduloNew);
                    $privilegioTipousuario->setTipousuario($usuarioTipo_managedcompany);
                    $privilegioTipousuario->setAccion($accion);
                    $entityManager->persist($privilegioTipousuario);
                    $entityManager->flush();

                    $privilegioNew=new Privilegio();
                    $privilegioNew->setUsuario($usuario);
                    $privilegioNew->setModuloPer($moduloNew);
                    $privilegioNew->setAccion($accion);

                    $entityManager->persist($privilegioNew);
                    $entityManager->flush();

                }
            }

            $privilegiosTipousuarios=$privilegioTipousuarioRepository->findByEmpresa(1);
            foreach ($privilegiosTipousuarios as $privilegioTipousuario) {
                if($privilegioTipousuario->getTipousuario()->getId()!=8){
                    $privilegioNew=new PrivilegioTipousuario();
                    
                    $privilegioNew->setTipousuario($privilegioTipousuario->getTipousuario());

                    $moduloPerAnterior=$privilegioTipousuario->getModuloPer();

                    $moduloOriginal=$moduloPerAnterior->getModulo();

                    $moduloPerActual=$moduloPerRepository->findOneBy(['empresa'=>$empresa->getId(),'modulo'=>$moduloOriginal]);
                    $privilegioNew->setModuloPer($moduloPerActual);

                    $accion=$accionRepository->findOneBy(['empresa'=>$empresa->getId(),'accion'=>$privilegioTipousuario->getAccion()->getAccion()]);

                    $privilegioNew->setAccion($accion);

                    $entityManager->persist($privilegioNew);
                    $entityManager->flush();

                }
                
            }

            $menus=$menuRepository->findBy(['empresa'=>1]);
            foreach($menus as $menu){
                $menuNew=new Menu();

                $menuNew->setEmpresa($empresa);
                $menuNew->setDependeDe($menu->getDependeDe());
                $menuNew->setIcono($menu->getIcono());
                $menuNew->setMenuCabezera($menu->getMenuCabezera());
                $menuNew->setNombre($menu->getNombre());
                $menuNew->setModulo($menu->getModulo());
                $menuNew->setOrden($menu->getOrden());

                $entityManager->persist($menuNew);
                $entityManager->flush();
            }


            return $this->redirectToRoute('empresa_index');
        }

        return $this->render('empresa/new.html.twig', [
            'empresa' => $empresa,
            'form' => $form->createView(),
            'pagina'=>'Nueva empresa',
        ]);
    }

    /**
     * @Route("/{id}", name="empresa_show", methods={"GET"})
     */
    public function show(Empresa $empresa): Response
    {
        $this->denyAccessUnlessGranted('view','empresa');
        return $this->render('empresa/show.html.twig', [
            'empresa' => $empresa,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="empresa_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Empresa $empresa): Response
    {
        $this->denyAccessUnlessGranted('edit','empresa');
        $empresa->setLogoAlt($empresa->getLogo());
        $form = $this->createForm(EmpresaType::class, $empresa);
        $form->add('fechaVigencia',DateType::class,array('widget'=>'single_text','html5'=>false));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logoFile=$form['logo']->getData();
            if($logoFile==''){
                $empresa->setLogo($empresa->getLogoAlt());
            }else{
                // This condition is needed because the 'brochure' field is not required
                // so the PDF file must be processed only when a file is uploaded
                if ($logoFile) {
                    $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                    // This is needed to safely include the file name as part of the URL
                    // Enable "Intl" extension in "php.ini"
                    // https://stackoverflow.com/questions/33869521/how-can-i-enable-php-extension-intl
                    //$safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                    $safeFilename=$originalFilename;
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$logoFile->guessExtension();

                    // Move the file to the directory where brochures are stored
                    try {
                        $logoFile->move(
                            $this->getParameter('logos_empresa'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                        
                    }

                    // Updates the 'brochureFilename' property to store the PDF file name
                    // instead of its contents
                    $empresa->setLogo($newFilename);
                }
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('empresa_index');
        }

        return $this->render('empresa/edit.html.twig', [
            'empresa' => $empresa,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="empresa_delete", methods={"DELETE"})
     */
    public function delete(Request $request, 
                            Empresa $empresa,
                            EmpresaRepository $empresaRepository,
                            MenuRepository $menuRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','empresa');
        if ($this->isCsrfTokenValid('delete'.$empresa->getId(), $request->request->get('_token'))) {
            echo $empresa->getNombre();
            $entityManager = $this->getDoctrine()->getManager();
            
            foreach ($empresa->getCuentas() as $cuenta) {
                foreach ($cuenta->getAgendas() as $agenda) {
                    $contrato=$agenda->getContrato();

                    foreach ($contrato->getTickets() as $ticket) {
                        $entityManager->remove($ticket);
                        $entityManager->flush();
                    }
                    $entityManager->remove($contrato);
                    $entityManager->flush();
                
                    $entityManager->remove($agenda);
                    $entityManager->flush();

                }

                //Eliminando Usuarios

               foreach ($cuenta->getUsuarioCuentas() as $usuarioCuenta) {

                    //$usuario=$usuarioCuenta->getUsuario();
                     /*foreach ($usuario->getUsuarioCarteras() as $usuarioCartera) {
                        $entityManager->remove($usuarioCartera);
                        $entityManager->flush();
                    }

                    foreach ($usuario->getUsuarioLotes() as $usuarioLote) {
                        $entityManager->remove($usuarioLote);
                        $entityManager->flush();
                    }

                    foreach ($usuario->getUsuarioNoDisponibles() as $noDisponible) {
                        $entityManager->remove($noDisponible);
                        $entityManager->flush();
                    }

                    foreach ($usuario->getUsuarioUsuariocategorias() as $usuarioCategoria) {
                        $entityManager->remove($usuarioCategoria);
                        $entityManager->flush();
                    }

                    foreach ($usuario->getPrivilegios() as $privilegio) {
                        $entityManager->remove($privilegio);
                        $entityManager->flush();
                    }

                    foreach ($usuario->getUsuarioTipo()->getPrivilegioTipousuarios() as $privilegioTipoUsuario) {
                        $entityManager->remove($privilegioTipoUsuario);
                        $entityManager->flush();
                    }
                    */
                    $entityManager->remove($usuarioCuenta);
                    $entityManager->flush();
                }

                foreach ($cuenta->getJuzgadoCuentas() as $juzgadoCuenta) {
                    $entityManager->remove($juzgadoCuenta);
                    $entityManager->flush();
                }

                $entityManager->remove($cuenta);
                $entityManager->flush();



            }


            $menus=$menuRepository->findBy(['empresa'=>$empresa]);

            foreach ($menus as $menu) {
                $entityManager->remove($menu);
                $entityManager->flush();
            }
            
            foreach ($empresa->getAcciones() as $accion) {
                $entityManager->remove($accion);
                $entityManager->flush();
            }

            
            
           
            foreach ($empresa->getEstrategiaJuridicas() as $estrategiaJuridica) {
                $entityManager->remove($estrategiaJuridica);
                $entityManager->flush();
            }
            foreach ($empresa->getEscrituras() as $escritura) {
                $entityManager->remove($escritura);
                $entityManager->flush();
            }
    
            foreach ($empresa->getUsuarioCategorias() as $usuarioCategoria) {
                $entityManager->remove($usuarioCategoria);
                $entityManager->flush();
            }

            foreach ($empresa->getModuloPers() as $moduloPer) {

                foreach ($moduloPer->getPrivilegios() as $privilegio) {
                    $entityManager->remove($privilegio);
                    $entityManager->flush();
                }

                foreach ($moduloPer->getPrivilegioTipousuarios() as $privilegioTipoUsuario) {
                    $entityManager->remove($privilegioTipoUsuario);
                    $entityManager->flush();
                }
                $entityManager->remove($moduloPer);
                $entityManager->flush();
            }


            //$empresaDos=$empresaRepository->find($empresa->getId());
            $entityManager->remove($empresa);
            $entityManager->flush();
        }

        return $this->redirectToRoute('empresa_index');
    }
}
