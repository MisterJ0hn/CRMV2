<?php

namespace App\Controller;

use App\Entity\ApiLlamado;
use App\Entity\ApiToken;
use App\Entity\PjudAnexoCausa;
use App\Entity\PjudAnexoMovimiento;
use App\Entity\PjudCausa;
use App\Entity\PjudEscritos;
use App\Entity\PjudExhortos;
use App\Entity\PjudInformacionReceptor;
use App\Entity\PjudLitigantes;
use App\Entity\PjudMovimiento;
use App\Entity\PjudNotificaciones;
use App\Entity\PjudPdf;
use App\Repository\CausaRepository;
use App\Repository\ConfiguracionRepository;
use App\Repository\PjudAnexoCausaRepository;
use App\Repository\PjudAnexoMovimientoRepository;
use App\Repository\PjudCausaRepository;
use App\Repository\PjudEscritosRepository;
use App\Repository\PjudPdfRepository;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/auth", methods={"POST"})
     */
    public function auth(
                        Request $request,
                        UsuarioRepository $users,
                        UserPasswordEncoderInterface $hasher,
                        EntityManagerInterface $em
                    ): JsonResponse 
    {
        $data = json_decode($request->getContent(), true);

        $user = $users->findOneBy(['username' => $data['username'] ?? null]);

        if (!$user || !$hasher->isPasswordValid($user, $data['password'] ?? '')) {
            return new JsonResponse(['error' => 'Credenciales inválidas'], 401);
        }

        $token = bin2hex(random_bytes(32));

        $apiToken = new ApiToken();
        $apiToken->setToken($token);
        $apiToken->setUser($user);
        $apiToken->setExpiresAt(new \DateTime('+1 day'));

        $em->persist($apiToken);
        $em->flush();

        return new JsonResponse([
            'token' => $token,
            'expires_at' => $apiToken->getExpiresAt()->format('c')
        ]);
    }

    /**
     * @Route("/api/registrar-movimientos", methods={"POST"})
     */
    public function registrar(Request $request, 
                            EntityManagerInterface $em, 
                            CausaRepository $causaRepository,
                            PjudCausaRepository $pjudCausaRepository,
                            ConfiguracionRepository $configuracionRepository): JsonResponse
    {
        $apiLlamado = new ApiLlamado();
        $data = json_decode($request->getContent(), true);

        
        $configuracion = $configuracionRepository->find(1);
        if($configuracion && $configuracion->getOcultarBase64EnTrasa()){
            $dataSinBase64 = $this->ocultarBase64Recursivo($data);
            $apiLlamado->setJsonRequest(json_encode($dataSinBase64));
        }else{
            $apiLlamado->setJsonRequest($request->getContent());
        }
         //$apiLlamado->setJsonRequest($request->getContent());
        

        $apiLlamado->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
        $mensajeErroApi="";
        if($data==null){
            $apiLlamado->setExito(0);
            $apiLlamado->setMensajeError("Payload inválido");
            $em->persist($apiLlamado);
            $em->flush();
            return $this->json([
                'success' => false,
                'message' => "Payload inválido"
            ], 500);
        }
        $crmCausa= $causaRepository->find($data['causa']['crm_causa_id']);
        try {
           
            // Si el JSON tiene una clave "Exito" con valor false, se registra el llamado como exitoso pero no se procesan los datos    
            if(isset($data['ok']) && $data['ok']===false){
                
                
                if(!$crmCausa)
                {
                    $mensajeerror="la crm_causa_id: ".$data['causa']['crm_causa_id']." No existe";
                    throw new JsonException($mensajeerror);
                }
                $crmCausa->setEstadoConsultaPjud("NoOk");

                $em->persist($crmCausa);
                $apiLlamado->setExito(1);
                $em->persist($apiLlamado);
                $em->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Datos registrados correctamente',
                    'causa_id'=>0
                ]);
                
            }
            
            
           
           

            if(!$crmCausa)
            {
                $mensajeerror="la crm_causa_id: ".$data['causa']['crm_causa_id']." No existe";
                throw new JsonException($mensajeerror);
            }
            $apiLlamado->setCausa($crmCausa);
          
            // 🔹 CAUSA
            $causa = $pjudCausaRepository->findOneBy(['causa'=>$crmCausa]);
            if(!$causa){
                $causa = new PjudCausa();
            }
            $causa->setCausa($crmCausa);
            $causa->setRit($data['causa']['rit']);
            $causa->setCaratulado($data['causa']['caratulado']);
            $causa->setTribunalNombre($data['causa']['tribunal_nombre']);
            $causa->setFechaIngreso($data['causa']['fecha_ingreso']);
            $causa->setEstado($data['causa']['estado_proc']);
            $causa->setEtapa($data['causa']['etapa']);
            $causa->setTotalMovimientos($data['causa']['total_movimientos']);
            $causa->setTotalPdfs($data['causa']['total_pdfs']);
            $causa->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
            $causa->setEstadoAdministracion($data['causa']['estado']);
            $causa->setProceso($data['causa']['procedimiento']);
            $causa->setUbicacion($data['causa']['ubicacion']);
            $causa->setDocEbook( $data['causa']['doc_ebook']);
            $causa->setDocDemanda($data['causa']['doc_demanda']);
            $causa->setDocCertificadoEnvio($data['causa']['doc_certificado_envio']);

            $em->persist($causa);
            /*$nombreArchivo="";
            if(isset($data['causa']['doc_ebook']) && isset($data['causa']['doc_ebook_base64'])){
                 $nombreArchivo=$this->grabarDocumento(
                    $data['causa']['doc_ebook_base64'],
                    $data['causa']['doc_ebook'],
                    "",
                    $data['causa']['crm_causa_id']
                );
            }
            
             $nombreArchivo="";
            if(isset($data['causa']['doc_demanda']) && isset($data['causa']['doc_demanda_base64'])){
                 $nombreArchivo=$this->grabarDocumento(
                    $data['causa']['doc_demanda_base64'],
                    $data['causa']['doc_demanda'],
                    "",
                    $data['causa']['crm_causa_id']
                );
            }
            $causa->setDocDemanda($nombreArchivo ?? null);
             $nombreArchivo="";
            if(isset($data['causa']['doc_certificado_envio']) && isset($data['causa']['doc_certificado_envio_base64'])){
                 $nombreArchivo=$this->grabarDocumento(
                    $data['causa']['doc_certificado_envio_base64'],
                    $data['causa']['doc_certificado_envio'],
                    "",
                    $data['causa']['crm_causa_id']
                );
            } 
            
            $causa->setDocCertificadoEnvio($nombreArchivo ?? null);
            $em->persist($causa);*/

            // 🔹 MOVIMIENTOS
            foreach ($data['movimientos'] ?? [] as $movData) {
                $exiteMov= $em->getRepository(PjudMovimiento::class)->findOneBy(
                        ['pjudCausa'=>$causa,
                        'folio'=>$movData['folio'],
                        'fecha'=>$movData['fecha'],
                        'descripcion'=>$movData['descripcion'],
                        'etapa'=>$movData['etapa'],
                        'tramite'=>$movData['tramite'],
                        'foja'=>$movData['foja'],
                        'indice'=>$movData['indice'],
                        'cuadernoId'=>$movData['cuaderno_id'] ?? null,
                        'cuadernoNombre'=>$movData['cuaderno_nombre'] ?? null
                        ]);
                if(!$exiteMov){
                    $mov = new PjudMovimiento();
                    $mov->setPjudCausa($causa);
                    $mov->setFolio($movData['folio']);
                    $mov->setTienePdf($movData['tiene_pdf']);
                    $mov->setEtapa($movData['etapa']);
                    $mov->setTramite($movData['tramite']);
                    $mov->setDescripcion($movData['descripcion']);
                    $mov->setFecha($movData['fecha']);
                    $mov->setFoja($movData['foja']);
                    $mov->setIndice($movData['indice']);
                    $mov->setCuadernoId($movData['cuaderno_id'] ?? null);
                    $mov->setCuadernoNombre($movData['cuaderno_nombre'] ?? null);

                    $em->persist($mov);

                    // PDFS
                    foreach ($movData['pdfs'] ?? [] as $pdfData) {
                        $pdf = new PjudPdf();
                        $pdf->setPjudMovimiento($mov);
                        $pdf->setTipo($pdfData['tipo']);
                        
                        /*$nombreArchivo="";
                        if(isset($pdfData['contenido_base64'])){
                            $nombreArchivo=$this->grabarDocumento(
                                $pdfData['contenido_base64'],
                                $pdfData['nombre_archivo'],
                                $pdfData['mime_type'],
                                $data['causa']['crm_causa_id']
                            );
                        }*/
                        $pdf->setNombreArchivo($pdfData['nombre_archivo']);
                        $pdf->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));

                        $em->persist($pdf);
                    }
                    foreach ($movData['pdfs_solicitud'] ?? [] as $anexoMov) {
                        $anexoMovimiento = new PjudAnexoMovimiento();
                        $anexoMovimiento->setPjudMovimiento($mov);
                        $anexoMovimiento->setFecha($anexoMov['fecha']);
                        $anexoMovimiento->setMimeType($anexoMov['mime_type']);
                        $anexoMovimiento->setNombreArchivo($anexoMov['nombre_archivo']);
                        $anexoMovimiento->setReferencia($anexoMov['referencia']);
                        /*if(isset($anexoMov['base64'])){
                            $nombreArchivo=$this->grabarDocumento(
                                $anexoMov['base64'],
                                $anexoMov['nombre_archivo'],
                                $anexoMov['mime_type'],
                                $data['causa']['crm_causa_id']
                            );
                        }*/

                        $em->persist($anexoMovimiento);
                    }
                }
            }
            // 🔹 ESCRITOS
            foreach ($data['escritos'] ?? [] as $escData) {
                /*$existeEscrito= $em->getRepository(PjudEscritos::class)->findOneBy(
                    ['pjudCausa'=>$causa,
                    'tipoEscrito'=>$escData['tipo_escrito'],
                    'fechaIngreso'=>$escData['fecha_ingreso'],
                    'solicitante'=>$escData['solicitante'],
                    'anexo'=>$escData['anexo']
                    ]);
                if(!$existeEscrito){*/
                    $esc = new PjudEscritos();
                    $esc->setPjudCausa($causa);
                    $esc->setFechaIngreso($escData['fecha_ingreso']);
                    $esc->setTipoEscrito($escData['tipo_escrito']);
                    $esc->setSolicitante($escData['solicitante']);
                    $esc->setCuadernoId($escData['cuaderno_id'] ?? null);
                    if(isset($escData['doc'])){
                       $esc->setDoc($escData['doc']);
                    }

                    
                    /*$nombreArchivo="";
                    if(isset($escData['doc']) && isset($escData['doc_base64'])){
                        $nombreArchivo=$this->grabarDocumento(
                            $escData['doc_base64'],
                            $escData['doc'],
                            $escData['doc_mime_type'],
                            $data['causa']['crm_causa_id']
                        );
                    }
                    if(isset($escData['anexo']) && isset($escData['anexo_base64'])){
                        $nombreArchivo=$this->grabarDocumento(
                            $escData['anexo_base64'],
                            $escData['anexo'],
                            $escData['anexo_mime_type'],
                            $data['causa']['crm_causa_id']
                        );
                    }
                    $esc->setDoc($nombreArchivo);*/
                    $em->persist($esc);
                //}
            }

            // 🔹 EXHORTOS
            foreach ($data['exhortos'] ?? [] as $exhData) {
                $existeExhorto= $em->getRepository(PjudExhortos::class)->findOneBy(
                    ['pjudCausa'=>$causa,
                    'rolOrigen'=>$exhData['rol_origen'],
                    'tipoExhorto'=>$exhData['tipo_exhorto'],
                    'rolDestino'=>$exhData['rol_destino'],
                    'fechaOrden'=>$exhData['fecha_orden'],
                    'fechaIngreso'=>$exhData['fecha_ingreso'],
                    'tribunalDestino'=>$exhData['tribunal_destino'],
                    'estadoExhorto'=>$exhData['estado_exhorto']
                    ]);
                if(!$existeExhorto){
                    $exh = new PjudExhortos();
                    $exh->setPjudCausa($causa);
                    $exh->setRolOrigen($exhData['rol_origen']);
                    $exh->setTipoExhorto($exhData['tipo_exhorto']);
                    $exh->setRolDestino($exhData['rol_destino']);
                    $exh->setFechaOrden($exhData['fecha_orden']);
                    $exh->setFechaIngreso($exhData['fecha_ingreso']);
                    $exh->setTribunalDestino($exhData['tribunal_destino']);
                    $exh->setEstadoExhorto($exhData['estado_exhorto']);
                    $exh->setCuadernoId($exhData['cuaderno_id'] ?? null);

                    $em->persist($exh);
                }
            }

            // Información Receptor
            foreach ($data['informacion_receptor'] as $infoData) {
                $existeInfoReceptor= $em->getRepository(PjudInformacionReceptor::class)->findOneBy(
                    ['pjudCausa'=>$causa,
                    'Cuaderno'=>$infoData['cuaderno'],
                    'datosRetiro'=>$infoData['datos_retiro'],
                    'fechaRetiro'=>$infoData['fecha_retiro'],
                    'estado'=>$infoData['estado']
                    ]);
                if(!$existeInfoReceptor){
                    $info = new PjudInformacionReceptor();
                    $info->setPjudCausa($causa);
                    $info->setCuaderno($infoData['cuaderno']);
                    $info->setDatosRetiro($infoData['datos_retiro']);
                    $info->setFechaRetiro($infoData['fecha_retiro']);
                    $info->setEstado($infoData['estado']);
                    $em->persist($info);
                }
            }

            // Litigantes
            foreach($data['litigantes'] as $litData) {
                /*$existeLitigante= $em->getRepository(PjudLitigantes::class)->findOneBy(
                    ['pjudCausa'=>$causa,
                    'participante'=>$litData['participante'],
                    'rut'=>$litData['rut'],
                    'persona'=>$litData['persona'],
                    'razonSocial'=>$litData['razon_social']
                    ]);
                if(!$existeLitigante){*/
                    $lit = new PjudLitigantes();
                    $lit->setPjudCausa($causa);
                    $lit->setParticipante($litData['participante']);
                    $lit->setRut($litData['rut']);
                    $lit->setPersona($litData['persona']);
                    $lit->setRazonSocial($litData['razon_social']);
                    $lit->setCuadernoId($litData['cuaderno_id'] ?? null);

                    $em->persist($lit);
                //}
            }

            // notificaciones
            foreach($data['notificaciones'] as $notData) {
                /*$existeNotificacion= $em->getRepository(PjudNotificaciones::class)->findOneBy(
                    ['pjudCausa'=>$causa,
                    'rol'=>$notData['rol'],
                    'estadoNotificacion'=>$notData['estado_notificacion'],
                    'tipoNotificacion'=>$notData['tipo_notificacion'],
                    'fechaTramite'=>$notData['fecha_tramite'],
                    'tipoPart'=>$notData['tipo_part'],
                    'nombre'=>$notData['nombre'],
                    'tramite'=>$notData['tramite'],
                    'observacion'=>$notData['observacion']
                    ]);
                if($existeNotificacion){*/
                    $not = new PjudNotificaciones();
                    $not->setPjudCausa($causa);
                    $not->setRol($notData['rol']);
                    $not->setEstadoNotificacion($notData['estado_notificacion']);
                    $not->setTipoNotificacion($notData['tipo_notificacion']);
                    $not->setFechaTramite($notData['fecha_tramite']);
                    $not->setTipoPart($notData['tipo_part']);
                    $not->setNombre($notData['nombre']);
                    $not->setTramite($notData['tramite']);
                    $not->setObservacion($notData['observacion']);
                    $not->setCuadernoId($notData['cuaderno_id'] ?? null);
                    
                    $em->persist($not);
               // }
            }

            //Anexos
            foreach($data['anexos'] as $anexoData) {
               /* $existeAnexo= $em->getRepository(PjudAnexoCausa::class)->findOneBy(
                    ['pjudCausa'=>$causa,
                    'doc'=>$anexoData['nombre_archivo'],
                    'fecha'=>$anexoData['fecha_documento'],
                    'referencia'=>$anexoData['referencia']
                    ]);
                if(!$existeAnexo){*/
                    $anexo = new PjudAnexoCausa();
                    $anexo->setPjudCausa($causa);
                    $anexo->setDoc($anexoData['nombre_archivo']);
                    $anexo->setFecha($anexoData['fecha_documento']);
                    $anexo->setReferencia($anexoData['referencia']);
                    /*$nombreArchivo="";
                    if(isset($anexoData['contenido_base64'])){
                        $nombreArchivo=$this->grabarDocumento(
                            $anexoData['contenido_base64'],
                            $anexoData['nombre_archivo'],
                            "",
                            $data['causa']['crm_causa_id']
                        );
                    }
                    $anexo->setDoc($nombreArchivo);*/
                    $em->persist($anexo);
                //}
            }
            $crmCausa->setEstadoConsultaPjud("Ok");
            $em->persist($crmCausa);

          

            $apiLlamado->setExito(1);
            $em->persist($apiLlamado);
            $em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Datos registrados correctamente',
                'causa_id'=>$causa->getId()
            ]);


        } 
        catch(\JsonException $e){
            $mensajeErroApi=$e->getMessage();
            $apiLlamado->setExito(0);
            $apiLlamado->setMensajeError($e->getLine()." - ".$mensajeErroApi." - ".$e->getTraceAsString());
            $em->persist($apiLlamado);
            
            if($crmCausa)
            {           
                $crmCausa->setEstadoConsultaPjud("NoOk");
                $em->persist($crmCausa);
            }
                
            $em->flush();
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
        catch (\Exception $e) {
            $mensajeErroApi=$e->getMessage();
            $apiLlamado->setExito(0);
            $apiLlamado->setMensajeError($e->getLine()." - ".$mensajeErroApi." - ".$e->getTraceAsString());
            $em->persist($apiLlamado);
            
            if($crmCausa)
            {           
                $crmCausa->setEstadoConsultaPjud("NoOk");
                $em->persist($crmCausa);
            }

            $em->flush();
            return $this->json([
                'success' => false,
                'message' => $mensajeErroApi
            ], 500);
        }
        
    }
    /*
    * {'crm_causa_id':123,'contenido_base64':'contenido', 'nombre_archivo':'documento.doc', 'mime_type':'application/word'}
    */
    /**
     * @Route("/api/registrar-documentos", methods={"POST"})
     */ 
    function registrarDocumento(Request $request, 
                            EntityManagerInterface $em, 
                            CausaRepository $causaRepository,
                            PjudCausaRepository $pjudCausaRepository,
                            ConfiguracionRepository $configuracionRepository,
                            PjudAnexoCausaRepository $pjudAnexoCausaRepository,
                            PjudAnexoMovimientoRepository $pjudAnexoMovimientoRepository,
                            PjudEscritosRepository $pjudEscritosRepository,
                            PjudPdfRepository $pjudPdfRepository
                            ){
        $apiLlamado = new ApiLlamado();
        $data = json_decode($request->getContent(), true);

        
        $configuracion = $configuracionRepository->find(1);
        if($configuracion && $configuracion->getOcultarBase64EnTrasa()){
            $dataSinBase64 = $this->ocultarBase64Recursivo($data);
            $apiLlamado->setJsonRequest(json_encode($dataSinBase64));
        }else{
            $apiLlamado->setJsonRequest($request->getContent());
        }
         //$apiLlamado->setJsonRequest($request->getContent());
        

        $apiLlamado->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
        $mensajeErroApi="";
        if($data==null){
            $apiLlamado->setExito(0);
            $apiLlamado->setMensajeError("Payload inválido");
            $em->persist($apiLlamado);
            $em->flush();
            return $this->json([
                'success' => false,
                'message' => "Payload inválido"
            ], 500);
        }
        try{
            $causaId= $data['crm_causa_id'];
            $base64= $data['contenido_base64'];
            $nombreArchivo = $data['nombre_archivo'];
            $mimeType=$data['mime_type'];
            $causa = $pjudCausaRepository->findOneBy(['causa'=>$causaId]);
           
            $resultado=$this->grabarDocumento(
                $base64,
                $nombreArchivo,
                $mimeType,
                $causaId
            );

            if($resultado!=true){
                throw new Exception($resultado);
            }
            //Validamos los archivos y marcamos como true a descargado
            $causaEbook = $pjudCausaRepository->findOneBy(['docEbook'=>$nombreArchivo]);
            if($causaEbook){
                $causaEbook->setDocEbookDescargado(1);
                $em->persist($causaEbook);
                $em->flush();
            }
            $causaDemandaDescargado = $pjudCausaRepository->findOneBy(['docDemanda'=>$nombreArchivo]);
            if($causaDemandaDescargado){
                $causaDemandaDescargado->setDocDemandaDescargado(1);
                 $em->persist($causaDemandaDescargado);
                $em->flush();
            }
            $causaCertificadoEnvioDescargado = $pjudCausaRepository->findOneBy(['docCertificadoEnvio'=>$nombreArchivo]);
            if($causaCertificadoEnvioDescargado){
                $causaCertificadoEnvioDescargado->setDocCertificadoEnvioDescargado(1);
                 $em->persist($causaCertificadoEnvioDescargado);
                $em->flush();
            }
            $anexoCausaDoc = $pjudAnexoCausaRepository->findOneBy(['doc'=>$nombreArchivo]);
             if($anexoCausaDoc){
                $anexoCausaDoc->setArchivoDescargado(1);
                $em->persist($anexoCausaDoc);
                $em->flush();
            }
             
            $anexoMovimientoDoc = $pjudAnexoMovimientoRepository->findOneBy(['nombreArchivo'=>$nombreArchivo]);
            if($anexoMovimientoDoc){
                $anexoMovimientoDoc->setArchivoDescargado(1);
                $em->persist($anexoMovimientoDoc);
                $em->flush();
            }
            $escritos = $pjudEscritosRepository->findOneBy(['doc'=>$nombreArchivo]);
            if($escritos){
                $escritos->setDocDescargado(1);
                $em->persist($escritos);
                $em->flush();
            }
           
            $pdfMovimiento = $pjudPdfRepository->findOneBy(['nombreArchivo'=>$nombreArchivo]);
            if($pdfMovimiento){
                $pdfMovimiento->setArchivoDescargado(1);
                $em->persist($pdfMovimiento);
                $em->flush();
            }
            if($causa){
                $apiLlamado->setCausa($causa->getCausa());
            }
            $apiLlamado->setExito(1);
            $em->persist($apiLlamado);
            $em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Datos registrados correctamente',
                'causa_id'=>$causaId
            ]);
        }catch(Exception $e){
            $apiLlamado->setExito(0);
            $apiLlamado->setMensajeError($e->getMessage());
            $em->persist($apiLlamado);
            $em->flush();
            
            return $this->json([
            'success' => false,
            'message' => "Registrar Documentos: ". $e->getMessage()
            ],500);
        }

    }

    function grabarDocumento($base64String, $nombreArchivo,$mime_type,$causaId) {

        try {
            $decoded = base64_decode($base64String);
            if ($decoded === false) return null;

            $folder = $this->getParameter('url_root').$this->getParameter('causa_pjud') . '/' . $causaId;
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            // Nombre estable/idempotente:
            // - Usar el nombre recibido (sin random) para permitir carga incremental por nombre.
            // - Si el archivo ya existe, no re-escribir (idempotencia).
            $safeName = preg_replace('/[^a-zA-Z0-9._-]+/', '_', (string)$nombreArchivo);
            $safeName = trim($safeName, '._-');
            if ($safeName === '') $safeName = 'documento';

            $extFromMime = $this->obtenerExtensionPorMime($mime_type) ?? null;
            $extFromName = null;
            $pos = strrpos($safeName, '.');
            if ($pos !== false && $pos > 0) {
                $extFromName = strtolower(substr($safeName, $pos + 1));
                $safeBase = substr($safeName, 0, $pos);
            } else {
                $safeBase = $safeName;
            }

            $extension = $extFromMime ?: ($extFromName ?: 'pdf');
            $finalName = $safeBase . '.' . $extension;
            $fullPath = $folder . '/' . $finalName;

            if (file_exists($fullPath)) {
                return $finalName;
            }

            file_put_contents($fullPath, $decoded);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }
    private function ocultarBase64Recursivo(array $data): array
    {
        foreach ($data as $key => $value) {

            // Si es array → seguir recorriendo
            if (is_array($value)) {
                $data[$key] = $this->ocultarBase64Recursivo($value);
                continue;
            }

            // Si la clave contiene "base64"
            if (stripos($key, 'base64') !== false && is_string($value)) {

                $length = strlen($value);

                $data[$key] = sprintf(
                    '[BASE64 OCULTO - %d caracteres]',
                    $length
                );
            }
        }

        return $data;
    }
    function obtenerExtensionPorMime($mimeType)
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp',

            'application/pdf' => 'pdf',
            'application/zip' => 'zip',
            'application/x-rar-compressed' => 'rar',
            'application/json' => 'json',

            'text/plain' => 'txt',
            'text/csv' => 'csv',
            'text/html' => 'html',

            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',

            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',

            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx'
        ];

        return $map[$mimeType] ?? null;
    }
}
