<?php

namespace App\Controller;

use App\Entity\Causa;
use App\Entity\PjudAnexoCausa;
use App\Entity\PjudAnexoMovimiento;
use App\Entity\PjudEscritos;
use App\Entity\PjudExhortos;
use App\Entity\PjudInformacionReceptor;
use App\Entity\PjudLitigantes;
use App\Entity\PjudMovimiento;
use App\Entity\PjudNotificaciones;
use App\Entity\VwPjudMovimientos;
use App\Repository\PjudAnexoCausaRepository;
use App\Repository\PjudAnexoMovimientoRepository;
use App\Repository\PjudCausaRepository;
use App\Repository\PjudEbookRepository;
use App\Repository\PjudEscritosRepository;
use App\Repository\PjudExhortosRepository;
use App\Repository\PjudInformacionReceptorRepository;
use App\Repository\PjudLitigantesRepository;
use App\Repository\PjudMovimientoRepository;
use App\Repository\PjudNotificacionesRepository;
use App\Repository\VwPjudCuadernosRepository;
use App\Repository\VwPjudMovimientosRepository;
use App\Service\PjudScraping;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/pjud_movimientos")
 */
class PjudMovimientosController extends AbstractController
{
    /**
     * @Route("/", name="pjud_movimientos_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('pjud_movimientos/index.html.twig', [
            'controller_name' => 'PjudMovimientosController',
        ]);
    }
    /**
     * @Route("/{id}/obtener_informacion_receptor", name="pjud_movimientos_obtener_informacion_receptor", methods={"GET","POST"})
     */
    function obtenerInformacion(Causa $causa,
                    PjudInformacionReceptorRepository $pjudInformacionReceptorRepository): JsonResponse
    {
        $informacionReceptor = $pjudInformacionReceptorRepository->findOneBy(['causa'=>$causa->getId()]);
        if(!$informacionReceptor){
            return $this->json(['error'=>'Información del receptor no encontrada'], 404);
        }
        return $this->json([
           'informacion_receptor'=>$informacionReceptor ?? [],
        ]);
    }
    /**
     * @Route("/{id}/obtener", name="pjud_movimientos_obtener", methods={"GET","POST"})
     */
    function obtener(Causa $causa,
                    PjudCausaRepository $pjudCausaRepository,
                    VwPjudCuadernosRepository $vwPjudCuadernosRepository,
                    PjudEbookRepository $pjudEbookRepository,
                    VwPjudMovimientosRepository $pjudMovimientoRepository,
                    PjudExhortosRepository $pjudExhortosRepository,
                    PjudLitigantesRepository $pjudLitigantesRepository,
                    PjudNotificacionesRepository $pjudNotificacionesRepository,
                    PjudInformacionReceptorRepository $pjudInformacionReceptorRepository,
                    PjudEscritosRepository $pjudEscritosRepository,
                    PjudAnexoCausaRepository $pjudAnexoCausaRepository
                   ): JsonResponse
    {
        $movimientosLegacy=[];
        $pjudCausa = $pjudCausaRepository->findOneBy(['causa'=>$causa->getId()]);
        if(!$pjudCausa){
            return $this->json(['error'=>'Causa no encontrada en Pjud'], 404);
        }
        $cabecera = [
            '',
            $pjudCausa->getRit() ,
            $pjudCausa->getFechaIngreso() ,
            $pjudCausa->getCaratulado(),
            $pjudCausa->getTribunalNombre()
        ];
        
        $pjudCuadernos = $vwPjudCuadernosRepository->findBy(['pjudCausa'=>$pjudCausa->getId()],['id'=>'ASC']);
        $cuadernos = array_map(function ($c) {
            return [
                'id' => $c->getId(),
                'nombre' => $c->getNombre() ?? '',
                'totalMovimientos' => $c->getTotalMovimientos() ?? 0
            ];
        }, $pjudCuadernos);
        $primerCuardernoid=$cuadernos[0]['id'] ?? null;
        $ebook = $pjudEbookRepository->findOneBy(['pjudCausa'=>$pjudCausa->getId()]);
        /*$pjudMovimientos = $pjudMovimientoRepository->findBy(['pjudCausa'=>$pjudCausa->getId(),'cuadernoId'=>$primerCuardernoid],['id'=>'DESC']);

        $movimientosDetallados = array_map(function (VwPjudMovimientos $m) {
                                    return [
                                        'folio' => $m->getFolio() ?? '',
                                        'tienePdf' => $m->getTienePdf(),
                                        'etapa' => $m->getEtapa() ?? '',
                                        'tramite' => $m->getTramite() ?? '',
                                        'descripcion' => $m->getDescripcion() ?? '',
                                        'fecha' => $m->getFecha() ? $m->getFecha() : '',
                                        'foja' => $m->getFoja() ?? '',
                                        'pdfPrincipalNombre' => $m->getPdfPrincipalNombre() ?? null,
                                        'pdfPrincipalBase64' => $m->getPdfPrincipalBase64() ?? null,
                                        'pdfAnexoNombre' => $m->getPdfAnexoNombre() ?? null,
                                        'pdfAnexoBase64' => $m->getPdfAnexoBase64() ?? null
                                    ];
                                }, $pjudMovimientos);*/
        $movimientosDetallados=[];
        $pdfs = [];
        $totalPdfs = 0;

        /*foreach ($pjudMovimientos as $mov) {
           
            // Formato legacy (9 elementos por fila)
            $movimientosLegacy[] = [
                $mov->getFolio() ?? '',
                $mov->getTienePdf() ? 'Descargar Documento' : '',
                $mov->getFolio() ?? '',
                $mov->getEtapa() ?? '',
                $mov->getTramite() ?? '',
                $mov->getDescripcion() ?? '',
                $mov->getFecha() ?? '',
                $mov->getFoja() ?? '',
                '' // Georef
            ];

            // Contar PDFs
            if ($mov->getPdfPrincipalNombre()) {
                $totalPdfs++;
                if ($mov->getPdfPrincipalBase64()) {
                    $pdfs[$mov->getPdfPrincipalNombre()] = $mov->getPdfPrincipalBase64();
                }
            }
            if ($mov->getPdfAnexoNombre()) {
                $totalPdfs++;
                if ($mov->getPdfAnexoBase64()) {
                    $pdfs[$mov->getPdfAnexoNombre()] = $mov->getPdfAnexoBase64();
                }
            }
           
        }
        array_unshift($movimientosLegacy, [], $cabecera);*/
      
        $litigantes_map = [];
        $exhortos_map = [];

        $informacion_receptor = $pjudInformacionReceptorRepository->findBy(['pjudCausa'=>$pjudCausa->getId()]);
        $informacion_receptor_map = array_map(function(PjudInformacionReceptor $i) {
            return [
                'cuaderno'=>$i->getCuaderno() ?? '',
                'datos_retiro'=>$i->getDatosRetiro() ?? '',
                'fecha_retiro'=>$i->getFechaRetiro() ?? '',
                'estado'    =>$i->getEstado() ?? '',
            ];
        }, $informacion_receptor);

       
        $escritos_map = [];

      
        $notificaciones_map = [];
        $anexos_causa = $pjudAnexoCausaRepository->findBy(['pjudCausa'=>$pjudCausa->getId()]);
        $anexos_causa_map = array_map(function(PjudAnexoCausa $a) {
            return [
                'doc'=>$a->getDoc() ?? '',
                'fecha'=>$a->getFecha() ?? '',
                'referencia'=>$a->getReferencia() ?? '',
                'doc_descargado'=>$a->getArchivoDescargado() ?? 0
            ];
        }, $anexos_causa);

        $response = [
            'legacy'=>$movimientosLegacy,
            'causa' =>[
                'rit'=>$pjudCausa->getRit() ?? '',
                'caratulado'=>$pjudCausa->getCaratulado() ?? '',
                'tribunal'=>$pjudCausa->getTribunalNombre() ?? '',
                'fechaIngreso'=>$pjudCausa->getFechaIngreso() ?? '',
                'estado'=>$pjudCausa->getEstadoAdministracion() ?? 'SIN_INFORMACION',
                'etapa'=>$pjudCausa->getEtapa() ?? '',
                'totalMovimientos'=>$pjudCausa->getTotalMovimientos(),
                'totalPdfs'=>$pjudCausa->getTotalPdfs(),
                'proceso'=>$pjudCausa->getProceso() ?? '',
                'ubicacion'=>$pjudCausa->getUbicacion() ?? '',
                'estado_proc'=>$pjudCausa->getEstado() ?? '',
                'doc_ebook'=>$pjudCausa->getDocEbook() ?? null,
                'doc_demanda'=>$pjudCausa->getDocDemanda() ?? null,
                'doc_certificado_envio'=>$pjudCausa->getDocCertificadoEnvio() ?? null,
                'doc_ebook_descargado'=>$pjudCausa->getDocEbookDescargado() ?? 0,
                'doc_demanda_descargado'=>$pjudCausa->getDocDemandaDescargado() ?? 0,
                'doc_certificado_envio_descargado'=>$pjudCausa->getDocCertificadoEnvioDescargado() ?? 0
            ],
            'cuadernos'=>$cuadernos,
            'ebook'=> $ebook ? [
                'nombre'=>$ebook->getNombreArchivo(),
                'ruta'=>$ebook->getRutaRelativa(),
                'tamano'=>$ebook->getTamanoBytes(),
                'descargado'=>$ebook->getDescargado()
            ] : null,
            'movimientos'=>$movimientosDetallados,
            'ultimoMovimiento'=>  !empty($movimientosDetallados) ? $movimientosDetallados[0]['fecha'] ?? null : null,
            'exhortos'=>$exhortos_map,
            'informacion_receptor'=>$informacion_receptor_map?? [],
            'litigantes'=> $litigantes_map ?? [],
            'escritos'=> $escritos_map ?? [],
            'notificaciones'=>$notificaciones_map ?? [],
            'anexos_causa'=>$anexos_causa_map ?? []
        ];
        if ( count($pdfs) > 0) {
            $response['pdfs'] = $pdfs;
        }
        return $this->json($response, 200);
    }
    /**
     * @Route("/{id}/obtener/{cuadernoId}", name="pjud_movimientos_obtener_por_cuaderno", methods={"GET","POST"})
     */
    function obtenerPorCuaderno(Causa $causa,int $cuadernoId,
                               PjudCausaRepository $pjudCausaRepository,
                                VwPjudMovimientosRepository $pjudMovimientoRepository,
                                PjudEscritosRepository $pjudEscritosRepository,
                                PjudExhortosRepository $pjudExhortosRepository,
                                PjudLitigantesRepository $pjudLitigantesRepository,
                                PjudNotificacionesRepository $pjudNotificacionesRepository): JsonResponse
    {
        
        $pjudCausa = $pjudCausaRepository->findOneBy(['causa'=>$causa->getId()]);
        if(!$pjudCausa){
            return $this->json(['error'=>'Causa no encontrada en Pjud'], 404);
        }
        $pjudMovimientos = $pjudMovimientoRepository->findBy(['pjudCausa'=>$pjudCausa->getId(),'cuadernoId'=>$cuadernoId],['id'=>'Desc']);
        try{
            $historial = array_map(function (VwPjudMovimientos $m) {
                                        return [
                                            'id'=>$m->getId(),
                                            'folio' => $m->getFolio() ?? '',
                                            'causa_id'=>$m->getPjudCausa()->getCausa()->getId(),
                                            'tienePdf' => $m->getTienePdf(),
                                            'etapa' => $m->getEtapa() ?? '',
                                            'tramite' => $m->getTramite() ?? '',
                                            'descripcion' => $m->getDescripcion() ?? '',
                                            'fecha' => $m->getFecha() ? $m->getFecha() : '',
                                            'foja' => $m->getFoja() ?? '',
                                            'pdfPrincipalNombre' => $m->getPdfPrincipalNombre() ?? null,
                                            'pdfPrincipalBase64' => $m->getPdfPrincipalBase64() ?? null,
                                            'pdfAnexoNombre' => $m->getPdfAnexoNombre() ?? null,
                                            'pdfAnexoBase64' => $m->getPdfAnexoBase64() ?? null,
                                            'cantidadAnexos'=>  $m->getCantidadAnexos() ?? 0,
                                            'pdfPrincipalDescargado'=>$m->getPdfPrincipalDescargado() ?? 0,
                                            'pdfAnexoDescargado'=>$m->getPdfAnexoDescargado() ?? 0
                                        ];
                                    }, $pjudMovimientos);

            $pjudNotificaciones = $pjudNotificacionesRepository->findBy(["pjudCausa"=>$pjudCausa->getId(),'cuadernoId'=>$cuadernoId]);
            $notificaciones = array_map(function (PjudNotificaciones $n) {
                                        return [
                                            'id' => $n->getId(),
                                            'rol'=> $n->getRol() ?? '',
                                            'estado_notificacion'=> $n->getEstadoNotificacion() ?? '',
                                            'tipo_notificacion'=> $n->getTipoNotificacion() ?? '',
                                            'fecha_tramite'=> $n->getFechaTramite() ?? '',
                                            'tipo_part'=> $n->getTipoPart() ?? '',
                                            'nombre'=> $n->getNombre() ?? '',
                                            'tramite'=> $n->getTramite() ?? '',
                                            'observacion'=> $n->getObservacion() ?? ''
                                        ];
                                    }, $pjudNotificaciones);

            $pjudEscritos = $pjudEscritosRepository->findBy(["pjudCausa"=>$pjudCausa->getId(),'cuadernoId'=>$cuadernoId]);
            $escritos = array_map(function (PjudEscritos $e) {
                                        return [
                                            'id' => $e->getId(),
                                            'doc'=> $e->getDoc() ?? '',
                                            'anexo'=> $e->getAnexo() ?? '',
                                            'fecha_ingreso'=> $e->getFechaIngreso() ?? '',
                                            'tipo_escrito'=> $e->getTipoEscrito() ?? '',
                                            'solicitante'=> $e->getSolicitante() ?? ''
                                        ];
                                    }, $pjudEscritos);


            $pjudLitigantes=$pjudLitigantesRepository->findBy(["pjudCausa"=>$pjudCausa->getId(),'cuadernoId'=>$cuadernoId]);
            $litigantes = array_map(function (PjudLitigantes $l) {
                                        return [
                                            'id' => $l->getId(),
                                            'razon_social'=> $l->getRazonSocial() ?? '',
                                            'rut'=> $l->getRut() ?? '',
                                            'persona'=> $l->getPersona() ?? '',
                                            'participante'=> $l->getParticipante() ?? ''
                                        ];
                                    }, $pjudLitigantes);

            $pjudExhortos=$pjudExhortosRepository->findBy(["pjudCausa"=>$pjudCausa->getId(),'cuadernoId'=>$cuadernoId]);
            $exhortos = array_map(function (PjudExhortos $e) {
                                        return [
                                            'id' => $e->getId(),
                                            'rol_origen'=> $e->getRolOrigen() ?? '',
                                            'tipo_exhorto'=>  $e->getTipoExhorto() ?? '',
                                            'rol_destino'=> $e->getRolDestino() ?? '',
                                            'fecha_orden'=> $e->getFechaOrden() ?? '',
                                            'fecha_ingreso'=> $e->getFechaIngreso() ?? null,
                                            'tribunal_destino'=> $e->getTribunalDestino() ?? '',
                                            'estado_exhorto'=> $e->getEstadoExhorto() ?? ''
                                        ];}, $pjudExhortos);

            
            $movimientosDetallados = ["historial"=>$historial,"notificaciones"=>$notificaciones,"escritos"=>$escritos,"litigantes"=>$litigantes,"exhortos"=>$exhortos];

            return $this->json($movimientosDetallados,200);
        }catch(Exception $e){
            return $this->json(["Error"=>$e->getMessage()],500);
        
        }
    }
    /**
     * @Route("/{id}/actualizar_causa", name="pjud_movimientos_actualizar_causa", methods={"GET","POST"})
     */
    function actualizarCausa(Causa $causa, PjudScraping $pjudScraping): JsonResponse
    {

        $response=[];
        
        try{
            
            $estadoContrato = $causa->getAgenda()->getStatus()->getId();
            $estado=1;
            if($estadoContrato == 13 || $estadoContrato == 15){
                $estado=0;
            }
        
            exec(
                sprintf(
                    'php %s/bin/console pjudScreaping:enviar-datos %s %s %s %s %s %s %s %s > /dev/null 2>&1 &',
                    $this->getParameter('kernel.project_dir'),
                    $causa->getRol(),
                    $causa->getLetra(),
                    $causa->getAnio(),
                    $causa->getMateriaEstrategia()->getMateria()->getPjudCompetenciaId(),
                    $causa->getCorte()->getPjudCorteId()??'',
                    $causa->getJuzgado()->getPjudTribunalId()??'',
                    $causa->getId(),
                    $estado
                )
            );
            $em = $this->getDoctrine()->getManager();
            $causa->setEstadoConsultaPjud("Consultando");
            $em->persist($causa);
            $em->flush();
        

        }catch(\Exception $e){
            $response['error']=$e->getMessage();
            return $this->json($response, 400);
        }
        return $this->json(["exito"=>true], 200);

    }

    /**
     * @Route("/{id}/anexos_movimiento", name="pjud_movimientos_anexos_movimientos" , methods={"GET","POST"})
     */
    public function anexosMovimiento(PjudMovimiento $movimiento, PjudAnexoMovimientoRepository $pjudAnexoMovimientoRepository):JsonResponse
    {
        try{
        $pjudAnexosMovimientos = $pjudAnexoMovimientoRepository->findBy(['pjudMovimiento'=>$movimiento->getId()],['id'=>'Desc']);

        $anexosMovimiento = array_map(function (PjudAnexoMovimiento $m) {
                                    return [
                                        'fecha'=>$m->getFecha(),
                                        'referencia'=>$m->getReferencia(),
                                        'nombre_archivo'=>$m->getNombreArchivo()??"",
                                        'archivo_descargado'=>$m->getArchivoDescargado()??0,
                                        'causa_id'=>$m->getPjudMovimiento()->getPjudCausa()->getCausa()->getId()
                                    ];
                                }, $pjudAnexosMovimientos);

        return $this->json($anexosMovimiento,200);
        }catch(Exception $e){
            return $this->json(["exito"=>false,"mensaje"=>$e->getMessage()],500);
        }
    }
    
}
