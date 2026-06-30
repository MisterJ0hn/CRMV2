<?php

namespace App\Controller;

use App\Entity\Movimientospjud;
use App\Form\MovimientospjudType;
use App\Repository\MovimientospjudRepository;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
 /**
 * @Route("/movimientospjud")
 */
class MovimientospjudController extends AbstractController
{
    /**
     * @Route("/", name="movimientospjud_index", methods={"GET","POST"})
     */
    public function index(Request $request,
                        MovimientospjudRepository $movimientospjudRepository,
                        PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('view','movimientospjud');
        $user=$this->getUser();
       // $pagina=$moduloPerRepository->findOneByName('importacion',$user->getEmpresaActual());
        $pjud = new Movimientospjud();

        $pjud->setFechaCarga(new \DateTime(date("Y-m-d H:i:s")));
        $pjud->setUsuarioRegistro($user);
        $form = $this->createForm(MovimientospjudType::class, $pjud);
        $form->handleRequest($request);

       if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('archivo')->getData();
             // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                //$safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',$originalFilename);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter("url_root").
                        $this->getParameter('archivos_pjud'),
                        $newFilename
                    );
                    $array_originalFilename = explode("_",$originalFilename);

                    $dia = $array_originalFilename[1];
                    $mes = $array_originalFilename[2];
                    $anio_array = explode("-", $array_originalFilename[3]);

                    $anio_array_espacio=explode(" ",$anio_array[0]);

                    if(count($anio_array_espacio)>1){
                        $anio=$anio_array_espacio[0];
                    }else{
                        $anio =$anio_array[0];
                    }
                    

                    $fechaPJUD=new DateTime(date("Y-m-d",strtotime($anio."-".$mes."-".$dia)));
                    $pjud->setArchivo($newFilename);
                    $pjud->setFechaPjud($fechaPJUD);
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($pjud);
                    $entityManager->flush();
                }catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    return $this->render('movimientospjud/index.html.twig', [
                        'pjud' => $pjud,
                        'form' => $form->createView(),
                        'pjuds'=> $movimientospjudRepository->findAll(),
                        'pagina'=>"Movimientos PJUD",
                        'error'=>'<div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-ban"></i>Error!</h5>
                        Existe un error en la carga, favor revisar tu fichero.
                      </div>',
                    ]);
                }
            }
        }
        $query=$movimientospjudRepository->findAll();
        $pjuds=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            100 /*limit per page*/,
            array('defaultSortFieldName' => 'fechaPjud', 'defaultSortDirection' => 'desc'));

        return $this->render('movimientospjud/index.html.twig', [
            'pagina' => 'Movimientos PJUD',
            'form' => $form->createView(),
            'pjuds'=> $pjuds,
        ]);
    }
}
