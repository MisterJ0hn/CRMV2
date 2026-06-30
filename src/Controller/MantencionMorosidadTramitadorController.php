<?php

namespace App\Controller;

use App\Repository\ConfiguracionRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mantencion_morosidad_tramitador")
 */
class MantencionMorosidadTramitadorController extends AbstractController
{
    /**
     * @Route("/", name="mantencion_morosidad_tramitador_index", methods={"GET","POST"})
     */
    public function index(Request $request, ConfiguracionRepository $configuracionRepository): Response
    {
        $this->denyAccessUnlessGranted('view','mantencion_morosidad_tramitador');
        $user=$this->getUser();
        $configuracion=$configuracionRepository->find(1);

        if(null !== $request->request->get('txtDiasMorosidad')){
            $configuracion->setMorosidadTramitadorMax($request->request->get('txtDiasMorosidad'));
        }

        return $this->render('mantencion_morosidad_tramitador/index.html.twig', [
            'pagina' => 'Mantención de Morosidad Tramitador',
            'morosidad' => $configuracion->getMorosidadTramitadorMax()
        ]);
    }
}
