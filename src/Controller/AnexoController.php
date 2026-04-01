<?php

namespace App\Controller;

use App\Entity\Causa;
use App\Entity\Configuracion;
use App\Entity\Contrato;
use App\Entity\ContratoAnexo;
use App\Entity\ContratoHistoricoSuscripcion;
use App\Entity\Cuota;
use App\Entity\Usuario;
use App\Entity\VirtualPosLog;
use App\Form\ContratoAnexoType;
use App\Repository\CausaRepository;
use App\Repository\ConfiguracionRepository;
use App\Repository\ContratoAnexoRepository;
use App\Repository\CuentaMateriaRepository;
use App\Repository\CuotaRepository;
use App\Repository\DiasPagoRepository;
use App\Repository\JuzgadoCuentaRepository;
use App\Repository\JuzgadoRepository;
use App\Repository\MateriaEstrategiaRepository;
use App\Service\VirtualPos;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Enum\OrigenAnexo;



/**
 * @Route("/anexo")
 */
class AnexoController extends AbstractController
{
    
    /**
     * @Route("/{id}", name="anexo_index", methods={"GET","POST"})
     */
    public function index(Contrato $contrato,ContratoAnexoRepository $contratoAnexoRepository, Request $request ): Response
    {
        $this->denyAccessUnlessGranted('view','anexo');
        $inicioSuscripcion=$request->query->get('inicio_suscripcion');
        $url="";
        if($inicioSuscripcion){
            $url= $this->getParameter("url_web")."/suscripcion/". $contrato->getSesionSuscripcion();
        }
        return $this->render('anexo/index.html.twig', [
            'pagina'            => 'Anexos',
            'contrato'          =>$contrato,
            'contratoAnexos'    =>$contratoAnexoRepository->findBy(['contrato'=>$contrato, 'estado'=>null]),
            'contratoAnexosNulo'=>$contratoAnexoRepository->findBy(['contrato'=>$contrato, 'estado'=>0]),
            'urlSuscripcion'    =>$url,
            'inicioSuscripcion' => $inicioSuscripcion
        ]);
    }

    /**
     * @Route("/{id}/new", name="anexo_new", methods={"GET","POST"})
     */
    public function crear(Contrato $contrato,Request $request): Response
    {
        $this->denyAccessUnlessGranted('create','anexo');

        $origen = $request->getSession()->get('origen_anexo');
  
        $tiposAnexo=[["id"=>1,"nombre"=>"Agregar Causa"],
            ["id"=>2,"nombre"=>"Extensión del plazo"],
            ["id"=>3,"nombre"=>"Renegociación"]];

        if($origen!=null){
            if(trim($origen)==trim(OrigenAnexo::COBRANZA_IA)){
                $tiposAnexo=[
                    ["id"=>3,"nombre"=>"Renegociación"]
                ];
            }
            if(trim($origen)==trim(OrigenAnexo::COBRANZA)){
                $tiposAnexo=[
                    ["id"=>3,"nombre"=>"Renegociación"]
                ];
            }
            if(trim($origen)==trim(OrigenAnexo::INCUMPLIMIENTO)){
                $tiposAnexo=[
                    ["id"=>3,"nombre"=>"Renegociación"]
                ];
            }
        }
   
        return $this->render('anexo/crearAnexo.html.twig', [
            'pagina' => 'Nuevo Anexo',
            'contrato'=>$contrato,
            'tiposAnexo' => $tiposAnexo
        ]);
    }

    /**
     * @Route("/{id}/causas", name="anexo_causas", methods={"GET","POST"})
     */
    public function causas(Contrato $contrato, 
                            CuotaRepository $cuotaRepository, 
                            JuzgadoRepository $juzgadoRepository, 
                            CuentaMateriaRepository $cuentaMateriaRepository,
                            DiasPagoRepository $diasPagoRepository,
                            ContratoAnexoRepository $contratoAnexoRepository,
                            MateriaEstrategiaRepository $materiaEstrategiaRepository,
                            JuzgadoCuentaRepository $juzgadoCuentaRepository,
                            CausaRepository $causaRepository,
                            ConfiguracionRepository $configuracionRepository,
                            Request $request): Response
    {
        $this->denyAccessUnlessGranted('create','anexo');
        $user = $this->getUser();
        $juzgados=$juzgadoRepository->findAll();
        $cuotas=$cuotaRepository->findBy(['contrato'=>$contrato,  'anular'=>null]);
        $contratoAnexo=new ContratoAnexo();
        //$contratoAnexo->setVigencia();
        $contratoAnexo->setIsDesiste(false);
        $contratoAnexo->setFechaCreacion(new \DateTime(date('Y-m-d H:i:s')));
        $contratoAnexo->setFechaPrimerPago(new \DateTime(date($request->request->get('txtFechaPago')."-1 00:00:00")));
        $contratoAnexo->setContrato($contrato);
        $contratoAnexo->setTipoAnexo(1);
        $contratoAnexo->setVigencia($contrato->getAgenda()->getCuenta()->getVigenciaContratos());
        $contratoAnexo->setUsuarioRegistro($user);
        $form = $this->createForm(ContratoAnexoType::class, $contratoAnexo);

        $ultAnexo=$contratoAnexoRepository->findOneBy(['contrato'=>$contrato,'isDesiste'=>false],['folio'=>'desc']);
            
        $folio=1;
        $ultFolio=null;
        if($ultAnexo){
            $folio=$ultAnexo->getFolio()+1;
            $ultFolio=$ultAnexo->getFolio();

        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contratoAnexo->setDiasPago($request->request->get('chkDiasPago'));
            
            $contratoAnexo->setFolio($folio);

            $this->getDoctrine()->getManager()->flush();
            
            $vigencia = $request->request->get('vigencia');
            $contratoAnexo->setVigencia($vigencia);
            
            $entityManager = $this->getDoctrine()->getManager();

            

            $submaterias=$request->request->get('hdSubMateria');
            $letra = $request->request->get('hdLetraCausa');
            $rol = $request->request->get('hdRolCausa');
            $anio = $request->request->get('hdAnioCausa');
            $caratulados=$request->request->get('hdCaratulado');
            $hdjuzgados=$request->request->get('hdJuzgado');

            //Suscripción crear o cancelar
            $cancelarSuscripcion = $request->request->get('chkCancelarSuscripcion');
            //cancelar suscripción
            if($cancelarSuscripcion==1){
                $this->cancelarSuscripcion($contrato,$configuracionRepository->find(1),$user);
                $contratoAnexo->setCancelaSuscripcion(1);
            }

            //suscribir
            $aceptarSuscripcion = $request->request->get('chkAceptaSuscripcion');
            if($aceptarSuscripcion==1){
                $this->crearSuscripcion($contrato);   
                $contratoAnexo->setAceptaSuscripcion(1);
            }
            $entityManager->persist($contratoAnexo);
            $entityManager->flush();

            $this->calularCuotas($contratoAnexo);

            //recalamos las cuotas en virtual pos
            if($contrato->getEstadoSuscripcion()=="ACTIVA"){
                $configuracion = $configuracionRepository->find(1);
                $this->regenerarCuotasVirtualPos($contrato,$configuracion);
            }

            $countCausa=count($submaterias);
            for ($i=0; $i < $countCausa ; $i++) { 
                
                $causa=new Causa();
                $causa->setEstado(1);
                $causa->setAgenda($contrato->getAgenda());
               // echo $contratoAnexo->getId();
                $causa->setAnexo($contratoAnexoRepository->find($contratoAnexo->getId()));
                if(null !== $letra[$i]){
                    $causa->setLetra($letra[$i]);
                }
                if(null !== $rol[$i]){
                    $causa->setRol($rol[$i]);
                }
                if(null !== $anio[$i] && $anio[$i]!=="" ){
                    $causa->setAnio($anio[$i]);
                }
                if(null !== $caratulados[$i]){
                    $causa->setCausaNombre($caratulados[$i]);
                }
                if(null !== $submaterias[$i]){
                    $causa->setMateriaEstrategia($materiaEstrategiaRepository->find($submaterias[$i]));
                }
                if(null !== $hdjuzgados[$i]){
                    $juzgado=$juzgadoRepository->find($hdjuzgados[$i]);
                    $causa->setJuzgado($juzgado);
                    if($juzgado){                
                        if($juzgado->getCorte()!=null){
                            $causa->setCorte($juzgado->getCorte());
                        }
                    }
                }
                $entityManager->persist($causa);
                $entityManager->flush();
            }
            $primeraCuotaVigente=$cuotaRepository->findOneByPrimeraVigente($contratoAnexo->getContrato()->getId());

            if($primeraCuotaVigente != null ){
                $contrato->setProximoVencimiento($primeraCuotaVigente->getFechaPago());
                $entityManager->persist($contrato);
                $entityManager->flush();
            }
            return $this->redirectToRoute('anexo_pdf',['id'=>$contratoAnexo->getId()]);
        }

        return $this->render('anexo/_anexoCausas.html.twig', [
            'form'=> $form->createView(),
            'pagina' => 'Agregar causa',
            'ultFolio'=>$ultFolio,
            'contratoAnexo'=>$contratoAnexo,
            'ultAnexo'=>$ultAnexo,
            'contrato'=>$contrato,
            'cuotas'=>$cuotas,
            'juzgados'=>$juzgados,
            'diasPagos'=>$diasPagoRepository->findAll(),
            'contrato_causas'=>$causaRepository->findBy(['agenda'=>$contrato->getAgenda(),'estado'=>1]),
            'cuenta_materias'=>$cuentaMateriaRepository->findBy(['cuenta'=>$contrato->getAgenda()->getCuenta(),'estado'=>1]),
        ]);

    }

  

    /**
     * @Route("/{id}/extender", name="anexo_extender", methods={"GET","POST"})
     */
    public function extender(Contrato $contrato, 
                            CuotaRepository $cuotaRepository, 
                            Request $request,
                            DiasPagoRepository $diasPagoRepository,
                            ContratoAnexoRepository $contratoAnexoRepository,
                            MateriaEstrategiaRepository $materiaEstrategiaRepository,
                            JuzgadoCuentaRepository $juzgadoCuentaRepository,
                            ConfiguracionRepository $configuracionRepository
                            ): Response
    {  
        $this->denyAccessUnlessGranted('create','anexo');
        $user = $this->getUser();
        $cuotas=$cuotaRepository->findBy(['contrato'=>$contrato,  'anular'=>null]);
        $contratoAnexo=new ContratoAnexo();
        //$contratoAnexo->setVigencia(24);
        $contratoAnexo->setIsDesiste(false);
        $contratoAnexo->setFechaCreacion(new \DateTime(date('Y-m-d  H:i:s')));
        $contratoAnexo->setFechaPrimerPago(new \DateTime(date($request->request->get('txtFechaPago')."-1 00:00:00")));
        $contratoAnexo->setUsuarioRegistro($user);
        $contratoAnexo->setContrato($contrato);
        $contratoAnexo->setTipoAnexo(2);
        $contratoAnexo->setVigencia($contrato->getAgenda()->getCuenta()->getVigenciaContratos());
        $form = $this->createForm(ContratoAnexoType::class, $contratoAnexo);
        
        $ultAnexo=$contratoAnexoRepository->findOneBy(['contrato'=>$contrato,'isDesiste'=>false],['folio'=>'desc']);

        $folio=1;
        $ultFolio=null;
        if($ultAnexo){
            $folio=$ultAnexo->getFolio()+1;
            $ultFolio=$ultAnexo->getFolio();

        }
        $contratoAnexo->setFolio($folio);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            

            $contratoAnexo->setDiasPago($request->request->get('chkDiasPago'));
            $vigencia = $request->request->get('vigencia');
            $contratoAnexo->setVigencia($vigencia);
            $entityManager = $this->getDoctrine()->getManager();
           
            

            //Anulando cuotas anteriores
            $detalleCuotas=$contrato->getDetalleCuotas();
            foreach($detalleCuotas as $detalleCuota){
                $detalleCuota->setAnular(1);
                $entityManager->persist($detalleCuota);
                $entityManager->flush();
            }

            $submaterias=$request->request->get('hdSubMateria');
            $letra = $request->request->get('hdLetraCausa');
            $rol = $request->request->get('hdRolCausa');
            $anio = $request->request->get('hdAnioCausa');
            $caratulados=$request->request->get('hdCaratulado');
            $hdjuzgados=$request->request->get('hdJuzgado');

            //Suscripción crear o cancelar
            $cancelarSuscripcion = $request->request->get('chkCancelarSuscripcion');
            //cancelar suscripción
            if($cancelarSuscripcion==1){
                $this->cancelarSuscripcion($contrato,$configuracionRepository->find(1),$user);
                $contratoAnexo->setCancelaSuscripcion(1);
            }

            //suscribir
            $aceptarSuscripcion = $request->request->get('chkAceptaSuscripcion');
            if($aceptarSuscripcion==1){
                $this->crearSuscripcion($contrato);   
                $contratoAnexo->setAceptaSuscripcion(1);
            }
            $entityManager->persist($contratoAnexo);
            $entityManager->flush();

            
            $this->calularCuotas($contratoAnexo);

            //recalamos las cuotas en virtual pos
            if($contrato->getEstadoSuscripcion()=="ACTIVA"){
                $configuracion = $configuracionRepository->find(1);
                $this->regenerarCuotasVirtualPos($contrato,$configuracion);
            }

            $this->calularCuotas($contratoAnexo);
            
            //Recalculamos las cuotas en virtual pos
            if($contrato->getEstadoSuscripcion()=="ACTIVA"){
                $configuracion = $configuracionRepository->find(1);
                $this->regenerarCuotasVirtualPos($contrato,$configuracion);
            }

            $countCausa=count($submaterias);
            for ($i=0; $i < $countCausa ; $i++) { 
                
                $causa=new Causa();
                $causa->setEstado(1);
                $causa->setAgenda($contrato->getAgenda());
               // echo $contratoAnexo->getId();
                $causa->setAnexo($contratoAnexoRepository->find($contratoAnexo->getId()));
                if(null !== $letra[$i]){
                    $causa->setLetra($letra[$i]);
                }
                if(null !== $rol[$i]){
                    $causa->setRol($rol[$i]);
                }
                if(null !== $anio[$i]){
                    $causa->setAnio($anio[$i]);
                }
                if(null !== $caratulados[$i]){
                    $causa->setCausaNombre($caratulados[$i]);
                }
                if(null !== $submaterias[$i]){
                    $causa->setMateriaEstrategia($materiaEstrategiaRepository->find($submaterias[$i]));
                }
                if(null !== $hdjuzgados[$i]){
                    $causa->setJuzgadoCuenta($juzgadoCuentaRepository->find($hdjuzgados[$i]));
                }
                
                $entityManager->persist($causa);
                $entityManager->flush();

            }
            

            $primeraCuotaVigente=$cuotaRepository->findOneByPrimeraVigente($contratoAnexo->getContrato()->getId());

            if($primeraCuotaVigente != null ){
                $contrato->setProximoVencimiento($primeraCuotaVigente->getFechaPago());
                $entityManager->persist($contrato);
                $entityManager->flush();
            }

            return $this->redirectToRoute('anexo_pdf',['id'=>$contratoAnexo->getId()]);
        }

        return $this->render('anexo/_anexoExtender.html.twig', [
            'pagina' => 'Extensión del plazo',
            'form'=> $form->createView(),
            'contrato'=>$contrato,
            'contratoAnexo'=>$contratoAnexo,
            'cuotas'=>$cuotas,
            'ultFolio'=>$ultFolio,
            'ultAnexo'=>$ultAnexo,
            'diasPagos'=>$diasPagoRepository->findAll(),
            
        ]);

    }

    /**
     * @Route("/{id}/renegociar", name="anexo_renegociar", methods={"GET","POST"})
     */
    public function renegociar(Contrato $contrato, 
                                CuotaRepository $cuotaRepository, 
                                Request $request,
                                DiasPagoRepository $diasPagoRepository,
                                ContratoAnexoRepository $contratoAnexoRepository,
                                ConfiguracionRepository $configuracionRepository): Response
                                {

        $this->denyAccessUnlessGranted('create','anexo');
        $user = $this->getUser();
        $cuotas=$cuotaRepository->findBy(['contrato'=>$contrato, 'anular'=>null]);
        $contratoAnexo=new ContratoAnexo();
        //$contratoAnexo->setVigencia(24);
        $contratoAnexo->setIsDesiste(false);
        $contratoAnexo->setFechaCreacion(new \DateTime(date('Y-m-d  H:i:s')));
        $contratoAnexo->setFechaPrimerPago(new \DateTime(date($request->request->get('txtFechaPago')."-1 00:00:00")));
        $contratoAnexo->setContrato($contrato);
        $contratoAnexo->setTipoAnexo(3);
        $contratoAnexo->setVigencia($contrato->getAgenda()->getCuenta()->getVigenciaContratos());
        $contratoAnexo->setUsuarioRegistro($user);
        $form = $this->createForm(ContratoAnexoType::class, $contratoAnexo);
        
        $ultAnexo=$contratoAnexoRepository->findOneBy(['contrato'=>$contrato,'isDesiste'=>false],['folio'=>'desc']);

        $folio=1;
        $ultFolio=null;
        if($ultAnexo){
            $folio=$ultAnexo->getFolio()+1;
            $ultFolio=$ultAnexo->getFolio();
        }
        $contratoAnexo->setFolio($folio);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $contratoAnexo->setDiasPago($request->request->get('chkDiasPago'));
            $vigencia = $request->request->get('vigencia');
            $contratoAnexo->setVigencia($vigencia);

            
            //Anulando cuotas anteriores
            /*$detalleCuotas=$contrato->getDetalleCuotas();
            foreach($detalleCuotas as $detalleCuota){
                $detalleCuota->setAnular(1);
                $entityManager->persist($detalleCuota);
                $entityManager->flush();
            }*/
                //Suscripción crear o cancelar
            $cancelarSuscripcion = $request->request->get('chkCancelarSuscripcion');
            //cancelar suscripción
            if($cancelarSuscripcion==1){
                $this->cancelarSuscripcion($contrato,$configuracionRepository->find(1),$user);
                $contratoAnexo->setCancelaSuscripcion(1);
            }

            //suscribir
            $aceptarSuscripcion = $request->request->get('chkAceptaSuscripcion');
            if($aceptarSuscripcion==1){
                $this->crearSuscripcion($contrato);   
                $contratoAnexo->setAceptaSuscripcion(1);
            }
            $entityManager->persist($contratoAnexo);
            $entityManager->flush();

            $this->calularCuotas($contratoAnexo);

            //Regeneramos las cuotas en virtual pos
            if($contrato->getEstadoSuscripcion()=="ACTIVA"){
                $configuracion = $configuracionRepository->find(1);
                $this->regenerarCuotasVirtualPos($contrato,$configuracion);
            }
            
            $primeraCuotaVigente=$cuotaRepository->findOneByPrimeraVigente($contratoAnexo->getContrato()->getId());

            if($primeraCuotaVigente != null ){
                $contrato->setProximoVencimiento($primeraCuotaVigente->getFechaPago());
                $entityManager->persist($contrato);
                $entityManager->flush();
            }
            return $this->redirectToRoute('anexo_pdf',['id'=>$contratoAnexo->getId()]);
        }


        return $this->render('anexo/_anexoRenegociar.html.twig', [
            'pagina' => 'Renegociación',
            'form'=> $form->createView(),
            'contrato'=>$contrato,
            'cuotas'=>$cuotas,
            'ultFolio'=>$ultFolio,
            'ultAnexo'=>$ultAnexo,
            'contratoAnexo'=>$contratoAnexo,
            'diasPagos'=>$diasPagoRepository->findAll(),
        ]);

    }

    /**
     * @Route("/{id}/eliminar", name="anexo_eliminar", methods={"GET","POST"})
     */
    public function eliminar(ContratoAnexo $contratoAnexo,
                            ContratoAnexoRepository $contratoAnexoRepository): Response
    {
        $this->denyAccessUnlessGranted('create','anexo');

        $entityManager = $this->getDoctrine()->getManager();


        //Eliminando un datos de anexo:::
        $contrato=$contratoAnexo->getContrato();
        if($contratoAnexo->getCausas()){
            $causas=$contratoAnexo->getCausas();

            foreach ($causas as $causa) {
                
                $entityManager->remove($causa);
                $entityManager->flush();
            }
        }

        $cuotas=$contratoAnexo->getCuotas();

        foreach ($cuotas as $cuota) {
            $entityManager->remove($cuota);
            $entityManager->flush();
        }

        $contratoAnexo->setEstado(false);
        $entityManager->persist($contratoAnexo);
        $entityManager->flush();

        //Activamos el ultimo anexo
        $ultAnexo=$contratoAnexoRepository->findOneBy(['contrato'=>$contrato,'isDesiste'=>false,'estado'=>null],['folio'=>'desc']);

        if($ultAnexo){
            $cuotas=$ultAnexo->getCuotas();

            foreach ($cuotas as $cuota) {

                $cuota->setAnular(null);
                $entityManager->persist($cuota);
                $entityManager->flush();
            }
        }else{
            $cuotas=$contrato->getDetalleCuotas();

            foreach ($cuotas as $cuota) {
                $cuota->setAnular(null);
                $entityManager->persist($cuota);
                $entityManager->flush();
            }
        }



        return $this->redirectToRoute('anexo_index',['id'=>$contratoAnexo->getContrato()->getId()]);
       
    }
     /**
     * @Route("/{id}/causas_anteriores", name="anexo_causas_anteriores", methods={"GET","POST"})
     */
    function listarCausasAnteriores(Contrato $contrato, CausaRepository $causaRepository): Response
    {

        return $this->render('anexo/_causasAnteriores.html.twig', [
            'causas_anteriores'=>$causaRepository->findBy(['agenda'=>$contrato->getAgenda(),'estado'=>true])
        ]);
    }
    /**
     * @Route("/{id}/causas_anteriores_extender", name="anexo_causas_anteriores_extender", methods={"GET","POST"})
     */
    function listarCausasAnterioresExtender(Contrato $contrato, CausaRepository $causaRepository): Response
    {

        return $this->render('anexo/_causasAnterioresExtender.html.twig', [
            'causas_anteriores'=>$causaRepository->findBy(['agenda'=>$contrato->getAgenda(),'estado'=>true])
        ]);
    }

    /**
     * @Route("/{id}/causas_anteriores_eliminar", name="anexo_causas_anteriores_eliminar", methods={"GET","POST"})
     */
    function eliminarCausasAnteriores(Causa $causa, CausaRepository $causaRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $causa->setEstado(false);

        $entityManager->persist($causa);
        $entityManager->flush();


        return $this->render('anexo/_causasAnteriores.html.twig', [
            'causas_anteriores'=>$causaRepository->findBy(['agenda'=>$causa->getAgenda(),'estado'=>true])
        ]);
    }


    /**
     * @Route("/{id}/pdf", name="anexo_pdf", methods={"GET","POST"})
     */
    public function pdf(ContratoAnexo $contratoAnexo)
    {
        $this->denyAccessUnlessGranted('view','anexo');
        $filename = sprintf('Anexo-'.$contratoAnexo->getId().'-%s.pdf',rand(0,9000));
       
        switch($contratoAnexo->getTipoAnexo()){
            case 1:
                $tipoAnexo="Agregar causa";
                break;
            case 2: 
                $tipoAnexo="Extensión del plazo";
                break;
            case 3:
                $tipoAnexo="Renegociación";
                break;
            default:
                $tipoAnexo="";
                break;
        }
        $html = $this->renderView('anexo/print.html.twig', array(
            'anexo' => $contratoAnexo,
            'Titulo'=>"Contrato",
            "tipoAnexo"=>$tipoAnexo,
            
        ));
    
        $entityManager = $this->getDoctrine()->getManager();
        $contratoAnexo->setPdf($filename);
        $entityManager->persist($contratoAnexo);
        $entityManager->flush();

       


        // Configure Dompdf según sus necesidades
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'helvetica');
    
        // Crea una instancia de Dompdf con nuestras opciones
        $dompdf = new Dompdf($pdfOptions);

        $dompdf->getOptions()->setChroot(array($this->getParameter('url_raiz')));
        
        // Cargar HTML en Dompdf
        $dompdf->loadHtml($html);
        
        // (Opcional) Configure el tamaño del papel y la orientación 'vertical' o 'vertical'
        $dompdf->setPaper('letter', 'portrait');

        // Renderiza el HTML como PDF
        $dompdf->render();

        $file=$dompdf->output();
        file_put_contents($this->getParameter('url_root'). $this->getParameter('pdf_contratos').$filename,$file);
        // Envíe el PDF generado al navegador (descarga forzada)
        /*$dompdf->stream($filename, [
            "Attachment" => true
        ]);*/

        if($contratoAnexo->getAceptaSuscripcion() and $contratoAnexo->getCancelaSuscripcion()==null){
            return $this->redirectToRoute('anexo_index',['id'=>$contratoAnexo->getContrato()->getId(),'inicio_suscripcion'=>true]);
        }else{
            return $this->redirectToRoute('anexo_index',['id'=>$contratoAnexo->getContrato()->getId()]);
        }
    }


    public function calularCuotas(ContratoAnexo $contratoAnexo){

        $entityManager = $this->getDoctrine()->getManager();

        //carga de Cuotas
        $countCuotas=$contratoAnexo->getNCuotas();
        $contrato=$contratoAnexo->getContrato();

        $fechaPrimerPago=$contratoAnexo->getFechaPrimerPago();
        $contrato->setFechaPrimerPago($contratoAnexo->getFechaPrimerPago());
        $contrato->setDiaPago($contratoAnexo->getDiasPago());
        $contrato->setCuotas($contratoAnexo->getNCuotas());
       
        $diaPago=$contratoAnexo->getDiasPago();
        $sumames=0;
        $numeroCuota=1;
        $isAbono=$contratoAnexo->getIsAbono();
        $isTotal=$contratoAnexo->getIsTotal();
        

        //Anulando cuotas anteriores
        $detalleCuotas=$contrato->getDetalleCuotas();
        foreach($detalleCuotas as $detalleCuota){
            $detalleCuota->setAnular(1);
            $entityManager->persist($detalleCuota);
            $entityManager->flush();
        }



        if($isAbono==true || $isTotal==true){
            $cuota=new Cuota();
            $cuota->setContrato($contrato);
            $cuota->setAnexo($contratoAnexo);
            $cuota->setNumero($numeroCuota);
            $cuota->setFechaPago($contratoAnexo->getFechaCreacion());
            $cuota->setMonto($contratoAnexo->getAbono());
            $entityManager->persist($cuota);
            $entityManager->flush();
            $numeroCuota++;

            $contrato->setProximoVencimiento($contrato->getFechaPrimeraCuota());
            $entityManager->persist($contrato);
            $entityManager->flush();
        }

        
        $primerPago=date("Y-m-".$diaPago,strtotime($fechaPrimerPago->format('Y-m-d')));
        if(date("n",strtotime($fechaPrimerPago->format('Y-m-d')))==2){
            if($diaPago==30)
                $primerPago=date("Y-m-28",strtotime($fechaPrimerPago->format('Y-m-d')));
        }

        $timePrimrePago=strtotime($primerPago);
        $timeFechaActual=strtotime(date("Y-m-d"));
        if($timeFechaActual>=$timePrimrePago){
            $sumames=1;
        }


        if($contratoAnexo->getValorCuota()!=0){
            for($i=0;$i<$countCuotas;$i++){
                $cuota=new Cuota();
        
                $i_aux=$i;
            
                $cuota->setContrato($contrato);
                $cuota->setAnexo($contratoAnexo);
                $cuota->setNumero($numeroCuota);

                $ts = mktime(0, 0, 0, date('m',$timePrimrePago) + $sumames+$i_aux, 1,date('Y',$timePrimrePago));
                
                $dia=$diaPago;
                if(date("n",$ts)==2){
                    if($diaPago==30){
                        $dia=date("d",mktime(0,0,0,date('m',$timePrimrePago)+ $sumames+$i_aux+1,1,date('Y',$timePrimrePago))-24);
                    }
                }
                $fechaCuota=date("Y-m-d", mktime(0,0,0,date('m',$timePrimrePago) + $sumames+$i_aux,$dia,date('Y',$timePrimrePago)));
                $cuota->setFechaPago(new \DateTime($fechaCuota));
                $cuota->setMonto($contratoAnexo->getValorCuota());
               if($numeroCuota==1 && $contrato->getIsAbono()==false && $contrato->getIsTotal()==false){
                    $contrato->setProximoVencimiento(new \DateTime($fechaCuota));
                    $entityManager->persist($contrato);
                    $entityManager->flush();
                }
                
                $entityManager->persist($cuota);
                $entityManager->flush();
                $numeroCuota++;
            }
        }




    }

    public function regenerarCuotasVirtualPos(Contrato $contrato,Configuracion $configuracion){
        $virtualPosLog = new VirtualPosLog();
        $entityManager = $this->getDoctrine()->getManager();
        $virtualPos =new VirtualPos($configuracion->getVirtualPosApiKey(),
                                    $configuracion->getVirtualPosSecretKey(),
                                    $configuracion->getVirtualPosPlan(),
                                    $configuracion->getVirtualPosUrl());

        // lo primero será anular los cargos que aun no se han cobrado.
        try{
            $response= $virtualPos->cancelarCargosFuturos($contrato->getSuscripcionId());
            $virtualPosLog->setExito(1);
            $virtualPosLog->setContrato($contrato);
            $virtualPosLog->setResponse(json_encode($response));
            $virtualPosLog->setRequest("");
            $virtualPosLog->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
            $entityManager->persist($virtualPosLog);
            $entityManager->flush();
        }catch(\Exception $e){
            $virtualPosLog->setExito(0);
            $virtualPosLog->setContrato($contrato);
            $virtualPosLog->setResponse($e->getMessage());
            $virtualPosLog->setRequest("");
            $virtualPosLog->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
            $entityManager->persist($virtualPosLog);
            $entityManager->flush();
        }

        //Seguno paso es crear las nuevas cuotas

        try{
            $detalleCuotas = $this->getDoctrine()->getRepository(Cuota::class)->findBy(["contrato"=>$contrato,"anular"=>null]);
            foreach ($detalleCuotas as $cuota) {
                $response = $virtualPos->crearCuota($cuota,$contrato->getSuscripcionId());
                $cuota->setInvoiceId($response["response"]["charge"]["id"]);
                $entityManager->persist($cuota);

                $virtualPosLogCuota = new VirtualPosLog();
                $virtualPosLogCuota->setExito(1);
                $virtualPosLogCuota->setContrato($contrato);
                $virtualPosLogCuota->setFechaRegistro(new \DateTime(date("Y-m-d")));
                $virtualPosLogCuota->setResponse(json_encode($response["response"]));
                $virtualPosLogCuota->setRequest($response["request"]);
                $entityManager->persist($virtualPosLogCuota);
                $entityManager->flush();
            }

        }catch(\Exception $e){
            $virtualPosLogCuota = new VirtualPosLog();
            $virtualPosLogCuota->setExito(0);
            $virtualPosLogCuota->setContrato($contrato);
            $virtualPosLogCuota->setFechaRegistro(new \DateTime(date("Y-m-d")));
            $virtualPosLogCuota->setResponse(json_encode($e->getMessage()));
            $virtualPosLogCuota->setRequest($response["request"]);
            $entityManager->persist($virtualPosLogCuota);
            $entityManager->flush();
        }



    }

    public function cancelarSuscripcion(Contrato $contrato,Configuracion $configuracion,Usuario $usuario)
    {
        $entityManager = $this->getDoctrine()->getManager();    
        if($contrato->getAceptaSuscripcion())
        {
            $suscripcionId=$contrato->getSuscripcionId();
            try{
                $virtualPosLog = new VirtualPosLog();
                $virtualPos =new VirtualPos($configuracion->getVirtualPosApiKey(),$configuracion->getVirtualPosSecretKey(),$configuracion->getVirtualPosPlan(),$configuracion->getVirtualPosUrl());
                if($suscripcionId!=""){
                    $response= $virtualPos->cancelarSuscripcion($suscripcionId);
                     $virtualPosLog->setExito(1);
                }else{
                    $response=["error"=>"no existe suscripcion activa"];
                     $virtualPosLog->setExito(0);
                }
               
                $virtualPosLog->setContrato($contrato);
                $virtualPosLog->setResponse(json_encode($response));
                $virtualPosLog->setRequest("");
                $virtualPosLog->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                $entityManager->persist($virtualPosLog);
                $entityManager->flush();

                $contrato->setCancelaSuscripcion(1);
                $contrato->setUsuarioCancelaSuscripcion($usuario);
                $contrato->setEstadoSuscripcion("CANCELADA");
                $contrato->setSesionSuscripcionActiva(0);
                $contrato->setAceptaSuscripcion(0);

                $contrato->setSuscripcionId("");
                $entityManager->persist($contrato);
                $entityManager->flush();

                $historicoSuscripcion = new ContratoHistoricoSuscripcion();
                $historicoSuscripcion->setContrato($contrato);
                $historicoSuscripcion->setExito(true);
                $historicoSuscripcion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                $historicoSuscripcion->setObservacion("Usuario cancela suscripción de pago automático y se crea nuevo anexo");
                $historicoSuscripcion->setSuscripcionId("");
                $entityManager->persist($historicoSuscripcion);
                $entityManager->flush();


            }catch(\Exception $e)
            {
                $virtualPosLog->setExito(0);
                $virtualPosLog->setContrato($contrato);
                $virtualPosLog->setResponse($e->getMessage());
                $virtualPosLog->setRequest("");
                $virtualPosLog->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                $entityManager->persist($virtualPosLog);
                $entityManager->flush();
            }
        }
    }

    public function crearSuscripcion(Contrato $contrato)
    {
        $entityManager = $this->getDoctrine()->getManager();    
        if($contrato->getAceptaSuscripcion()==null or ($contrato->getAceptaSuscripcion()==1 and $contrato->getCancelaSuscripcion()==1))
        {
            $historicoSuscripcion = new ContratoHistoricoSuscripcion();
            $historicoSuscripcion->setContrato($contrato);
            try{
        
                $contrato->setAceptaSuscripcion(1);
                $contrato->setCancelaSuscripcion(null);
                $contrato->setSesionSuscripcion(uniqid());
                $contrato->setEstadoSuscripcion(null);
                $contrato->setSesionSuscripcionActiva(1);
                $entityManager->persist($contrato);
                $entityManager->flush();

                
                $historicoSuscripcion->setExito(true);
                $historicoSuscripcion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                $historicoSuscripcion->setObservacion("Usuario acepta suscripción de pago automático y se crea nuevo anexo");
                $historicoSuscripcion->setSuscripcionId("");
                
            }catch(\Exception $e)
            {
                $historicoSuscripcion->setExito(false);
                $historicoSuscripcion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                $historicoSuscripcion->setObservacion("Ha ocurrido un error: ".$e->getMessage());
                $historicoSuscripcion->setSuscripcionId("");
            }
            $entityManager->persist($historicoSuscripcion);
            $entityManager->flush();
        }

    }
}
