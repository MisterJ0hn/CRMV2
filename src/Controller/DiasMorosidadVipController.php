<?php

namespace App\Controller;

use App\Entity\Modulo;
use App\Repository\ConfiguracionRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\ModuloRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/dias_morosidad_vip")
 */
class DiasMorosidadVipController extends AbstractController
{
    /**
     * @Route("/", name="dias_morosidad_vip_index", methods={"GET","POST"})
     */
    public function index(ConfiguracionRepository $configuracionRepository, 
                        ModuloPerRepository $moduloPerRepository,
                        Request $request): Response
    {
        $this->denyAccessUnlessGranted('view','dias_morosidad_vip'); 
        $user=$this->getUser();
        $pagina = $moduloPerRepository->findOneByName('dias_morosidad_vip',$user->getEmpresaActual());
        $configuracion = $configuracionRepository->find(1);
        if($request->request->get('txtDiasMorosidadVip')){
            $entityManager = $this->getDoctrine()->getManager();
            $configuracion->setDiasMorisidadVip($request->request->get('txtDiasMorosidadVip'));
            $entityManager->persist($configuracion);
            $entityManager->flush();    
        }

        return $this->render('dias_morisidad_vip/index.html.twig', [
            'pagina' => $pagina->getNombre(),
            'bDiasMorosidad'=>$configuracion->getDiasMorisidadVip()
        ]);
    }
}
