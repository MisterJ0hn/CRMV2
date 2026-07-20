<?php

namespace App\Controller;

use App\Entity\Contrato;
use App\Repository\CausaObservacionRepository;
use App\Repository\ContratoRepository;
use App\Repository\CuentaRepository;
use App\Repository\DiasPagoRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\VwContratoRepository;
use App\Repository\VwContratosVencidosRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/contratos_vencidos")
 */
class ContratosVencidosController extends AbstractController
{
    /**
     * @Route("/", name="contratos_vencidos_index")
     */
    public function index(VwContratosVencidosRepository $contratoRepository,
                            PaginatorInterface $paginator,
                            ModuloPerRepository $moduloPerRepository,
                            Request $request,
                            CuentaRepository $cuentaRepository): Response
    {

         $this->denyAccessUnlessGranted('view','contratos_vencidos');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('contratos_vencidos',$user->getEmpresaActual());
        $filtro=null;
        $error='';
        $error_toast="";
        $otros="";
        $folio="";
        $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30*24);
        $dateFin=date('Y-m-d');
        $request->getSession()->set('origen_anexo','contratos_vencidos');
        if(null !== $request->query->get('error_toast')){
            $error_toast=$request->query->get('error_toast');
        }
        $compania=null;
        
        switch($user->getUsuarioTipo()->getId()){
            case 3: case 4:  case 1:  case 8:  case 11: case 12: case 14:
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;
            default:
                $companias=$cuentaRepository->findByPers($user->getId());
                break;
        }
        
        return $this->render('contratos_vencidos/index.html.twig', [
           
            'bFiltro'=>'',
            'bFolio'=>'',
            'companias'=>$companias,
            'bCompania'=>null,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'pagina'=>$pagina->getNombre(),
            'error'=>'',
            'error_toast'=>$error_toast,
            'TipoFiltro'=>'Contrato'
        ]);
    }

    /**
     * @Route("/obtenerContenido", name="contratos_vencidos_obtener_contenido", methods={"GET"})
     */
    public function obtenerContenido(ContratoRepository $contratoRepository,
                                    PaginatorInterface $paginator,
                                    Request $request,
                                    CuentaRepository $cuentaRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('view','contratos_vencidos');

        try {
            $user    = $this->getUser();
            $filtro  = null;
            $folio   = null;
            $compania = null;

            // --- Filtros básicos ---
            if (null !== $request->query->get('bFolio') && $request->query->get('bFolio') != '') {
                $folio      = $request->query->get('bFolio');
                $dateInicio = date('Y-m-d', mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30*24);
                $dateFin    = date('Y-m-d');
                $fecha      = "(c.folio = '$folio' OR a.id = '$folio') AND a.status IN (7,14)";
            } elseif (null !== $request->query->get('bFiltro') && $request->query->get('bFiltro') != '') {
                $filtro     = $request->query->get('bFiltro');
                $dateInicio = date('Y-m-d', mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30*24);
                $dateFin    = date('Y-m-d');
                $fecha      = "a.status IN (7,14)";
            } else {
                if (null !== $request->query->get('bCompania') && $request->query->get('bCompania') != 0) {
                    $compania = $request->query->get('bCompania');
                }
                if (null !== $request->query->get('bFecha')) {
                    $aux_fecha  = explode(" - ", $request->query->get('bFecha'));
                    $dateInicio = $aux_fecha[0];
                    $dateFin    = $aux_fecha[1];
                } else {
                    $dateInicio = date('Y-m-d', mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30*24);
                    $dateFin    = date('Y-m-d');
                }
                // Incluye contratos creados en el rango Y contratos con anexos creados en el rango
                $fecha = "((c.fechaCreacion BETWEEN '$dateInicio' AND '$dateFin 23:59:59' AND  TIMESTAMPDIFF(MONTH, c.fechaCreacion, NOW()) > c.vigencia
                    AND NOT EXISTS(SELECT ca FROM App\\Entity\\ContratoAnexo ca WHERE ca.contrato = c AND ca.fechaCreacion BETWEEN '$dateInicio' AND '$dateFin 23:59:59' AND TIMESTAMPDIFF(MONTH, ca.fechaCreacion, NOW()) <= ca.vigencia) )
                    OR EXISTS(SELECT ca3 FROM App\\Entity\\ContratoAnexo ca3 WHERE ca3.contrato = c AND ca3.fechaCreacion BETWEEN '$dateInicio' AND '$dateFin 23:59:59' AND TIMESTAMPDIFF(MONTH, ca3.fechaCreacion, NOW()) > ca3.vigencia)
                )";
            }

            switch ($user->getUsuarioTipo()->getId()) {
                case 3: case 4: case 1: case 8: case 11: case 14:
                    $query = $contratoRepository->findIndexQueryVencidos(null, $user->getEmpresaActual(), $compania, $filtro, null, $fecha);
                    break;
                case 13: case 10:
                    $companias     = $cuentaRepository->findByPers($user->getId());
                    $listIds = [];
                    foreach ($companias as $c) { $listIds[] = $c->getId(); }
                    $listCompanias = implode(',', $listIds);
                    if ($listCompanias) {
                        $fecha .= " AND a.cuenta IN ($listCompanias)";
                    }
                    $query = $contratoRepository->findIndexQueryVencidos(null, $user->getEmpresaActual(), $compania, $filtro, null, $fecha);
                    break;
                case 7:
                    $carteras = [];
                    foreach ($user->getUsuarioCarteras() as $uc) { $carteras[] = $uc->getCartera()->getId(); }
                    $fecha .= count($carteras) > 0
                        ? " AND c.cartera IN (" . implode(",", $carteras) . ")"
                        : " AND c.cartera IS NULL";
                    $query = $contratoRepository->findIndexQueryVencidos(null, null, $compania, $filtro, null, $fecha);
                    break;
                case 12:
                    $lotes = [];
                    foreach ($user->getUsuarioLotes() as $ul) { $lotes[] = $ul->getLote()->getId(); }
                    $fecha .= count($lotes) > 0
                        ? " AND c.idLote IN (" . implode(",", $lotes) . ")"
                        : " AND c.idLote IS NULL";
                    $query = $contratoRepository->findIndexQueryVencidos(null, $user->getEmpresaActual(), $compania, $filtro, null, $fecha);
                    break;
                default:
                    $query = $contratoRepository->findIndexQueryVencidos($user->getId(), null, $compania, $filtro, null, $fecha);
                    break;
            }

           
            $contratos = $paginator->paginate(
                $query,
                $request->query->getInt('page', 1),
                20,
                ['defaultSortFieldName' => 'c.id', 'defaultSortDirection' => 'desc']
            );

             // ── Enriquecer los 20 items con native SQL ─────────────────────────
            $items = $contratos->getItems();
            if (!empty($items)) {
                $idsList = implode(',', array_map(fn($c) => $c->getId(), $items));
                try {
                    $conn = $this->getDoctrine()->getConnection();
                    $sql = "
                        SELECT
                            c.id,
                            COALESCE(TO_DAYS(NOW()) - TO_DAYS(vuolt.fecha_registro), 0) AS dias_ult_observacion,
                            CASE WHEN TIMESTAMPDIFF(MONTH, c.fecha_creacion, NOW()) <= c.vigencia THEN 1 ELSE 0 END AS vigencia_contrato,
                            CASE
                                WHEN ca.id IS NULL THEN NULL
                                WHEN TIMESTAMPDIFF(MONTH, cax.fecha_creacion, NOW()) <= cax.vigencia THEN 1
                                ELSE 0
                            END AS vigencia_anexo,
                            CASE WHEN vm.folio IS NOT NULL OR vr.folio IS NOT NULL OR vu.folio IS NOT NULL THEN 1 ELSE 0 END AS vip,
                            CASE WHEN cm.contrato_id IS NOT NULL THEN 1 ELSE 0 END AS moroso,
                            COALESCE(ca.fecha_creacion, c.fecha_creacion) AS fecha_creacion_vista,
                            COALESCE(concat(ca.id,'-',c.folio,'-',ca.folio),c.folio) AS folio_vista
                        FROM contrato c
                        LEFT JOIN vista_contrato_anexo_max ca ON ca.contrato_id = c.id
                        LEFT JOIN contrato_anexo cax ON cax.id = ca.id
                        LEFT JOIN vw_ult_observacion_linea_tiempo vuolt ON vuolt.contrato_id = c.id
                        LEFT JOIN vw_vip_mayor_2mm vm ON vm.contrato_id = c.id
                        LEFT JOIN vw_vip_referidos vr ON vr.contrato_id = c.id
                        LEFT JOIN vw_vip_una_cuota vu ON vu.contrato_id = c.id
                        LEFT JOIN vw_clientes_morosos cm ON cm.contrato_id = c.id
                        WHERE c.id IN ($idsList)
                    ";
                    $rows     = $conn->fetchAllAssociative($sql);
                    $byId     = array_column($rows, null, 'id');

                    foreach ($items as $contrato) {
                        $row = $byId[$contrato->getId()] ?? null;
                        if ($row) {
                           $contrato->setDiasUltObservacion((int)$row['dias_ult_observacion']);
                            $contrato->setVigenciaContrato((int)$row['vigencia_contrato']);
                            $contrato->setVigenciaAnexo($row['vigencia_anexo'] !== null ? (int)$row['vigencia_anexo'] : null);
                            $contrato->setVip((int)$row['vip']);
                            $contrato->setFolioContrato($row['folio_vista']);
                            if ($row['fecha_creacion_vista']) {
                                $contrato->setFechaCreacionVista(new \DateTime($row['fecha_creacion_vista']));
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // enriquecimiento falla silenciosamente
                }
            }
            $html = $this->renderView('contratos_vencidos/_tabla.html.twig', [
                'contratos'    => $contratos
            ]);
            return new JsonResponse(['html' => $html]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()]);
        }
    }
    /**
     * @Route("/{id}", name="contratos_vencidos_show", methods={"GET"})
     */
    public function show(Contrato $contrato,
                        DiasPagoRepository $diasPagoRepository,
                        ModuloPerRepository $moduloPerRepository,
                        CausaObservacionRepository $causaObservacionRepository): Response
    {
        $this->denyAccessUnlessGranted('view','contratos_vencidos');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('contratos_vencidos',$user->getEmpresaActual());
        return $this->render('contrato/show.html.twig', [
            'contrato' => $contrato,
            'agenda'=>$contrato->getAgenda(),
            'pagina'=>$pagina->getNombre(),
            'diasPagos'=>$diasPagoRepository->findAll(),
            'noSuscribir'=>'si',
            'observaciones'=>$causaObservacionRepository->findBy(['contrato'=>$contrato],['fechaRegistro'=>'Desc'])
        ]);
    }
}
