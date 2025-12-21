<?php

namespace App\Controller;

use App\Entity\AgendaObservacion;
use App\Entity\InfAgendados;
use App\Repository\AgendaObservacionRepository;
use App\Repository\AgendaRepository;
use App\Repository\ReporteRepository;
use App\Repository\AgendaStatusRepository;
use App\Repository\CuentaRepository;
use App\Repository\UsuarioTipoRepository;
use App\Repository\ModuloPerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ContratoRepository;
use App\Repository\InfAgendadosRepository;
use App\Repository\UsuarioRepository;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @Route("/reporte")
 */

class ReporteController extends AbstractController
{
    /**
     * @Route("/", name="reporte_index")
     */
    public function index(): Response
    {
        return $this->render('reporte/index.html.twig', [
            'controller_name' => 'ReporteController',
        ]);
    }
    /**
     * @Route("/agendador", name="reporte_agendador")
     */
    public function agendador(AgendaRepository $agendaRepository,
                            CuentaRepository $cuentaRepository,
                            PaginatorInterface $paginator,
                            Request $request,
                            ModuloPerRepository $moduloPerRepository,
                            AgendaObservacionRepository $agendaObservacionRepository): Response
    {
        $this->denyAccessUnlessGranted('view','reporte_agendador');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('reporte_agendador',$user->getEmpresaActual());

        $filtro=null;
        $compania=null;
        $fecha=null;
        $statues='5';
        //$statuesgroup='1,2,3,4,5,6,7,8,9,10,11,12,13,14,15';
        $statuesgroup='1,2,3,4,5,6,7,8,9,10,11,12,13,14,15';
        $status=null;
        $tipo_fecha=0;
        if(null !== $request->query->get('bFiltro') && trim($request->query->get('bFiltro'))!=''){
            $filtro=$request->query->get('bFiltro');
        }
        if(null !== $request->query->get('bCompania')&&$request->query->get('bCompania')!=0){
            $compania=$request->query->get('bCompania');
        }

        if(null !== $request->query->get('bFecha')){
            $aux_fecha=explode(" - ",$request->query->get('bFecha'));
            $dateInicio=$aux_fecha[0];
            $dateFin=$aux_fecha[1];
            $statues=$statuesgroup;
        }else{
            
            $dateInicio=date('Y-m-d');
            $dateFin=date('Y-m-d');

        }
        if(null !== $request->query->get('bTipofecha') ){
            $tipo_fecha=$request->query->get('bTipofecha');
        }
        switch($tipo_fecha){
            case 0:
                $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            case 1:
                $fecha="a.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            case 2:
                $fecha="a.fechaContrato between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            default:
                $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
        }
  
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 1:
            case 4:
                $query=$agendaRepository->findByAgendReporte(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,0,$fecha);   
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
            break;
            default:
                $query=$agendaRepository->findByAgendReporte($user->getId(),$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,0,$fecha);   
                $companias=$cuentaRepository->findByPers($user->getId());
            break;
        }
        $datos=array();
        foreach($query as $total){
            $cantAgendado=0;
            $cantNoCalificaAB=0;
            $cantNoCalifica=0;
            $cantNoContesta=0;
            $cantSeguimiento=0;
            $cantContrato=0;
            $cantDesiste=0;
            $monto=0;
            $agenda=$total[0];
            //$valor=$agenda.valor;

            $agendados=$agendaRepository->findByAgendReporte($agenda->getAgendador()->getId(),$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,0,$fecha." and a.fechaAsignado is not null");
            foreach($agendados as $agendado){
                $cantAgendado=$agendado['valor'];
            }
           
            $noCalificanAB=$agendaRepository->findByAgendReporte($agenda->getAgendador()->getId(),$user->getEmpresaActual(),$compania,'9',$filtro,0,$fecha." and a.fechaAsignado is not null");
            foreach($noCalificanAB as $noCalificaAB){
                $cantNoCalificaAB=$noCalificaAB['valor'];
                //$monto=$contrata['monto'];
            }

            $noCalifican=$agendaRepository->findByAgendReporte($agenda->getAgendador()->getId(),$user->getEmpresaActual(),$compania,'9',$filtro,0,$fecha." and a.fechaAsignado is null");
            foreach($noCalifican as $noCalifica){
                $cantNoCalifica=$noCalifica['valor'];
                //$monto=$contrata['monto'];
            }

            $noContestan=$agendaRepository->findByAgendReporte($agenda->getAgendador()->getId(),$user->getEmpresaActual(),$compania,'10',$filtro,0,$fecha);
            foreach($noContestan as $noContesta){
                $cantNoContesta=$noContesta['valor'];
                //$monto=$contrata['monto'];
            }
            $seguimientos=$agendaRepository->findByAgendReporte($agenda->getAgendador()->getId(),$user->getEmpresaActual(),$compania,'2',$filtro,0,$fecha);
            foreach($seguimientos as $seguimiento){
                $cantSeguimiento=$seguimiento['valor'];
                //$monto=$contrata['monto'];
            }
            $contratos=$agendaRepository->findByAgendReporte($agenda->getAgendador()->getId(),$user->getEmpresaActual(),$compania,'7',$filtro,0,$fecha);
            foreach($contratos as $contrato){
                $cantContrato=$contrato['valor'];
                //$monto=$contrata['monto'];
            }
            $desistes=$agendaRepository->findByAgendReporte($agenda->getAgendador()->getId(),$user->getEmpresaActual(),$compania,'13,15',$filtro,0,$fecha);
            foreach($desistes as $desiste){
                $cantDesiste=$desiste['valor'];
                //$monto=$contrata['monto'];
            }
            $datos[]=array(
                
                "agendador_id"=>$agenda->getAgendador()->getId(),
                "agendador_nombre"=>$agenda->getAgendador()->getNombre(),
                "total"=>$total['valor'],
                "agendado"=>$cantAgendado,
                "nocalificaAB"=>$cantNoCalificaAB,
                "nocalifica"=>$cantNoCalifica,
                "nocontesta"=>$cantNoContesta,
                "seguimiento"=>$cantSeguimiento,
                "contrato"=>$cantContrato,
                "desiste"=>$cantDesiste,
            );

        }
        
        return $this->render('reporte/reporte_agendador.html.twig', [
            'controller_name' => 'ReporteController',
            'pagina'=>$pagina->getNombre(),
            'reportes'=>$datos,
            'bFiltro'=>$filtro,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'tipoFecha'=>$tipo_fecha,

        ]);
    }
     /**
     * @Route("/abogado", name="reporte_abogado", methods={"GET"})
     */
    public function abogado(AgendaRepository $agendaRepository,
                            CuentaRepository $cuentaRepository,
                            PaginatorInterface $paginator,
                            Request $request,
                            ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','reporte_abogado');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('reporte_abogado',$user->getEmpresaActual());

        $filtro=null;
        $compania=null;
        $fecha=null;
        $statues='5';
        $statuesgroup='4,5,7,6,8,12,13,14,15';
        $status=null;
        $tipo_fecha=1;
        if(null !== $request->query->get('bFiltro') && trim($request->query->get('bFiltro'))!=''){
            $filtro=$request->query->get('bFiltro');
        }
        if(null !== $request->query->get('bCompania')&&$request->query->get('bCompania')!=0){
            $compania=$request->query->get('bCompania');
        }

        if(null !== $request->query->get('bFecha')){
            $aux_fecha=explode(" - ",$request->query->get('bFecha'));
            $dateInicio=$aux_fecha[0];
            $dateFin=$aux_fecha[1];
            $statues=$statuesgroup;
        }else{
            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');

        }
        if(null !== $request->query->get('bTipofecha') ){
            $tipo_fecha=$request->query->get('bTipofecha');
        }
        switch($tipo_fecha){
            case 0:
                $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
                $fechaAsignado="a.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            case 1:
                $fecha="a.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
                $fechaAsignado="a.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            case 2:
                $fecha="a.fechaContrato between '$dateInicio' and '$dateFin 23:59:59'" ;
                $fechaAsignado="a.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            default:
                $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
        }
       // $fecha="a.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
        
        
        //$queryresumen=$agendaRepository->findByAgendGroup(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,null,$fecha);   
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 1:
            case 4:
                $query=$agendaRepository->findByAgendReporte(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,1,$fecha);   
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
            break;
            default:
                $query=$agendaRepository->findByAgendReporte($user->getId(),$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,1,$fecha);   
                $companias=$cuentaRepository->findByPers($user->getId());
            break;
        }
        $datos=array();
        foreach($query as $total){
            $cantAgendado=0;
            $cantNoCalifica=0;
            $cantNoContrata=0;
            $cantNoResponde=0;
            $cantContrata=0;
            $cantRatificaTermino=0;
            $cantDesconoceoDesiste=0;
            $monto=0;
            $agenda=$total[0];
            //$valor=$agenda.valor;

            //$agendados=$agendaRepository->findByAgendReporte($agenda->getAbogado()->getId(),$user->getEmpresaActual(),$compania,'5,15,13,8,7',$filtro,1,$fecha);
            $agendados=$agendaRepository->findByAgendReporte($agenda->getAbogado()->getId(),$user->getEmpresaActual(),$compania,'5',$filtro,1,$fecha);
            foreach($agendados as $agendado){
                $cantAgendado=$agendado['valor'];
            }
            $nocalifican=$agendaRepository->findByAgendReporte($agenda->getAbogado()->getId(),$user->getEmpresaActual(),$compania,'6',$filtro,1,$fecha);
            foreach($nocalifican as $nocalifica){
                $cantNoCalifica=$nocalifica['valor'];
            }
            $nocontratan=$agendaRepository->findByAgendReporte($agenda->getAbogado()->getId(),$user->getEmpresaActual(),$compania,'8',$filtro,1,$fecha);
            foreach($nocontratan as $nocontrata){
                $cantNoContrata=$nocontrata['valor'];
            }
            $contratan=$agendaRepository->findByAgendReporte($agenda->getAbogado()->getId(),$user->getEmpresaActual(),$compania,'7,14',$filtro,1,$fecha);
            foreach($contratan as $contrata){
                $cantContrata=$contrata['valor'];
                $monto=$contrata['monto'];
            }
            $ratificantermino=$agendaRepository->findByAgendReporte($agenda->getAbogado()->getId(),$user->getEmpresaActual(),$compania,'15',$filtro,1,$fecha);
            foreach($ratificantermino as $ratificatermino){
                $cantRatificaTermino=$ratificatermino['valor'];
            }
            $noresponden=$agendaRepository->findByAgendReporte($agenda->getAbogado()->getId(),$user->getEmpresaActual(),$compania,'4',$filtro,1,$fecha);
            foreach($noresponden as $noresponde){
                $cantNoResponde=$noresponde['valor'];
            }
            $desconoceodesisten=$agendaRepository->findByAgendReporte($agenda->getAbogado()->getId(),$user->getEmpresaActual(),$compania,'13',$filtro,1,$fecha);
            foreach($desconoceodesisten as $desconoceodesiste){
                $cantDesconoceoDesiste=$desconoceodesiste['valor'];
            }

          
            $datos[]=array(
                
                "abogado_id"=>$agenda->getAbogado()->getId(),
                "abogado_nombre"=>$agenda->getAbogado()->getNombre(),
                "total"=>$total['valor'],
                "agendado"=>$cantAgendado,
                "nocalifica"=>$cantNoCalifica,
                "nocontrata"=>$cantNoContrata,
                "contrata"=>$cantContrata,
                "ratificatermino"=>$cantRatificaTermino,
                "noresponde"=>$cantNoResponde,
                'desconoceodesiste'=>$cantDesconoceoDesiste,
                'monto'=>$monto
            );

        }
        
        return $this->render('reporte/reporte_abogado.html.twig', [
            'controller_name' => 'ReporteController',
            'pagina'=>$pagina->getNombre(),
            'reportes'=>$datos,
            'bFiltro'=>$filtro,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'tipoFecha'=>$tipo_fecha,

        ]);
    }

    /**
     * @Route("/cobrador", name="reporte_cobrador", methods={"GET"})
     */
    public function cobrador(): Response
    {
        $this->denyAccessUnlessGranted('view','reporte_cobrador');
        return $this->render('reporte/reporte_cobrador.html.twig', [
            'controller_name' => 'ReporteController',
        ]);
    }

    /**
     * @Route("/campania", name="reporte_campania", methods={"GET"})
     */
    public function campania(AgendaRepository $agendaRepository,
                            CuentaRepository $cuentaRepository,
                            PaginatorInterface $paginator,
                            Request $request,
                            ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','reporte_campania');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('reporte_campania',$user->getEmpresaActual());

        $filtro=null;
        $compania=null;
        $fecha=null;
        $statues='5';
        $statuesgroup='1,2,3,4,5,6,7,8,9,10,11,12,13,14,15';
        $status=null;
        $tipo_fecha=0;
        if(null !== $request->query->get('bFiltro') && trim($request->query->get('bFiltro'))!=''){
            $filtro=$request->query->get('bFiltro');
        }
        if(null !== $request->query->get('bCompania')&&$request->query->get('bCompania')!=0){
            $compania=$request->query->get('bCompania');
        }

        if(null !== $request->query->get('bFecha')){
            $aux_fecha=explode(" - ",$request->query->get('bFecha'));
            $dateInicio=$aux_fecha[0];
            $dateFin=$aux_fecha[1];
            $statues=$statuesgroup;
        }else{
            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');

        }
        if(null !== $request->query->get('bTipofecha') ){
            $tipo_fecha=$request->query->get('bTipofecha');
        }
        switch($tipo_fecha){
            case 0:
                $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            case 1:
                $fecha="a.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            case 2:
                $fecha="a.fechaContrato between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            default:
                $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
        }
       // $fecha="a.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
        
        
        //$queryresumen=$agendaRepository->findByAgendGroup(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,null,$fecha);   
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 1:
            case 4:
                $query=$agendaRepository->findByCampaniaReporte(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,0,$fecha);   
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
            break;
            default:
                $query=$agendaRepository->findByCampaniaReporte($user->getId(),$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,0,$fecha);   
                $companias=$cuentaRepository->findByPers($user->getId());
            break;
        }
        $datos=array();
        foreach($query as $total){
            $cantAgendado=0;
            $cantContrata=0;
            $monto=0;
            $agenda=$total[0];
            //$valor=$agenda.valor;
            
            $agendados=$agendaRepository->findByCampaniaReporte(null,$user->getEmpresaActual(),$compania,5,null,0,$fecha,$agenda->getCampania());
            foreach($agendados as $agendado){
                $cantAgendado=$agendado['valor'];
            }
           
            $contratan=$agendaRepository->findByCampaniaReporte(null,$user->getEmpresaActual(),$compania,'7',null,0,$fecha,$agenda->getCampania());
            foreach($contratan as $contrata){
                $cantContrata=$contrata['valor'];
                $monto=$contrata['monto'];
            }

          
            $datos[]=array(
                
               
                "campania_nombre"=>$agenda->getCampania(),
                "total"=>$total['valor'],
                "agendado"=>$cantAgendado,
                "contrata"=>$cantContrata,
                'monto'=>$monto
            );

        }
        
        return $this->render('reporte/reporte_campania.html.twig', [
            'controller_name' => 'Reporte ',
            'pagina'=>$pagina->getNombre(),
            'reportes'=>$datos,
            'bFiltro'=>$filtro,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'tipoFecha'=>$tipo_fecha,

        ]);
    }

    /**
     * @Route("/contratos", name="reporte_contratos", methods={"GET"})
     */
    public function contratos(AgendaRepository $agendaRepository,
                            CuentaRepository $cuentaRepository,
                            PaginatorInterface $paginator,
                            Request $request,
                            ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','reporte_contratos');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('reporte_contratos',$user->getEmpresaActual());

        $filtro=null;
        $compania=null;
        $fecha=null;
        $statues='5';
        $statuesgroup='7,14,13,12,15';
        $status=null;
        $tipo_fecha=2;
        if(null !== $request->query->get('bFiltro') && trim($request->query->get('bFiltro'))!=''){
            $filtro=$request->query->get('bFiltro');
        }
        if(null !== $request->query->get('bCompania')&&$request->query->get('bCompania')!=0){
            $compania=$request->query->get('bCompania');
        }

        if(null !== $request->query->get('bFecha')){
            $aux_fecha=explode(" - ",$request->query->get('bFecha'));
            $dateInicio=$aux_fecha[0];
            $dateFin=$aux_fecha[1];
            $statues=$statuesgroup;
        }else{
            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');

        }
        if(null !== $request->query->get('bTipofecha') ){
            $tipo_fecha=$request->query->get('bTipofecha');
        }
        switch($tipo_fecha){
            case 0:
                $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            case 1:
                $fecha="a.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            case 2:
                $fecha="a.fechaContrato between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            default:
                $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
        }
       // $fecha="a.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
        
        
        //$queryresumen=$agendaRepository->findByAgendGroup(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,null,$fecha);   
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 1:
            case 4:
                $query=$agendaRepository->findByContratoReporte(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,1,$fecha);   
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
            break;
            default:
                $query=$agendaRepository->findByContratoReporte($user->getId(),$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,1,$fecha);   
                $companias=$cuentaRepository->findByPers($user->getId());
            break;
        }
        $datos=array();
        foreach($query as $total){
            
            $cantDesiste=0;
            $cantContrataRRSS=0;
            $cantContrataManual=0;
            $cantRatificaTermino=0;
            $monto=0;
            $montoDesiste=0;
            $agenda=$total[0];
            //$valor=$agenda.valor;

           
            $contratanRRSS=$agendaRepository->findByContratoReporte(null,$user->getEmpresaActual(),$agenda->getCuenta()->getId(),'7,14',$filtro,1,$fecha." and a.lead is not null ");
            foreach($contratanRRSS as $contrata){
                $cantContrataRRSS=$contrata['valor'];
                $monto+=$contrata['monto'];
            }
            $contratanManual=$agendaRepository->findByContratoReporte(null,$user->getEmpresaActual(),$agenda->getCuenta()->getId(),'7,14',$filtro,1,$fecha." and a.lead is null ");
            foreach($contratanManual as $contrata){
                $cantContrataManual=$contrata['valor'];
                $monto+=$contrata['monto'];
            }
           /* $ratificantermino=$agendaRepository->findByContratoReporte(null,$user->getEmpresaActual(),$agenda->getCuenta()->getId(),'15',$filtro,1,$fecha);
            foreach($ratificantermino as $ratificatermino){
                $cantRatificaTermino=$ratificatermino['valor'];
                
            }*/
            $desisten=$agendaRepository->findByContratoReporte(null,$user->getEmpresaActual(),$agenda->getCuenta()->getId(),'12,13,15',$filtro,1,$fecha);
            foreach($desisten as $desiste){
                $cantDesiste=$desiste['valor'];
                $montoDesiste+=$desiste['monto'];
            }

          
            $datos[]=array(
                
                
                "cuenta"=>$agenda->getCuenta()->getNombre(),
                "contrataRRSS"=>$cantContrataRRSS,
                "contrataManual"=>$cantContrataManual,
                "ratificatermino"=>$cantRatificaTermino,
                "desiste"=>$cantDesiste,
                "montoDesiste"=>$montoDesiste,
                'total'=>$monto
            );

        }
        
        return $this->render('reporte/reporte_contrato.html.twig', [
            'controller_name' => 'ReporteController',
            'pagina'=>$pagina->getNombre(),
            'reportes'=>$datos,
            'bFiltro'=>$filtro,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'tipoFecha'=>$tipo_fecha,

        ]);
    }

    /**
     * @Route("/desiste", name="reporte_desiste", methods={"GET"})
     */
    public function desiste(AgendaRepository $agendaRepository,
                            CuentaRepository $cuentaRepository,
                            PaginatorInterface $paginator,
                            Request $request,
                            ModuloPerRepository $moduloPerRepository,
                            AgendaStatusRepository $agendaStatusRepository): Response
    {
        $this->denyAccessUnlessGranted('view','reporte_desiste');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('reporte_desiste',$user->getEmpresaActual());
        $filtro=null;
        $compania=null;
        $fecha=null;
        $statuesgroup='14,13,12,15';
        $status=null;
        $tipo_fecha=2;
        $folio='';
        $otros='';
        $statues=$agendaStatusRepository->findBy(['id'=>[12,13,14,15]]);
        
        if(null !== $request->query->get('bFolio') && $request->query->get('bFolio')!=''){
            $folio=$request->query->get('bFolio');
            $otros=" con.folio= $folio";

            $aux_fecha=explode(" - ",$request->query->get('bFecha'));
            $dateInicio=$aux_fecha[0];
            $dateFin=$aux_fecha[1];
  
            
        }else{
            if(null !== $request->query->get('bFiltro') && trim($request->query->get('bFiltro'))!=''){
                $filtro=$request->query->get('bFiltro');
            }
            if(null !== $request->query->get('bCompania')&&$request->query->get('bCompania')!=0){
                $compania=$request->query->get('bCompania');
            }
            if(null !== $request->query->get('bStatus')&&$request->query->get('bStatus')!=0){
                $status=$request->query->get('bStatus');
                
            }

            if(null !== $request->query->get('bFecha')){
                $aux_fecha=explode(" - ",$request->query->get('bFecha'));
                $dateInicio=$aux_fecha[0];
                $dateFin=$aux_fecha[1];
                $statues=$statuesgroup;
            }else{
                $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
                $dateFin=date('Y-m-d');

            }
            if(null !== $request->query->get('bTipofecha') ){
                $tipo_fecha=$request->query->get('bTipofecha');
            }
            switch($tipo_fecha){
                case 0:
                    $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
                    break;
                case 1:
                    $fecha="con.fechaTermino between '$dateInicio' and '$dateFin 23:59:59'" ;
                    break;
                case 2:
                    $fecha="a.fechaContrato between '$dateInicio' and '$dateFin 23:59:59'" ;
                    break;
                default:
                    $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
                    break;
            }
        }
        
       // $fecha="a.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
        $fecha.=$otros;
        $queryResumen=array();
        //$queryresumen=$agendaRepository->findByAgendGroup(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,null,$fecha);   
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 1:
            case 4:
                $query=$agendaRepository->findByContrato2Reporte(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,1,$fecha); 
                $queryResumen=$agendaRepository->groupByDesisteConfirmaDesiste(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,1,$fecha); 
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
            break;
            default:
                $query=$agendaRepository->findByContrato2Reporte($user->getId(),$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,1,$fecha);   
                $companias=$cuentaRepository->findByPers($user->getId());
                $queryResumen=$agendaRepository->groupByDesisteConfirmaDesiste($user->getId(),$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,1,$fecha);   
            break;
        }
        $datos=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/,
            array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));
        
        
        return $this->render('reporte/reporte_desiste.html.twig', [
            'controller_name' => 'ReporteController',
            'pagina'=>$pagina->getNombre(),
            'bStatus'=>$status,
            'statues'=>$statues,
            'reportes'=>$datos,
            'bFiltro'=>$filtro,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'tipoFecha'=>$tipo_fecha,
            'bFolio'=>$folio,
            'resumenes'=>$queryResumen,

        ]);
    }

    /**
     * @Route("/contacto", name="reporte_contacto", methods={"GET"})
     */
    public function contacto(AgendaRepository $agendaRepository,
                            CuentaRepository $cuentaRepository,
                            PaginatorInterface $paginator,
                            Request $request,
                            ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','reporte_contacto');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('reporte_contacto',$user->getEmpresaActual());

        $filtro=null;
        $compania=null;
        $fecha=null;
        $statues='5';
        $statuesgroup='1,2,3,4,5,6,7,8,9,10,11,12,13,14,15';
        $status=null;
        $tipo_fecha=0;
        if(null !== $request->query->get('bFiltro') && trim($request->query->get('bFiltro'))!=''){
            $filtro=$request->query->get('bFiltro');
        }
        if(null !== $request->query->get('bCompania')&&$request->query->get('bCompania')!=0){
            $compania=$request->query->get('bCompania');
        }

        if(null !== $request->query->get('bFecha')){
            $aux_fecha=explode(" - ",$request->query->get('bFecha'));
            $dateInicio=$aux_fecha[0];
            $dateFin=$aux_fecha[1];
            $statues=$statuesgroup;
        }else{
           // $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
           $dateInicio=date('Y-m-d'); 
           $dateFin=date('Y-m-d');

        }
        if(null !== $request->query->get('bTipofecha') ){
            $tipo_fecha=$request->query->get('bTipofecha');
        }
        switch($tipo_fecha){
            case 0:
                $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            case 1:
                $fecha="a.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            case 2:
                $fecha="a.fechaContrato between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            default:
                $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
        }
       // $fecha="a.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
        
        
        //$queryresumen=$agendaRepository->findByAgendGroup(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,null,$fecha);   
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 1:
            case 4:
                $query=$agendaRepository->findByContactoReporte(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,0,$fecha);   
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
            break;
            default:
                $query=$agendaRepository->findByContactoReporte($user->getId(),$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,0,$fecha);   
                $companias=$cuentaRepository->findByPers($user->getId());
            break;
        }
        $datos=array();
        foreach($query as $total){
            $cantAgendado=0;
            $cantContrata=0;
            $monto=0;
            $agenda=$total[0];

  
            //$valor=$agenda.valor;
            
            $agendados=$agendaRepository->findByContactoReporte(null,$user->getEmpresaActual(),$compania,5,null,0,$fecha,$agenda->getAgendaContacto());
            foreach($agendados as $agendado){
                $cantAgendado=$agendado['valor'];
            }
           
            $contratan=$agendaRepository->findByContactoReporte(null,$user->getEmpresaActual(),$compania,'7',null,0,$fecha,$agenda->getAgendaContacto());
            foreach($contratan as $contrata){
                $cantContrata=$contrata['valor'];
                $monto=$contrata['monto'];
            }

          
            $datos[]=array(
                
               
                "campania_nombre"=>$agenda->getAgendaContacto(),
                "total"=>$total['valor'],
                "agendado"=>$cantAgendado,
                "contrata"=>$cantContrata,
                'monto'=>$monto
            );

        }
        
        return $this->render('reporte/reporte_contacto.html.twig', [
            'controller_name' => 'Reporte ',
            'pagina'=>$pagina->getNombre(),
            'reportes'=>$datos,
            'bFiltro'=>$filtro,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'tipoFecha'=>$tipo_fecha,

        ]);
    }

    /**
     * @Route("/gestiones", name="reporte_gestiones")
     */
    public function gestiones(ReporteRepository $reporteRepository,
                            UsuarioTipoRepository $usuarioTipoRepository,
                            AgendaRepository $agendaRepository,
                            PaginatorInterface $paginator,
                            Request $request,
                            ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','reporte_gestiones');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('reporte_gestiones',$user->getEmpresaActual());

        $perfil=null;
        $empleado=null;
        $fecha=null;
        $statues='5';
        $statuesgroup='1,2,3,4,5,6,7,8,9,10,11,12,13,14,15';
        $status=null;
        $tipo_fecha=0;
        if(null !== $request->query->get('bPerfil') && $request->query->get('bPerfil')!=0){
            $perfil=$request->query->get('bPerfil');
        }
        if(null !== $request->query->get('bEmpleado') && $request->query->get('bEmpleado')!=0){
            $empleado=$request->query->get('bEmpleado');
        }
        if(null !== $request->query->get('bTipofecha') ){
            $tipo_fecha=$request->query->get('bTipofecha');
        }

        if(null !== $request->query->get('bFecha')){
            $aux_fecha=explode(" - ",$request->query->get('bFecha'));
            $dateInicio=$aux_fecha[0];
            $dateFin=$aux_fecha[1];
            $statues=$statuesgroup;
        }else{
            $dateInicio=date('Y-m-d');
            $dateFin=date('Y-m-d');
        }

        switch($tipo_fecha){
            case 0:
                $fecha="a.fechaRegistro between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
        }
        
        
        //$queryresumen=$agendaRepository->findByAgendGroup(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,null,$fecha);   
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 1:
                $query=$reporteRepository->findByGestionReporte($empleado,$user->getEmpresaActual(),$perfil,$statuesgroup,null,0,$fecha);   
                $totalAgenda=$reporteRepository->findByGestionReporteCountAgendas($empleado,$user->getEmpresaActual(),$perfil,$statuesgroup,null,0,$fecha); 
                $totalGestiones=$reporteRepository->findByGestionReporteCountGestiones($empleado,$user->getEmpresaActual(),$perfil,$statuesgroup,null,0,$fecha); 
                $perfiles=$usuarioTipoRepository->findBy(['id'=>[5,6]]);
            break;
            default:
                $query=$reporteRepository->findByGestionReporte($user->getId(),$user->getEmpresaActual(),$perfil,$statuesgroup,null,0,$fecha);
                $totalAgenda=$reporteRepository->findByGestionReporteCountAgendas($empleado,$user->getEmpresaActual(),$perfil,$statuesgroup,null,0,$fecha); 
                $totalGestiones=$reporteRepository->findByGestionReporteCountGestiones($empleado,$user->getEmpresaActual(),$perfil,$statuesgroup,null,0,$fecha); 
                $perfiles=$usuarioTipoRepository->findBy(['id'=>[5,6]]);
            break;
        }

        $datos=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/,
            array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));
        
        
        
        return $this->render('reporte/reporte_gestiones.html.twig', [
            'controller_name' => 'ReporteController',
            'pagina'=>$pagina->getNombre(),
            'reportes'=>$datos,
            'totalAgenda'=>$totalAgenda,
            'totalGestiones'=>$totalGestiones,
            'bEmpleado'=>$empleado,
            'perfiles'=>$perfiles,
            'bPerfil'=>$perfil,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'tipoFecha'=>$tipo_fecha,

        ]);
    }

    /**
     * @Route("/cbo_usuarios", name="reporte_cbo_usuarios", methods={"POST"})
     */
    public function cboUsuario(Request $request, UsuarioRepository $usuarioRepository)
    {
        
        $user=$this->getUser();
        $id_usuario=null;
        if(null !== $request->request->get('id_usuario') && $request->request->get('id_usuario')!=0){
            $id_usuario=$request->request->get('id_usuario');
        }
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 1:
                if(null !== $request->request->get('id_perfil') && $request->request->get('id_perfil')!=0){
                    $usuarios=$usuarioRepository->findBy(['usuarioTipo'=>$request->request->get('id_perfil'),'estado'=>1]);
        
                }else{
                    $usuarios=$usuarioRepository->findBy(['usuarioTipo'=>[5,6],'estado'=>1]);
                }
        
            break;
            default:
                
                    $usuarios=$usuarioRepository->findBy(['id'=>$user->getId(),'estado'=>1]);
        
               
            break;
        }

        
        return $this->render('reporte/cbo_usuario.html.twig', [
            
            'usuarios'=>$usuarios,
            'id_usuario'=>$id_usuario
        ]);

    }
    /**
     * @Route("/agendados", name="reporte_agendados")
     */
    public function agendados(ReporteRepository $reporteRepository,
                            UsuarioTipoRepository $usuarioTipoRepository,
                            AgendaRepository $agendaRepository,
                            AgendaObservacionRepository $agendaObservacionRepository,
                            PaginatorInterface $paginator,
                            Request $request,
                            ModuloPerRepository $moduloPerRepository,
                            InfAgendadosRepository $infAgendadosRepository): Response
    {
        $this->denyAccessUnlessGranted('view','reporte_agendados');
        $user=$this->getUser();
        $qdias=0;
        if(null !== $request->query->get('bFecha')){
            $aux_fecha=explode(" - ",$request->query->get('bFecha'));
            $dateInicio=$aux_fecha[0];
            $dateFin=$aux_fecha[1];
            
        }else{
            $dateInicio=date('Y-m-d');
            $dateFin=date('Y-m-d');
        }

        $qdias=(strtotime($dateFin)-strtotime($dateInicio))/60/60/24;

        $criterioAvanzado=[];
        array_push($criterioAvanzado,['a.fechaRegistro','<=',"'$dateFin 23:59:59'"]);
        array_push($criterioAvanzado,['a.fechaRegistro','>=',"'$dateInicio'"]);
        array_push($criterioAvanzado,['a.abogadoDestino',' is not ','null']);

        $agendados=$agendaObservacionRepository->findByAgendados([
                                                            'status'=>5
                                                        ],
                                                        ['abogadoDestino'],
                                                        null,
                                                        null,
                                                        null,
                                                        $criterioAvanzado
                                                        );

        $prospectos=$agendaObservacionRepository->findByAgendados([
                                                            'status'=>5
                                                        ],
                                                        ['agenda','abogadoDestino'],
                                                        null,
                                                        null,
                                                        null,
                                                        $criterioAvanzado
                                                        ); 
        
                                                        
        $totalAgendados=$agendaObservacionRepository->findByAgendados([
                                                            'status'=>5
                                                        ],
                                                        [],
                                                        null,
                                                        null,
                                                        null,
                                                        $criterioAvanzado
                                                        );

        $totalAgenda=$agendaObservacionRepository->findByAgendados([
            'status'=>5
        ],
        ['agenda'],
        null,
        null,
        null,
        $criterioAvanzado
        ); 

        $entityManager = $this->getDoctrine()->getManager();
        
        $infAgendadosRepository->removeBySesion($user->getId());

        foreach ($prospectos as $prospecto) {
           
            $informe=new InfAgendados();
            
            $informe->setUsuario($user);
            $informe->setAbogado($prospecto[0]->getAbogadoDestino());
            $informe->setAgendados(0);
            $informe->setProspectos(1);

            $entityManager->persist($informe);
            $entityManager->flush();
        }

        foreach ($agendados as $agendado) {
            
            $informe=new InfAgendados();
            
            $informe->setUsuario($user);
            $informe->setAbogado($agendado[0]->getAbogadoDestino());
            $informe->setAgendados($agendado['agendados']);
            $informe->setProspectos(0);
            $entityManager->persist($informe);
            $entityManager->flush();
        }

        $informes=$infAgendadosRepository->findByGroupPersonalizado(['usuario'=>$user->getId()]);

        return $this->render('reporte/reporte_agendados.html.twig', [
            'agendados'=>$agendados,
            'total_agendado'=>$totalAgendados,
            'total_agenda'=>$totalAgenda,
            'informeAgendados'=>$informes,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'qdias'=>$qdias
            
        ]);
    }

}

