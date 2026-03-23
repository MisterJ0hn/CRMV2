<?php

namespace App\Controller;

use App\Entity\Contrato;
use App\Repository\ContratoHistoricoSuscripcionRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\UsuarioRepository;
use App\Repository\VwContratoPagoAutomaticoRepository;
use App\Repository\VwCuotaConEquipoRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/pagos_automaticos")
 */
class PagosAutomaticosController extends AbstractController
{
    /**
     * @Route("/", name="pagos_automaticos_index", methods={"GET"})
     */
    public function index(
        VwContratoPagoAutomaticoRepository $contratoPagoAutomaticoRepository,
        PaginatorInterface $paginator,
        ModuloPerRepository $moduloPerRepository,
        Request $request,
        UsuarioRepository   $usuarioRepository
    ): Response {
        $this->denyAccessUnlessGranted('view', 'pagos_automaticos');
        $user = $this->getUser();
        $pagina = $moduloPerRepository->findOneByName('pagos_automaticos', $user->getEmpresaActual());

        $filtro = null;
        $folio  = null;
        $estado = null;
        $fecha  = null;
        $cerrador = null;
       
        $cerradores = $usuarioRepository->findBy(["usuarioTipo"=>6,"estado"=>1]);

        if (null !== $request->query->get('bFolio') && $request->query->get('bFolio') != '') {
            $folio = $request->query->get('bFolio');
            $dateInicio = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 60 * 60 * 24 * 30);
            $dateFin    = date('Y-m-d');
        } else {
            if (null !== $request->query->get('bFiltro') && $request->query->get('bFiltro') != '') {
                $filtro = $request->query->get('bFiltro');
            }
            if (null !== $request->query->get('bEstado') && $request->query->get('bEstado') != '') {
                $estado = $request->query->get('bEstado');
            }
            if (null !== $request->query->get('bFecha')) {
                $aux_fecha  = explode(' - ', $request->query->get('bFecha'));
                $dateInicio = $aux_fecha[0];
                $dateFin    = $aux_fecha[1];
            } else {
                $dateInicio = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 60 * 60 * 24 * 7);
                $dateFin    = date('Y-m-d');
            }
            if(null !== $request->query->get('bCerrador')){
                $cerrador = $request->query->get('bCerrador');
            }
            $fecha = "c.fechaCreacion between '$dateInicio' and '$dateFin 23:59:59'";
        }

        $query = $contratoPagoAutomaticoRepository->findBySuscripcion($filtro, $folio, $estado, $fecha,$cerrador);
        $total = $contratoPagoAutomaticoRepository->findBySuscripcionTotal($filtro, $folio, $estado, $fecha,$cerrador);
        $totalEstados = $contratoPagoAutomaticoRepository->totalPorEstado($filtro, $folio, $estado, $fecha,$cerrador);
        $totalAbogados = $contratoPagoAutomaticoRepository->totalPorAbogado($filtro, $folio, $estado, $fecha,$cerrador);
      
        $contratos = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20,
            []
        );

        return $this->render('pagos_automaticos/index.html.twig', [
            'contratos'      => $contratos,
            'bFiltro'     => $filtro,
            'bFolio'      => $folio,
            'bEstado'     => $estado,
            'dateInicio'  => $dateInicio,
            'dateFin'     => $dateFin,
            'total'       => $total['total'] ?? 0,
            'pagina'      => $pagina ? $pagina->getNombre() : 'Pagos Automáticos',
            'bCerrador'   => $cerrador,
            'cerradores'  => $cerradores,
            'totalEstados'=>$totalEstados,
            'totalAbogados'=>$totalAbogados
        ]);
    }

    /**
     * @Route("/historico_suscripcion/{id}", name="pagos_automaticos_historico_suscripcion", methods={"GET"})
     */
    public function historicoSuscripcion(Contrato $contrato,
        ContratoHistoricoSuscripcionRepository $contratoHistoricoSuscripcionRepository,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('view', 'pagos_automaticos');
        $suscripciones = $contratoHistoricoSuscripcionRepository->findBy(['contrato' => $contrato->getId()]);

        return $this->render('pagos_automaticos/_historico_suscripcion.html.twig', [
            'suscripciones' => $suscripciones,
            'contrato'     => $contrato
        ]);
    }
    
}
