<?php

namespace App\Controller;

use App\Entity\AgendaObservacion;
use App\Entity\Contrato;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\CuentaRepository;
use App\Repository\GrupoRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\UsuarioGrupoRepository;
use App\Repository\UsuarioRepository;
use App\Repository\ContratoRepository;
use App\Repository\VencimientoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/contrato_consultor")
 */
class ContratoConsultorController extends AbstractController
{
    /**
     * @Route("/", name="contrato_consultor_index")
     */
    public function index(ModuloPerRepository $moduloPerRepository,
                        Request $request,
                        CuentaRepository $cuentaRepository,
                        VencimientoRepository $vencimientoRepository): Response
    {
        $this->denyAccessUnlessGranted('view','contrato_consultor');
        $user=$this->getUser();
        $moduloPerRepository->findOneByName('contrato_consultor',$user->getEmpresaActual());

        $error_toast='';
        if(null !== $request->query->get('error_toast')){
            $error_toast=$request->query->get('error_toast');
        }

        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 1:
            case 8:
            case 4:
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;
            default:
                $companias=$cuentaRepository->findByPers($user->getId());
                break;
        }

        $vencimientos=$vencimientoRepository->findBy(
            ['empresa'=>$user->getEmpresaActual(),'soloPorAdmin'=>false],
            ['valMin'=>'ASC'],
            2
        );

        $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30*24);
        $dateFin=date('Y-m-d');

        return $this->render('contrato_consultor/index.html.twig', [
            'companias'     => $companias,
            'vencimientos'  => $vencimientos,
            'dateInicio'    => $dateInicio,
            'dateFin'       => $dateFin,
            'pagina'        => 'Contrato Consultor',
            'error_toast'   => $error_toast,
            'tabInicial'    => $request->query->get('tab','prime'),
            'bFolio'=> null
        ]);
    }

    /**
     * @Route("/tab_data", name="contrato_consultor_tab_data", methods={"GET"})
     */
    public function tabData(ContratoRepository $contratoRepository,
                            PaginatorInterface $paginator,
                            Request $request): Response
    {
        $this->denyAccessUnlessGranted('view','contrato_consultor');
        $user = $this->getUser();

        $tab      = $request->query->get('tab', 'prime');
        $filtro   = null;
        $compania = null;
        $otros    = null; // condición DQL extra (folio, grupo, etc.)

        // ── Parse filtros del buscador ──────────────────────────────────────
        if ($request->query->get('bFolio')) {
            $folio  = $request->query->get('bFolio');
            $otros  = "(c.folio = '$folio' OR c.agenda = '$folio')";
        } elseif ($request->query->get('bFiltro')) {
            $filtro = $request->query->get('bFiltro');
        } else {
            if ($request->query->get('bCompania')) {
                $compania = $request->query->get('bCompania');
            }
            if ($request->query->get('bFecha')) {
                [$dateInicio, $dateFin] = explode(' - ', $request->query->get('bFecha'));
            } else {
                $dateInicio = date('Y-m-d', mktime(0,0,0,date('m'),date('d'),date('Y')) - 60*60*24*30*24);
                $dateFin    = date('Y-m-d');
            }
            $otros = "c.fechaCreacion BETWEEN '$dateInicio' AND '$dateFin 23:59:59'";
        }

        // ── Filtro por grupo (tipo 4) ───────────────────────────────────────
        if ($user->getUsuarioTipo()->getId() === 4) {
            $listGrupos = implode(',', array_map(
                fn($g) => $g->getGrupo()->getId(),
                iterator_to_array($user->getusuarioGrupos())
            ));
            $grupoCondicion = "c.grupo IN ($listGrupos)";
            $otros = $otros ? "($otros) AND $grupoCondicion" : $grupoCondicion;
        }

        // ── Condición de tab (directo sobre cuota, sin pasar por vw_cuota_pendiente) ──
        $isMoroso = ($tab === 'morosos');

        // Condición de "cuota pendiente" reutilizable (no anulada y con saldo)
        $cuotaPendienteCond = "(cuo.anular IS NULL OR cuo.anular = false) AND (cuo.pagado IS NULL OR cuo.monto > cuo.pagado)";

        // Condición de "está en vencimiento 1 ó 2" (overdue)
        $enVencimientoCond = "DATEDIFF(CURRENT_DATE(), cuo.fechaPago) >= v.valMin AND (v.valMax IS NULL OR DATEDIFF(CURRENT_DATE(), cuo.fechaPago) <= v.valMax) AND v.id IN (1, 2)";

        if ($isMoroso) {
            // morosos: existe cuota pendiente cuya antigüedad cae en vencimiento 1 ó 2
            $tabCondicion = "EXISTS (
                SELECT cuo.id FROM App\\Entity\\Cuota cuo, App\\Entity\\Vencimiento v
                WHERE cuo.contrato = c AND $cuotaPendienteCond AND $enVencimientoCond
            )";
        } elseif ($tab === 'prime') {
            // prime: sin cuotas pendientes (todo pagado o sin cuotas)
            $tabCondicion = "NOT EXISTS (
                SELECT cuo.id FROM App\\Entity\\Cuota cuo
                WHERE cuo.contrato = c AND $cuotaPendienteCond
            ) AND c.fechaDesiste IS NULL";
        } else {
            // preferente: tiene cuotas pendientes pero ninguna ha entrado a vencimiento
            $tabCondicion = "EXISTS (
                SELECT cuo.id FROM App\\Entity\\Cuota cuo
                WHERE cuo.contrato = c AND $cuotaPendienteCond
            ) AND NOT EXISTS (
                SELECT cu2.id FROM App\\Entity\\Cuota cu2, App\\Entity\\Vencimiento v2
                WHERE cu2.contrato = c AND (cu2.anular IS NULL OR cu2.anular = false)
                AND (cu2.pagado IS NULL OR cu2.monto > cu2.pagado)
                AND DATEDIFF(CURRENT_DATE(), cu2.fechaPago) >= v2.valMin
                AND (v2.valMax IS NULL OR DATEDIFF(CURRENT_DATE(), cu2.fechaPago) <= v2.valMax)
            ) AND c.fechaDesiste IS NULL";
        }

        $otros = $otros ? "($otros) AND $tabCondicion" : $tabCondicion;

        // ── Usuario ────────────────────────────────────────────────────────
        $tipoId  = $user->getUsuarioTipo()->getId();
        $empresa = in_array($tipoId, [1, 3, 4, 8]) ? $user->getEmpresaActual() : null;
        $usuario = in_array($tipoId, [1, 3, 4, 8]) ? null : $user->getId();

        $query = $contratoRepository->findConsultorQuery($usuario, $empresa, $compania, $filtro, $otros);

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
                        u.nombre        AS consultor_nombre,
                        COALESCE(ca.fecha_creacion, c.fecha_creacion) AS fecha_creacion_vista,
                        co.fecha_registro AS fecha_ultima_observacion
                    FROM contrato c
                    LEFT JOIN usuario_grupo ug ON ug.grupo_id = c.grupo_id
                    LEFT JOIN usuario u         ON u.id = ug.usuario_id
                    LEFT JOIN vista_contrato_anexo_max ca ON ca.contrato_id = c.id
                    LEFT JOIN contrato_observacion co ON co.id = (
                        SELECT id FROM contrato_observacion
                        WHERE contrato_id = c.id
                        ORDER BY fecha_registro DESC
                        LIMIT 1
                    )
                    WHERE c.id IN ($idsList)
                ";
                $rows     = $conn->fetchAllAssociative($sql);
                $byId     = array_column($rows, null, 'id');

                foreach ($items as $contrato) {
                    $row = $byId[$contrato->getId()] ?? null;
                    if ($row) {
                        $contrato->setConsultorNombre($row['consultor_nombre']);
                        if ($row['fecha_creacion_vista']) {
                            $contrato->setFechaCreacionVista(new \DateTime($row['fecha_creacion_vista']));
                        }
                        if ($row['fecha_ultima_observacion']) {
                            $contrato->setFechaUltimaObservacion(new \DateTime($row['fecha_ultima_observacion']));
                        }
                    }
                }
            } catch (\Exception $e) {
                // enriquecimiento falla silenciosamente
            }
        }

        return $this->render('contrato_consultor/_tabla.html.twig', [
            'contratos' => $contratos,
            'tab'       => $tab,
            'isMoroso'  => $isMoroso,
        ]);
    }

    /**
    * @Route("/{id}/reasignar" , name="contrato_consultor_reasignar" , methods={"GET","POST"})
    */
    public function reasignarGrupo(Contrato $contrato,
                                    UsuarioGrupoRepository $usuarioGrupoRepository,
                                    GrupoRepository $grupoRepository,
                                    Request $request,
                                    ModuloPerRepository $moduloPerRepository,
                                    UsuarioRepository   $usuarioRepository
                                    ):Response
    {

        $this->denyAccessUnlessGranted('edit','contrato_consultor_reasignar');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('contrato_consultor_reasignar',$user->getEmpresaActual());

        if($request->request->get('cboConsultor')!=null){
            $entityManager = $this->getDoctrine()->getManager();
            $consultorNuevo=$request->request->get('cboConsultor');

            $usuarioGrupo=$usuarioGrupoRepository->findPrimerGrupoDisponiblePorUsuario($consultorNuevo);

            if(null == $usuarioGrupo){
                //si no hay lotes para utilizar, se setean en false todos para poder utilizar...
                $grupos=$grupoRepository->findBy(['estado'=>true]);
                foreach($grupos as $grupo){
                    $grupo->setUtilizado(false);
                    $entityManager->persist($grupo);
                    $entityManager->flush();
                }
                $grupo=$grupoRepository->findPrimerDisponible();

                $grupo->setUtilizado(true);
                $entityManager->persist($grupo);
                $entityManager->flush();
            }else{
                $grupo = $usuarioGrupo->getGrupo();
                $grupo->setUtilizado(true);
                $entityManager->persist($grupo);
                $entityManager->flush();
            }
            $contrato->setGrupo($grupo);
            $agenda = $contrato->getAgenda();

            $observacion=new AgendaObservacion();
            $observacion->setAgenda($agenda);
            $observacion->setUsuarioRegistro($usuarioRepository->find($user->getId()));
            $observacion->setStatus($agenda->getStatus());
            $observacion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
            $observacion->setObservacion($request->request->get('txtObservacion'));
            $observacion->setSubStatus($agenda->getSubStatus());
            $entityManager->persist($agenda);
            $entityManager->persist($observacion);
            $entityManager->flush();
            return $this->redirectToRoute('contrato_consultor_index',['error_toast'=>"Toast.fire({icon: 'success',title: 'Registro grabado con exito'})"]);

        }
        $usuarioConsultor = $usuarioGrupoRepository->findOneBy(["grupo"=>$contrato->getGrupo()]);
        $consultores = $usuarioGrupoRepository->consultores();


        return $this->render('contrato_consultor/reasignar.html.twig', [
            'contrato' => $contrato,
            'pagina'    => $pagina ? $pagina->getNombre() : 'Contrato Consultor',
            'consultores' => $consultores,
            'consultorActual' => $usuarioConsultor->getUsuario()
        ]);
    }
}
