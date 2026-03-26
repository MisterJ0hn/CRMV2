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
use App\Repository\VwContratoConsultorRepository;
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
    public function index(VwContratoConsultorRepository $contratoRepository,
                        PaginatorInterface $paginator,ModuloPerRepository $moduloPerRepository,
                        Request $request,CuentaRepository $cuentaRepository): Response
    {
        $this->denyAccessUnlessGranted('view','contrato_consultor');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('contrato_consultor',$user->getEmpresaActual());
        $filtro=null;
        $error='';
        $error_toast="";
        $otros="";
        $folio="";
        $tipoCliente="prime";
        
        if(null !== $request->query->get('error_toast')){
            $error_toast=$request->query->get('error_toast');
        }
        $compania=null;

        if(null !== $request->query->get('bTipoCliente') && $request->query->get('bTipoCliente')!=''){
            $tipoCliente=$request->query->get('bTipoCliente') ;
        }
        if(null !== $request->query->get('bFolio') && $request->query->get('bFolio')!=''){
            $folio=$request->query->get('bFolio');
            $otros=" (co.folio= '$folio' or co.agenda = '$folio') ";

            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30*24);
            $dateFin=date('Y-m-d');
            $fecha=$otros. " and a.status in (7,14)";

        }else{
            if(null !== $request->query->get('bFiltro') && $request->query->get('bFiltro')!=''){
                $filtro=$request->query->get('bFiltro');
                $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30*24);
                $dateFin=date('Y-m-d');
                $fecha=$otros. " a.status in (7,14)";
            }else{
                if(null !== $request->query->get('bCompania') && $request->query->get('bCompania')!=0){
                    $compania=$request->query->get('bCompania');
                }
                if(null !== $request->query->get('bFecha')){
                    $aux_fecha=explode(" - ",$request->query->get('bFecha'));
                    $dateInicio=$aux_fecha[0];
                    $dateFin=$aux_fecha[1];
                }else{
                    $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30*24);
                    //$dateInicio=date('Y-m-d');
                    
                    $dateFin=date('Y-m-d');
                }
                $fecha="co.fechaCreacion between '$dateInicio' and '$dateFin 23:59:59' and a.status in (7,14)" ;
            }
        }

        if($tipoCliente=="prime"){
            $fecha.=" and c.prime=1";
        }else{
            $fecha.=" and c.preferente=1";
        }
        switch($user->getUsuarioTipo()->getId()){
            case 3:
           
            case 1:
            case 8:
                $query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;
            case 4:
                $grupos = $user->getusuarioGrupos();

                

        
                $listGrupos='';

                $i=0;
                foreach ($grupos as $grupo) {
                    
                    if($i>0){
                        $listGrupos.=',';
                    }
                    $listGrupos.=$grupo->getGrupo()->getId();
                    $i++;
                }
                $fecha.=" and co.grupo in ($listGrupos) ";
                $query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;
            default:
                $query=$contratoRepository->findByPers($user->getId(),null,$compania,$filtro,null,$fecha);
                $companias=$cuentaRepository->findByPers($user->getId());
                
            break;
        }
        //$companias=$cuentaRepository->findByPers($user->getId());
        //$query=$contratoRepository->findAll();
        $contratos=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/,
            array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));
        return $this->render('contrato_consultor/index.html.twig', [
            'contratos' => $contratos,
            'bFiltro'=>$filtro,
            'bFolio'=>$folio,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'pagina'=>"Contrato Consultor",
            'error'=>$error,
            'error_toast'=>$error_toast,
            'TipoFiltro'=>'Contrato',
            'bTipoCliente'=>$tipoCliente
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
