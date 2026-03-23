<?php

namespace App\Controller;

use App\Entity\Contrato;
use App\Repository\CausaObservacionRepository;
use App\Repository\CuentaRepository;
use App\Repository\DiasPagoRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\VwContratoRepository;
use App\Repository\VwContratosVencidosRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        if(null !== $request->query->get('error_toast')){
            $error_toast=$request->query->get('error_toast');
        }
        $compania=null;
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
                   // $dateInicio=date('Y-m-d');
                    
                    $dateFin=date('Y-m-d');
                }
                $fecha="c.fechaCreacion between '$dateInicio' and '$dateFin 23:59:59' and a.status in (7,14)" ;
            }
        }
      
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 4:
            case 1:
            case 8:
            case 11:
                $query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;
            case 13:
            case 10:
                $companias=$cuentaRepository->findByPers($user->getId());
                $listCompanias='';

                $i=0;
                foreach($companias as $compania_loop){
                    
                    if($i>0){
                        $listCompanias.=',';
                    }
                    $listCompanias.=$compania_loop->getId();
                    $i++;
                }
                $fecha.=" and a.cuenta in ($listCompanias) ";
                $query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha);
                break;
            case 7:
                $carteras=[];
                foreach($user->getUsuarioCarteras() as $usuarioCartera){
                    $carteras[]=$usuarioCartera->getCartera()->getId();
                }
                if(count($carteras)>0){
                    $fecha.=" and co.cartera in (".implode(",",$carteras).") ";
                }else{
                    $fecha.=" and co.cartera is null ";
                }

                $query=$contratoRepository->findByPers(null,null,$compania,$filtro,null,$fecha);
                $companias=$cuentaRepository->findByPers($user->getId());
                break;
            case 12://Cobradores
                $lotes=[];
                foreach($user->getUsuarioLotes() as $usuarioLote){
                    $lotes[]=$usuarioLote->getLote()->getId();
                }
                if(count($lotes)>0){
                    $fecha.=" and co.idLote in (".implode(",",$lotes).") ";
                }else{
                    $fecha.=" and co.idLote is null ";
                }
                //$fecha.=" and c.idLote in (".implode(",",$lotes).") ";
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
        return $this->render('contratos_vencidos/index.html.twig', [
            'contratos' => $contratos,
            'bFiltro'=>$filtro,
            'bFolio'=>$folio,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'pagina'=>$pagina->getNombre(),
            'error'=>$error,
            'error_toast'=>$error_toast,
            'TipoFiltro'=>'Contrato'
        ]);
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
            'observaciones'=>$causaObservacionRepository->findBy(['contrato'=>$contrato],['fechaRegistro'=>'Desc'])
        ]);
    }
}
