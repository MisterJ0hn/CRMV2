<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\Usuario;
use App\Entity\AgendaStatus;
use App\Entity\AgendaObservacion;
use App\Form\AgendaType;
use App\Repository\AgendaRepository;
use App\Repository\ReunionRepository;
use App\Repository\UsuarioRepository;
use App\Repository\AgendaStatusRepository;
use App\Repository\CuentaRepository;
use App\Repository\ModuloRepository;
use App\Repository\ModuloPerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/reasignar")
 */
class ReasignarController extends AbstractController
{
    /**
     * @Route("/", name="reasignar_index")
     */
    public function index(AgendaRepository $agendaRepository,CuentaRepository $cuentaRepository,PaginatorInterface $paginator,ModuloPerRepository $moduloPerRepository,Request $request): Response
    {
        $this->denyAccessUnlessGranted('view','reasignar');
        

        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('reasignar',$user->getEmpresaActual());
        $filtro=null;
        $compania=null;
        $fecha=null;
        $tipo_fecha=0;
        $folio=null;

        if(null !== $request->query->get('bFolio') && $request->query->get('bFolio')!=''){
            $folio=$request->query->get('bFolio');
            $fecha=" (a.id = '$folio') ";

            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');
            

        }else{
            if(null !== $request->query->get('bFiltro') && $request->query->get('bFiltro')!=''){
                $filtro=$request->query->get('bFiltro');
            }
            if(null !== $request->query->get('bCompania') && $request->query->get('bCompania')!=0){
                $compania=$request->query->get('bCompania');
            }


            
            if(null !== $request->query->get('bFecha')){
                $aux_fecha=explode(" - ",$request->query->get('bFecha'));
                $dateInicio=$aux_fecha[0];
                $dateFin=$aux_fecha[1];
                
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
        }

        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 4:
            case 1:
                $query=$agendaRepository->findByPers(null,$user->getEmpresaActual(),$compania,'9,6',$filtro,3,$fecha);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
            break;
            case 5:
                $query=$agendaRepository->findByPers($user->getId(),null,$compania,'9,6',$filtro,3,$fecha);
                $companias=$cuentaRepository->findByPers($user->getId());
            break;
            default:
                $query=$agendaRepository->findByPers($user->getId(),null,$compania,'9,6',$filtro,3,$fecha);
                $companias=$cuentaRepository->findByPers($user->getId());
            break;
        }

        
        
        
        $agendas=$paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page',1), /*page number*/
        20 /*limit per page*/,
        array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));

        return $this->render('reasignar/index.html.twig', [
            'agendas' => $agendas,
            'pagina'=>$pagina->getNombre(),
            'bFiltro'=>$filtro,
            'bFolio'=>$folio,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'tipoFecha'=>$tipo_fecha,
            'TipoFiltro'=>'Reasignar'
        ]);
        
    }
    /**
     * @Route("/{id}", name="reasignar_show", methods={"GET","POST"})
     */
    public function show(Agenda $agenda,
            AgendaRepository $agendaRepository,
            AgendaStatusRepository $agendaStatusRepository,
            CuentaRepository $cuentaRepository,
            UsuarioRepository $usuarioRepository,
            ReunionRepository $reunionRepository,
            ModuloPerRepository $moduloPerRepository,
            Request $request): Response
        {
        $this->denyAccessUnlessGranted('view','reasignar');
        
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('reasignar',$user->getEmpresaActual());
        $form = $this->createForm(AgendaType::class, $agenda);

        $form->handleRequest($request);
        switch($user->getUsuarioTipo()->getId()){
            case 1:
                $cuentas=$cuentaRepository->findBy(['empresa'=>$user->getEmpresaActual()]);
            break;
            default:
                $cuentas=$cuentaRepository->findByPers($usuarioRepository->find($user->getId()));
        }
        if ($form->isSubmitted() && $form->isValid()) {
        
        $agenda->setStatus($agendaStatusRepository->find(1));
        $cuenta=$request->request->get('cboCuenta');
        $usuario=$request->request->get('cboAgendador');
        //$agenda->setCuenta($cuentaRepository->find($cuenta));
        $agenda->setAgendador($usuarioRepository->find($usuario));
        $entityManager = $this->getDoctrine()->getManager();


        $observacion=new AgendaObservacion();
        $observacion->setAgenda($agenda);
        $observacion->setUsuarioRegistro($usuarioRepository->find($user->getId()));
        $observacion->setStatus($agendaStatusRepository->find(1));
        $observacion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
        $observacion->setObservacion($request->request->get('txtObservacion'));

        $entityManager->persist($observacion);
        $entityManager->flush();
        $entityManager->persist($agenda);
        $entityManager->flush();
        return $this->redirectToRoute('reasignar_index');
        }
        return $this->render('reasignar/new.html.twig', [
        'agenda'=>$agenda,
        'form' => $form->createView(),
        'cuentas'=>$cuentas,
        'pagina'=>$pagina->getNombre(),
        
        ]);
    }
}
