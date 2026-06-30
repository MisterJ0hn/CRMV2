<?php

namespace App\Controller;

use App\Repository\CausaRepository;
use App\Repository\ConfiguracionRepository;
use App\Repository\ContratoRepository;
use App\Repository\CuentaRepository;
use App\Repository\EstrategiaJuridicaRepository;
use App\Repository\MateriaRepository;
use App\Repository\UsuarioRepository;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mis_clientes")
 */
class MisClientesController extends AbstractController
{
    /**
     * @Route("/", name="mis_clientes_index", methods={"GET"})
     */
    public function index(CuentaRepository $cuentaRepository, 
                            MateriaRepository $materiaRepository, 
                            Request $request,
                            UsuarioRepository $usuarioRepository): Response
    {
        $this->denyAccessUnlessGranted('view', 'mis_clientes');
        $user = $this->getUser();
        $casos_activos=0;
        $tramitadores = [];
        $request->getSession()->set("origen", "Mis_Clientes");
        $request->getSession()->set("origen_anexo", "Mis_Clientes");
       //$dateInicio=date('Y-m-'.date('d'),mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30*24);
       $dateInicio = (new DateTime())->modify('-24 months')->format('Y-m-d');        
       $dateFin    = date('Y-m-d');
        $conn = $this->getDoctrine()->getConnection();
        $empresa = $user->getEmpresaActual();
        switch ($user->getUsuarioTipo()->getId()) {
            case 3: case 4: case 1: case 8: case 11: case 12:
                $companias = $cuentaRepository->findByPers(null, $user->getEmpresaActual());
                $tramitadores = $usuarioRepository->findBy(['usuarioTipo'=>7, 'estado'=>1]);
                 $sqlServicios = '
                    SELECT DISTINCT m.id, m.nombre as nombre
                    FROM materia m
                    INNER JOIN cuenta_materia cm ON cm.materia_id = m.id
                    INNER JOIN cuenta c ON c.id = cm.cuenta_id
                    INNER JOIN usuario_cuenta uc on uc.cuenta_id = c.id
                    WHERE m.empresa_id = :empresa
                    ORDER BY m.nombre ASC
                    ';
                    $materiasRaw = $conn->fetchAllAssociative($sqlServicios, [ 'empresa' => $empresa]);
                break;
            default:
                $companias = $cuentaRepository->findByPers($user->getId());
                $tramitadores = $usuarioRepository->findBy(['id'=>$user->getId(), 'estado'=>1]);
                $sqlServicios = '
                    SELECT DISTINCT m.id, m.nombre as nombre
                    FROM materia m
                    INNER JOIN cuenta_materia cm ON cm.materia_id = m.id
                    INNER JOIN cuenta c ON c.id = cm.cuenta_id
                    INNER JOIN usuario_cuenta uc on uc.cuenta_id = c.id
                    WHERE m.empresa_id = :empresa
                    and uc.usuario_id = :usuario
                    
                    ORDER BY m.nombre ASC
                    ';
                    $materiasRaw = $conn->fetchAllAssociative($sqlServicios, ['usuario'=>$user->getId(), 'empresa' => $empresa]);
                break;
        }
        $materias = $materiaRepository->findAll();

        return $this->render('mis_clientes/index.html.twig', [
           
            'companias'  => $companias,
            'bFiltro'    => '',
            'bCompania'  => null,
            'bMateria'   => null,
            'bAtrasado'  => null,
            'materias'   => $materiasRaw,
            'tramitadores'=>$tramitadores,
            'dateInicio'    => $dateInicio,
            'dateFin'       => $dateFin,
        ]);
    }

    /**
     * @Route("/obtenerContenido", name="mis_clientes_obtener_contenido", methods={"GET"})
     */
    public function obtenerContenido(
        ContratoRepository $contratoRepository,
        PaginatorInterface $paginator,
        Request            $request,
        ConfiguracionRepository $configuracionRepository
        
    ): JsonResponse {
        try {
            $this->denyAccessUnlessGranted('view', 'mis_clientes');
        
        
            $user     = $this->getUser();
            $filtro   = null;
            $materia = null;
            $empresa  = null;
            $usuario  = null;
            $otros    = null;
            $atrasado = null;
            $tramitador = null;
            $tipoCliente=null;
            $prioridad = null;
            $primerPago = null;
            $servicio = null;

            $configuracion = $configuracionRepository->find(1);

            if (null !== $request->query->get('bFiltro') && $request->query->get('bFiltro') !== '') {
                $filtro = $request->query->get('bFiltro');
            }
            if (null !== $request->query->get('bMateria') && $request->query->get('bMateria') != 0) {
                $materia = $request->query->get('bMateria');
            }
            if (null !== $request->query->get('bAtrasado') && $request->query->get('bAtrasado') != 0) {
                $atrasado = $request->query->get('bAtrasado');
            }
            if (null !== $request->query->get('bTramitador') && $request->query->get('bTramitador') != 0) {
                $tramitador = $request->query->get('bTramitador');
            }
            if (null !== $request->query->get('bTipoCliente') && $request->query->get('bTipoCliente') != 0) {
                $tipoCliente = $request->query->get('bTipoCliente');
            }
            if (null !== $request->query->get('bPrioridad') && $request->query->get('bPrioridad') != 0) {
                $prioridad = $request->query->get('bPrioridad');
            }
            if (null !== $request->query->get('bPrimerPago') && $request->query->get('bPrimerPago') != 0) {
                $primerPago = $request->query->get('bPrimerPago');
            }
            if (null !== $request->query->get('bServicio') && $request->query->get('bServicio') != 0) {
                $servicio = $request->query->get('bServicio');
            }
            if (null !== $request->query->get('bFecha')) {
                    $aux_fecha  = explode(" - ", $request->query->get('bFecha'));
                $dateInicio = $aux_fecha[0];
                $dateFin    = $aux_fecha[1];
            } else {
                $dateInicio = (new DateTime())->modify('-24 months')->format('Y-m-d');        
                $dateFin    = date('Y-m-d');
            }




            $empresa = $user->getEmpresaActual();
            switch ($user->getUsuarioTipo()->getId()) {
                case 3: case 4: case 1: case 8: case 11: case 12:
                    $empresa = $user->getEmpresaActual();
                    break;
                case 7:
                    $carteras = [];
                    foreach ($user->getUsuarioCarteras() as $uc) {
                        $carteras[] = $uc->getCartera()->getId();
                    }
                    $otros = count($carteras) > 0
                        ? 'c.cartera IN (' . implode(',', $carteras) . ')'
                        : 'c.cartera IS NULL';
                    break;
                default:
                    $usuario = $user->getId();
                    break;
            }

           
            $qb = $contratoRepository->findMisClientesQuery($empresa, $materia, $filtro, $usuario, $otros, $atrasado,$tramitador,$tipoCliente,$prioridad,$primerPago,$dateInicio,$dateFin,$servicio);
      
           
            $contratos = $paginator->paginate(
                $qb,
                $request->query->getInt('page', 1),
                10,
                ['defaultSortFieldName' => 'c.id', 'defaultSortDirection' => 'desc']
            );

             $items = $contratos->getItems();
            if (!empty($items)) {
                $idsList = implode(',', array_map(fn($c) => $c->getId(), $items));
                try {
                    

                   
                    $conn = $this->getDoctrine()->getConnection();
                    $sql = '
                        SELECT
                            c.id,
                            c.agenda_id,
                            CASE WHEN TIMESTAMPDIFF(MONTH, c.fecha_creacion, NOW()) <= c.vigencia THEN 1 ELSE 0 END AS vigencia_contrato,
                            CASE
                                WHEN ca.id IS NULL THEN NULL
                                WHEN TIMESTAMPDIFF(MONTH, cax.fecha_creacion, NOW()) <= cax.vigencia THEN 1
                                ELSE 0
                            END AS vigencia_anexo,                    
                            COALESCE(ca.fecha_creacion, c.fecha_creacion) AS fecha_creacion_vista,
                            COALESCE(concat(ca.id,"-",c.folio,"-",ca.folio),c.folio) AS folio_vista,                                                                  
                           (select count(*) from cuota c10 where c10.contrato_id=c.id and (c10.anular is null  OR c10.anular=0) and (c10.monto>=(c10.pagado+'.$configuracion->getDeudaMinima().') or c10.pagado is null) and c10.numero=1 ) as primer_pago                                
                        FROM contrato c
                        LEFT JOIN vista_contrato_anexo_max ca ON ca.contrato_id = c.id
                        LEFT JOIN contrato_anexo cax ON cax.id = ca.id                                                                                          
                        WHERE c.id IN ('.$idsList.')
                    ';
                    $rows      = $conn->fetchAllAssociative($sql);
                    $rowsById  = array_column($rows, null, 'id');

                    foreach ($items as $contrato) {
                        $row = $rowsById[$contrato->getId()] ?? null;
                        if ($row) {
                            $hoy = new \DateTime(date("Y-m-d"));
                            $fechaObs = new \DateTime($contrato->getFechaUltimaObservacionGeneral()->format('Y-m-d'));

                            $diasUltObservacion = $hoy->diff($fechaObs)->days;
                            $contrato->setDiasUltObservacion((int)$diasUltObservacion);
                            $contrato->setVigenciaContrato((int)$row['vigencia_contrato']);
                            $contrato->setVigenciaAnexo($row['vigencia_anexo'] !== null ? (int)$row['vigencia_anexo'] : null);
                           
                            $contrato->setFolioContrato($row['folio_vista']);
                            if ($row['fecha_creacion_vista']) {
                                $contrato->setFechaCreacionVista(new \DateTime($row['fecha_creacion_vista']));
                            }
                          
                            $dqlCausa='select CASE  WHEN ca3.letra IS NULL 
                                                        AND ca3.rol IS NULL 
                                                        AND ca3.anio IS NULL 
                                                        
                                                    THEN "<span style=\'color:#555;\'>—</span>"
                                                    ELSE 
                                                        concat(
                                                            ca3.letra, "-", ca3.rol, "-", ca3.anio
                                                        )
                                                    END as rit_rol_causa,
                                                    ej2.nombre as nombre_servicio,
                                                    case when ca3.etapa_pendiente is null or ca3.etapa_pendiente="" then "<span style=\'color:#555;\'>—</span>" 
                                                    else ca3.etapa_pendiente 
                                                    end as etapa_pendiente,

                                                    ca3.id as causa_id
                                        FROM causa ca3
                                                   
                                                    INNER JOIN materia_estrategia me2 ON me2.id = ca3.materia_estrategia_id
                                                    INNER JOIN estrategia_juridica ej2 ON ej2.id = me2.estrategia_juridica_id
                                        WHERE (ca3.causa_finalizada IS NULL OR ca3.causa_finalizada = 0)
                                        and ca3.estado = 1
                                        and ca3.agenda_id='.$row['agenda_id'];

                            $rowsCausa      = $conn->fetchAllAssociative($dqlCausa);
                            $rit_rol_causas ="";
                            $nombre_servicios="";
                            $etapas="";
                            $ultimos_recordatorios="";
                            foreach ($rowsCausa as $rowCausa) {
                                $rit_rol_causas .= $rowCausa['rit_rol_causa']."<br>" ?? '';
                                $nombre_servicios .= $rowCausa['nombre_servicio']."<br>" ?? '';
                                $etapas .= $rowCausa['etapa_pendiente']."<br>" ?? '';
                                $dqlRecordatorio='select
                                                min(m.fecha_aviso) as fecha_aviso,
                                                mp.color,
                                                mp.color_texto,
                                                m.id as mensaje_id
                                               
                                                from mensaje m
                                                left JOIN mensaje_prioridad mp on mp.id = m.mensaje_prioridad_id 
                                            WHERE 
                                                 (
                                                    m.fecha_aviso >= now() 
                                                    and m.leido=0
                                                ) 
                                                and m.causa_id = '.$rowCausa['causa_id'].' group by m.causa_id';
                                $rowsRecordatorio      = $conn->fetchAllAssociative($dqlRecordatorio);
                                if(count($rowsRecordatorio)>0){
                                    foreach ($rowsRecordatorio as $rowRecordatorio) {
                                        $ultimos_recordatorios .=  "<div class='badge' style='background-color:".$rowRecordatorio['color'].";color:".$rowRecordatorio['color_texto'].";' >
                                                <a href='#' style='color:".$rowRecordatorio['color_texto'].";' data-toggle='modal' data-target='#modalObservacion' data-mensaje-id='".$rowRecordatorio['mensaje_id']."'\> 
                                                ".$rowRecordatorio['fecha_aviso']."</a></div>
                                                </a></div><br>";
                                    }
                                }else{
                                    $ultimos_recordatorios .= "<span style='color:#555;'>—</span><br>";
                                }


                            }

                            $contrato->setRitRolCausa($rit_rol_causas);
                            $contrato->setServicios($nombre_servicios);
                           
                            $contrato->setEtapas($etapas);
                   
                            
                            $contrato->setUltimoRecordatorio($ultimos_recordatorios);
                            $contrato->setPrimerPago((int)$row['primer_pago']);
                        }
                    }
                } catch (\Exception $e) {
                    // Si falla el enriquecimiento, continuar con valores por defecto (0/null)
                }
            }
            $html = $this->renderView('mis_clientes/_tabla.html.twig', [
                'contratos' => $contratos,
            ]);

            return new JsonResponse([
                'html'  => $html,
                'total' => $contratos->getTotalItemCount(),
                'total_formateado' => number_format($contratos->getTotalItemCount(), 0, ',', '.'),
            
            ]);

        } catch (\Exception $e) {
            if($e->getCode()==403){
                //cuando el error es por privilegios:
                return new JsonResponse(['error' =>'No fue posible acceder a los datos. Su sesión puede haber expirado. Por favor, vuelva a iniciar sesión.']);
            }else{
                return new JsonResponse(['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * @Route("/obtenerTotal", name="mis_clientes_obtener_total", methods={"GET"})
     */
    public function obtenerTotal(
        CausaRepository $causaRepository,
        Request            $request
    ): JsonResponse {
            $this->denyAccessUnlessGranted('view', 'mis_clientes');
    
            try {
                $user     = $this->getUser();
                $filtro   = null;
                $materia = null;
                $empresa  = null;
                $usuario  = null;
                $otros    = null;
                $atrasado = null;
                $tramitador = null;
                $tipoCliente = null;
                $prioridad = null;
                $primerPago = null;
                $servicio = null;

                if (null !== $request->query->get('bFiltro') && $request->query->get('bFiltro') !== '') {
                    $filtro = $request->query->get('bFiltro');
                }
                if (null !== $request->query->get('bMateria') && $request->query->get('bMateria') != 0) {
                    $materia = $request->query->get('bMateria');
                }
                if (null !== $request->query->get('bAtrasado') && $request->query->get('bAtrasado') != 0) {
                    $atrasado = $request->query->get('bAtrasado');
                }
                if (null !== $request->query->get('bTramitador') && $request->query->get('bTramitador') != 0) {
                    $tramitador = $request->query->get('bTramitador');
                }
                 if (null !== $request->query->get('bTipoCliente') && $request->query->get('bTipoCliente') != 0) {
                    $tipoCliente = $request->query->get('bTipoCliente');
                }
                if (null !== $request->query->get('bPrioridad') && $request->query->get('bPrioridad') != 0) {
                    $prioridad = $request->query->get('bPrioridad');
                }
                if (null !== $request->query->get('bPrimerPago') && $request->query->get('bPrimerPago') != 0) {
                    $primerPago = $request->query->get('bPrimerPago');
                }
                if (null !== $request->query->get('bServicio') && $request->query->get('bServicio') != 0) {
                    $servicio = $request->query->get('bServicio');
                }
                if (null !== $request->query->get('bFecha')) {
                    $aux_fecha  = explode(" - ", $request->query->get('bFecha'));
                    $dateInicio = $aux_fecha[0];
                    $dateFin    = $aux_fecha[1]." 23:59:59";
                } else {
                    $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30*24);
                    $dateFin    = date('Y-m-d');
                }
                $empresa = $user->getEmpresaActual();
                switch ($user->getUsuarioTipo()->getId()) {
                    case 3: case 4: case 1: case 8: case 11: case 12:
                        $empresa = $user->getEmpresaActual();
                        break;
                    case 7:
                        $carteras = [];
                        foreach ($user->getUsuarioCarteras() as $uc) {
                            $carteras[] = $uc->getCartera()->getId();
                        }
                        $otros = count($carteras) > 0
                            ? 'co.cartera IN (' . implode(',', $carteras) . ')'
                            : 'co.cartera IS NULL';
                        break;
                    default:
                        $usuario = $user->getId();
                        break;
                } 
                
                $total = $causaRepository->countMisClientes($empresa, $materia, $filtro, $usuario, $otros, $atrasado,$tramitador,$tipoCliente,$prioridad,$primerPago,$dateInicio,$dateFin,$servicio);
              
                return new JsonResponse(['total' => number_format($total[0]['total'], 0, ',', '.')]);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => $e->getMessage()],400);
            }
    }

    /**
     * @Route("/filtros_por_materia", name="mis_clientes_filtros_por_materia", methods={"GET"})
     */
    public function filtrosPorMateria(
        Request $request,
        EstrategiaJuridicaRepository $estrategiaJuridicaRepository,
        UsuarioRepository $usuarioRepository
    ): JsonResponse {
        $this->denyAccessUnlessGranted('view', 'mis_clientes');

        $materiaId = $request->query->get('materia', 0);
        $user = $this->getUser();
        $empresa = $user->getEmpresaActual();

        if ($materiaId == 0) {
            
            $conn = $this->getDoctrine()->getConnection();
            switch ($user->getUsuarioTipo()->getId()) {
                case 3: case 4: case 1: case 8: case 11: case 12:
                    $tramitadoresRaw = $usuarioRepository->findBy(['usuarioTipo' => 7, 'estado' => 1]);
                    $sqlServicios = '
                    SELECT DISTINCT ej.id, ej.nombre as nombre
                    FROM estrategia_juridica ej
                    INNER JOIN materia_estrategia me ON me.estrategia_juridica_id = ej.id
                    INNER JOIN materia m ON m.id = me.materia_id
                    INNER JOIN cuenta_materia cm ON cm.materia_id = m.id
                    INNER JOIN cuenta c ON c.id = cm.cuenta_id
                    INNER JOIN usuario_cuenta uc on uc.cuenta_id = c.id
                    WHERE ej.empresa_id = :empresa
                    
                    ORDER BY ej.nombre ASC
                    ';
                    $serviciosRaw = $conn->fetchAllAssociative($sqlServicios, [ 'empresa' => $empresa]);
                    
                    break;
                default:
                    $tramitadoresRaw = $usuarioRepository->findBy(['id' => $user->getId(), 'estado' => 1]);
                    
                    $sqlServicios = '
                    SELECT DISTINCT ej.id, ej.nombre as nombre
                    FROM estrategia_juridica ej
                    INNER JOIN materia_estrategia me ON me.estrategia_juridica_id = ej.id
                    INNER JOIN materia m ON m.id = me.materia_id
                    INNER JOIN cuenta_materia cm ON cm.materia_id = m.id
                    INNER JOIN cuenta c ON c.id = cm.cuenta_id
                    INNER JOIN usuario_cuenta uc on uc.cuenta_id = c.id
                    WHERE uc.usuario_id = :usuario
                    AND ej.empresa_id = :empresa
                    
                    ORDER BY ej.nombre ASC
                    ';
                    $serviciosRaw = $conn->fetchAllAssociative($sqlServicios, ['usuario' => $user->getId(), 'empresa' => $empresa]);
                    break;
            }
        } else {
            $conn = $this->getDoctrine()->getConnection();

            $sqlServicios = '
                SELECT DISTINCT ej.id, ej.nombre as nombre
                FROM estrategia_juridica ej
                INNER JOIN materia_estrategia me ON me.estrategia_juridica_id = ej.id
                WHERE me.materia_id = :materia
                AND ej.empresa_id = :empresa
                ORDER BY ej.nombre ASC
            ';
            $serviciosRaw = $conn->fetchAllAssociative($sqlServicios, ['materia' => $materiaId, 'empresa' => $empresa]);

            $sqlTramitadores = '
                SELECT DISTINCT u.id, u.nombre
                FROM usuario u
                INNER JOIN usuario_cuenta uc on uc.usuario_id=u.id 
                inner join cuenta_materia cm on cm.cuenta_id=uc.cuenta_id
            
                WHERE cm.materia_id = :materia
                AND u.usuario_tipo_id = 7
                AND u.estado = 1
                ORDER BY u.nombre ASC
            ';
            $tramitadoresRaw = $conn->fetchAllAssociative($sqlTramitadores, ['materia' => $materiaId]);

            return new JsonResponse([
                'servicios'    => $serviciosRaw,
                'tramitadores' => $tramitadoresRaw,
            ]);
        }

        $servicios = $serviciosRaw;

        $tramitadores = array_map(fn($t) => [
            'id'     => $t->getId(),
            'nombre' => $t->getNombre(),
        ], $tramitadoresRaw);

        return new JsonResponse([
            'servicios'    => $servicios,
            'tramitadores' => $tramitadores,
        ]);
    }
}
