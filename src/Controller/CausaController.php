<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\Causa;
use App\Entity\MateriaEstrategia;
use App\Repository\CausaRepository;
use App\Repository\CuentaRepository;
use App\Repository\JuzgadoCuentaRepository;
use App\Repository\JuzgadoRepository;
use App\Repository\MateriaEstrategiaRepository;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/causa")
 */
class CausaController extends AbstractController
{
    /**
     * @Route("/", name="causa_index")
     */
    public function index(): Response
    {
        return $this->render('causa/index.html.twig', [
            'controller_name' => 'CausaController',
        ]);
    }

    /**
     * @Route("/{id}/new", name="causa_new", methods={"GET"})
     */
    public function agregar(Agenda $agenda,
                            Request $request, 
                            MateriaEstrategiaRepository $materiaEstrategiaRepository,
                            JuzgadoRepository $juzgadoRepository){
        $entityManager = $this->getDoctrine()->getManager();
        $causa=new Causa();
        $causa->setEstado(1);
        $causa->setAgenda($agenda);
        /*if(null !== $request->query->get('txtNombreCausa')){
            $causa->setIdCausa($request->query->get('txtNombreCausa'));
        }*/
        if(( $request->query->get('txtLetra')!="") && ( $request->query->get('txtRol')!="")  && ($request->query->get('txtAnio')!="")){
            $causa->setLetra($request->query->get('txtLetra'));
            $causa->setRol($request->query->get('txtRol')); 
            $causa->setAnio($request->query->get('txtAnio'));
        }

        if(null !== $request->query->get('txtCaratulado')){
            $causa->setCausaNombre($request->query->get('txtCaratulado'));
        }
        if(null !== $request->query->get('cboSubMateria')){
            $causa->setMateriaEstrategia($materiaEstrategiaRepository->find($request->query->get('cboSubMateria')));
        }
        if(null !== $request->query->get('juzgado')){
            $juzgado= $juzgadoRepository->find($request->query->get('juzgado'));
            $causa->setJuzgado($juzgado);
            if($juzgado){                
                if($juzgado->getCorte()!=null){
                    $causa->setCorte($juzgado->getCorte());
                }
            }
            
        }
        
        $entityManager->persist($causa);
        $entityManager->flush();

        return $this->render('causa/index.html.twig');

    }
     /**
     * @Route("/{id}/list", name="causa_list", methods={"GET"})
     */
    public function list(Agenda $agenda,
                            Request $request, 
                            CausaRepository $causaRepository,
                            MateriaEstrategiaRepository $materiaEstrategiaRepository,
                            JuzgadoCuentaRepository $juzgadoCuentaRepository){
        
        $causas=$causaRepository->findBy(['agenda'=>$agenda->getId(),'estado'=>true]);

        return $this->render('causa/list.html.twig', [
            'causas' => $causas,
            
        ]);

    }

     /**
     * @Route("/{id}/delete", name="causa_delete", methods={"GET"})
     */
    public function delete(Causa $causa,
                            Request $request, 
                            CausaRepository $causaRepository,
                            MateriaEstrategiaRepository $materiaEstrategiaRepository,
                            JuzgadoCuentaRepository $juzgadoCuentaRepository){
        $entityManager = $this->getDoctrine()->getManager();

        $causa->setEstado(false);

        $entityManager->persist($causa);
        $entityManager->flush();
        
       
        return $this->render('causa/index.html.twig');

    }
}
