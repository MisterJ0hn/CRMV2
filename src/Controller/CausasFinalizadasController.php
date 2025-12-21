<?php

namespace App\Controller;

use App\Entity\Importacion;
use App\Form\ImportacionType;
use App\Repository\CuentaRepository;
use App\Repository\ImportacionRepository;
use App\Repository\UsuarioRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/causas_finalizadas")
 */
class CausasFinalizadasController extends AbstractController
{
    /**
     * @Route("/", name="causas_finalizadas_index",methods={"GET","POST"})
     */
    public function index(Request $request,UsuarioRepository $usuarioRepository,ImportacionRepository $importacionRepository,CuentaRepository $cuentaRepository,KernelInterface $kernel): Response
    {

        $this->denyAccessUnlessGranted('view','causas_finalizadas');
        $user=$this->getUser();

        $importacion = new Importacion();
        $importacion->setFechaCarga(new \DateTime(date("Y-m-d H:i:s")));
        $importacion->setCuenta($cuentaRepository->find(1));
        $form = $this->createForm(ImportacionType::class, $importacion);
        $form->add('cuenta');
        $form->handleRequest($request);
        
        $importaciones=$importacionRepository->findBy(['tipoImportacion'=>1,'usuarioCarga'=>$user->getId()],['id'=>'Desc'],3);

       if ($form->isSubmitted() && $form->isValid()) {
      
            /** @var UploadedFile $brochureFile */
           /* $brochureFile = $form->get('url')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                //$safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',$originalFilename);
                $safeFilename =$originalFilename;
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('csv_importacion'),
                        $newFilename
                    );
                     */
                    $importacion->setNombre('importacion');
                    $importacion->setUrl($this->getParameter('csv_importacion'));
                    $importacion->setUsuarioCarga($usuarioRepository->find($user->getId()));
                    $importacion->setEstado(0);
                    $importacion->setTipoImportacion(1);
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($importacion);
                    $entityManager->flush();
                   

                  /*  $application = new Application($kernel);
                    $application->setAutoExit(false);

                    $input = new ArrayInput(array(
                        'command' => 'app:netear-causas',
                        'importacion'=>$importacion->getId(),
                    ));
                    

                    // Use the NullOutput class instead of BufferedOutput.
                    $output = new NullOutput();

                    $application->run($input, $output);*/

                    shell_exec("cd ". $this->getParameter('url_raiz')."; php74 bin/console app:netear-causas ".$importacion->getId()."  > /dev/null 2>&1 &");
                
                /*
                    $fp = fopen($importacion->getUrl(), "r");
                    $i=0;
                    $paso=true;
                    $mensajeError="";

                    while (!feof($fp)){
                        $linea = fgets($fp);
                        $datos=explode(";",$linea);
                        if ($i==0){
                            $i++;
                            continue;
                        }
                        $i++;
                        
                        //if($datos[0]=="") break;
                        if($datos[3]!="SIN FOLIO" and $datos[3]!="0"){

                       
                        $folio=$datos[3];
                        echo $folio." <br />";

                        }
                        
                    }
                    fclose($fp);*/

                /*
                }catch(Exception $e){
                    echo " ERROR <br>";
                    echo $e->getMessage();
                }*/

                return $this->redirectToRoute('causas_finalizadas_index');
            //}
          

       }
        return $this->render('causas_finalizadas/index.html.twig', [
            'controller_name' => 'CausasFinalizadasController',
            'form' => $form->createView(),
            'importacion' => $importacion,
            'importaciones'=> $importaciones,
        ]);
    }
    
}
