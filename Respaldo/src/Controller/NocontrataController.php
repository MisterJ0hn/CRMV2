<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\Usuario;
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
 * @Route("/nocontrata")
 */
class NocontrataController extends AbstractController
{
    /**
     * @Route("/", name="nocontrata_index")
     */
    public function index(AgendaRepository $agendaRepository,CuentaRepository $cuentaRepository,PaginatorInterface $paginator,ModuloPerRepository $moduloPerRepository,Request $request): Response
    {
        $this->denyAccessUnlessGranted('view','nocontrata');

        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('nocontrata',$user->getEmpresaActual());
        $filtro=null;
        $error='';
        $error_toast="";
        $otros="";
        $folio="";
        $compania=null;
        if(null !== $request->query->get('error_toast')){
            $error_toast=$request->query->get('error_toast');
        }
        
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
        $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
     


        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 4:
            case 1:
                $query=$agendaRepository->findByPers(null,$user->getEmpresaActual(),$compania,'8',$filtro,3,$fecha);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
            break;
            case 5:
                $query=$agendaRepository->findByPers($user->getId(),null,$compania,'8',$filtro,3,$fecha);
                $companias=$cuentaRepository->findByPers($user->getId());
            break;
            default:
                $query=$agendaRepository->findByPers($user->getId(),null,$compania,'8',$filtro,3,$fecha);
                $companias=$cuentaRepository->findByPers($user->getId());
            break;
        }

        
        
        
        $agendas=$paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /*page number*/
        20 /*limit per page*/,
        array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));

        return $this->render('nocontrata/index.html.twig', [
            'agendas' => $agendas,
            'pagina'=>$pagina->getNombre(),
            'bFiltro'=>$filtro,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'error'=>$error,
            'error_toast'=>$error_toast,
            'TipoFiltro'=>'NoContrata',
        ]);
        
    }
    /**
     * @Route("/{id}", name="nocontrata_show", methods={"GET","POST"})
     */
    public function show(Agenda $agenda,
            AgendaRepository $agendaRepository,
            AgendaStatusRepository $agendaStatusRepository,
            CuentaRepository $cuentaRepository,
            UsuarioRepository $usuarioRepository,
            ReunionRepository $reunionRepository
            ,ModuloPerRepository $moduloPerRepository,
            Request $request): Response
        {
        $this->denyAccessUnlessGranted('view','nocontrata');

        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('nocontrata',$user->getEmpresaActual());
        $form = $this->createForm(AgendaType::class, $agenda);
        switch($user->getUsuarioTipo()->getId()){
            case 1:
                $cuentas=$cuentaRepository->findBy(['empresa'=>$user->getEmpresaActual()]);
            break;
            default:
                $cuentas=$cuentaRepository->findByPers($usuarioRepository->find($user->getId()));
        }
        $form->handleRequest($request);

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
            return $this->redirectToRoute('nocontrata_index');
        }
        return $this->render('nocontrata/new.html.twig', [
        'agenda'=>$agenda,
        'form' => $form->createView(),
        'pagina'=>$pagina->getNombre(),
        'cuentas'=>$cuentas,
        ]);
    }
}
