<?php

namespace App\Controller;

use App\Repository\ConfiguracionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/mantencion_morosidad_pat")
 */
class MantencionMorosidadPatController extends AbstractController
{
    /**
     * @Route("/", name="mantencion_morosidad_pat_index", methods={"GET","POST"})
     */
    public function index(Request $request, ConfiguracionRepository $configuracionRepository): Response
    {
        $this->denyAccessUnlessGranted('view','mantencion_morosidad_pat');
            
            $configuracion=$configuracionRepository->find(1);
            $bNombreSegmento=$configuracion->getMorosidadPatNombre();
            $bDiasMorosidad=$configuracion->getDiasMorosidadPat();
            $bColor=$configuracion->getMorosidadPatColor();
            $bIcono=$configuracion->getMorosidadPatIcono();
            if($request->isMethod('POST')){
                $bNombreSegmento=$request->request->get('txtNombreSegmento');
                $bDiasMorosidad=$request->request->get('txtDiasMorosidadPat');
                $bColor=$request->request->get('txtColor');
                $bIcono=$request->request->get('txtIcono');
                
                if($configuracion){
                    $configuracion->setMorosidadPatNombre($bNombreSegmento);
                    $configuracion->setDiasMorosidadPat($bDiasMorosidad);
                    $configuracion->setMorosidadPatColor($bColor);
                    $configuracion->setMorosidadPatIcono($bIcono);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($configuracion);
                    $em->flush();
                }
            }

        return $this->render('mantencion_morosidad_pat/index.html.twig', [
            'pagina'=>'Mantención Morosidad Pat',
            'controller_name' => 'MantencionMorosidadPatController',
            'bNombreSegmento' => $bNombreSegmento,
            'bDiasMorosidad' => $bDiasMorosidad,
            'bColor' => $bColor,
            'bIcono' => $bIcono,
        ]);
    }
}
