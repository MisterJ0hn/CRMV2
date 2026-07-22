<?php

namespace App\Controller;

use App\Repository\ConfiguracionRepository;
use App\Repository\CuentaRepository;
use App\Repository\CuotaRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\VencimientoRepository;
use App\Repository\VwCuotaPendienteRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/cobranza_pat")
 */
class CobranzaPatController extends AbstractController
{
    /**
     * @Route("/", name="cobranza_pat_index", methods={"GET"})
     */
    public function index(VencimientoRepository $vencimientoRepository,
                        CuentaRepository $cuentaRepository,
                        ModuloPerRepository $moduloPerRepository,
                        ConfiguracionRepository $configuracionRepository,
                        Request $request): Response
    {
        $this->denyAccessUnlessGranted('view','Cobranza_pago_recurrente');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('Cobranza_pago_recurrente',$user->getEmpresaActual());
        $vencimiento=$vencimientoRepository->findOneMaxNotNull($user->getEmpresaActual(),'v.valMax','ASC');
        $filtro=null;
        $folio=null;
        $compania=null;
        $dateInicio=null;
        $dateFin=null;
       
        $configuracion = $configuracionRepository->find(1);
        //   "maximo ".$vencimiento->getValMax();
        //$vencimiento=$vencimientoArray[0];
        $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
        $fecha=null;
        $fechaVW=null;
        $error='';
        $status=1;
        $error_toast="";
        $mostrarMensaje="NO";
        $request->getSession()->set('origen_anexo','cobranza_pat');
         switch($user->getUsuarioTipo()->getId()){
            case 1:
                $mostrarMensaje="SI";
                break;
            
         }

        $vencimiento_pat = ['min'=>$configuracion->getDiasMorosidadPat(),
                        'max'=>$vencimiento->getValMin()-1,
                        'color'=>$configuracion->getMorosidadPatColor(),
                        'icono'=>$configuracion->getMorosidadPatIcono(),
                        'nombre'=>$configuracion->getMorosidadPatNombre()];

        
        return $this->render('cobranza_pat/index.html.twig', [
           
            'bFiltro'=>$filtro,
            'bFolio'=>$folio,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'pagina'=>$pagina->getNombre(),
            'error'=>$error,
            'error_toast'=>$error_toast,
            'vencimiento'=>$vencimiento_pat,
            'mostrarMensaje'=>$mostrarMensaje,
        ]);
    }


    /**
     * @Route("/obtenerContenido", name="cobranza_pat_obtener_contenido", methods={"GET"})
     */
    public function obtenerContenido(PaginatorInterface $paginator, 
                        VencimientoRepository $vencimientoRepository,                        
                        VwCuotaPendienteRepository $cuotaRepository,                        
                        ConfiguracionRepository $configuracionRepository,                    
                        Request $request): JsonResponse
    {
        $user=$this->getUser();
        $this->denyAccessUnlessGranted('view','Cobranza_pago_recurrente');
        $vencimiento=$vencimientoRepository->findOneMaxNotNull($user->getEmpresaActual(),'v.valMax','ASC');
        $fecha=null;
        $filtro=null;
        $folio=null;
        $compania=null;
        $configuracion = $configuracionRepository->find(1);
        $otros=' DATEDIFF(now(),c.fechaPago) >= '.$configuracion->getDiasMorosidadPat().' and DATEDIFF(now(),c.fechaPago)< '.$vencimiento->getValMin();
         $vencimiento_pat = [
                        'color'=>$configuracion->getMorosidadPatColor(),
                        'icono'=>$configuracion->getMorosidadPatIcono()
                        ];

        try{
        if(null !== $request->query->get('error_toast')){
            $error_toast=$request->query->get('error_toast');
        }
       
        $status=$request->query->get('bStatus');
      

        if(null !== $request->query->get('bFolio') && $request->query->get('bFolio')!=''){
            $folio=$request->query->get('bFolio');
            $status=null;
            
            $otros.=" and (co.folio= $folio or co.agenda= $folio)";
           
        }else{
 
            if(null !== $request->query->get('bFiltro') && $request->query->get('bFiltro')!=''){
                $filtro=$request->query->get('bFiltro');
            }
            if(null !== $request->query->get('bCompania') && $request->query->get('bCompania')!=0){
                $compania=$request->query->get('bCompania');
            }
          
        }
        
        $fecha.=$otros." and a.status != 13 and co.contratoEstadoSuscripcion = 2";
        
        switch($user->getUsuarioTipo()->getId()){
            case 4:
            case 8:
            case 13:
                $query=$cuotaRepository->findVencimientoPatIndex(null,null,$compania,$filtro,null,true,$fecha, true,true,$status);
                break;
            case 7://tramitador
                $query=$cuotaRepository->findVencimientoPatIndex($user->getId(),null,$compania,$filtro,7,true,$fecha,true,true,$status);
                break;
            case 6: //abogado
                $query=$cuotaRepository->findVencimientoPatIndex($user->getId(),null,$compania,$filtro,6,true,$fecha, true,true,$status);
                break;
            case 11://Administrativo
            case 1://administrador y jefes    
            case 3:
                //$query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha,true);
                $query=$cuotaRepository->findVencimientoPatIndex(null,null,$compania,$filtro,null,true,$fecha, true,true,$status);
            break;
            default:
                //$query=$contratoRepository->findByPers(null,null,$compania,$filtro,null,$fecha,true);
                $query=$cuotaRepository->findVencimientoPatIndex(null,null,null,$filtro,null,true,$fecha,true,true,$status);
                
            break;
        }
       
        $contratos=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            100 /*limit per page*/,
            array());
        
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
                    $rows      = $conn->fetchAllAssociative($sql);
                    $rowsById  = array_column($rows, null, 'id');

                    foreach ($items as $cuota) {
                        $contrato = $cuota->getContrato();
                        $row = $rowsById[$contrato->getId()] ?? null;
                        if ($row) {
                            $contrato->setDiasUltObservacion((int)$row['dias_ult_observacion']);
                            $contrato->setVigenciaContrato((int)$row['vigencia_contrato']);
                            $contrato->setVigenciaAnexo($row['vigencia_anexo'] !== null ? (int)$row['vigencia_anexo'] : null);
                            $contrato->setVip((int)$row['vip']);
                            $contrato->setMoroso((int)$row['moroso']);
                            $contrato->setFolioContrato($row['folio_vista']);
                            if ($row['fecha_creacion_vista']) {
                                $contrato->setFechaCreacionVista(new \DateTime($row['fecha_creacion_vista']));
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Si falla el enriquecimiento, continuar con valores por defecto (0/null)
                }
            }

            $html = $this->renderView('cobranza_pat/_tabla.html.twig', ['cuotas' => $contratos,'vencimiento'=>$vencimiento_pat]);
            return new JsonResponse(['html' => $html]);
        }catch(\Exception $e){
            return new JsonResponse(['html' => '<div class="alert alert-danger m-3">Error al cargar los datos. '.$e->getMessage().'</div>']);  
        }
    }
}
