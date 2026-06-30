<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Agenda;
use App\Entity\AgendaObservacion;
use App\Entity\Usuario;
use App\Form\AgendaType;
use App\Repository\AgendaRepository;
use App\Repository\ReunionRepository;
use App\Repository\UsuarioRepository;
use App\Repository\AgendaStatusRepository;
use App\Repository\CuentaRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;


/**
 * @Route("/resumen")
 */
class ResumenController extends AbstractController
{
    /**
     * @Route("/", name="resumen_index", methods={"GET","POST"})
     */
    public function index(AgendaRepository $agendaRepository,CuentaRepository $cuentaRepository,PaginatorInterface $paginator,Request $request): Response
    {
        $this->denyAccessUnlessGranted('view','resumen');

        $user=$this->getUser();
        $filtro=null;
        $compania=null;
        $fecha=null;
        $statues='1,2,10,3';
        $statuesgroup=null;
        $status=null;
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
        }else{
            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');
        }
        $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
        
        if(null !== $request->query->get('bStatus') && trim($request->query->get('bStatus')!='')){
            $status=$request->query->get('bStatus');
            $statues=$status;
            $statuesgroup=$status;
        }
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 1:
                $query=$agendaRepository->findByPers(null,$user->getEmpresaActual(),$compania,$statues,$filtro,null,$fecha);
                $queryresumen=$agendaRepository->findByPersGroup(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,null,$fecha);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
            break;
            default:
                $query=$agendaRepository->findByPers($user->getId(),null,$compania,$statues,$filtro,null,$fecha);
                $queryresumen=$agendaRepository->findByPersGroup($user->getId(),null,$compania,$statuesgroup,$filtro,null,$fecha);
                $companias=$cuentaRepository->findByPers($user->getId());
            break;
        }

         
        
        $agendas=$paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /*page number*/
        20 /*limit per page*/,
        array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));

        return $this->render('resumen/index.html.twig', [
            'agendas' => $agendas,
            'pagina'=>'Panel Agendador',
            'bFiltro'=>$filtro,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'resumenes'=>$queryresumen,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'status'=>$status,
        ]);
    }
    /**
     * @Route("/{id}", name="resumen_new", methods={"GET","POST"})
     */
    public function new(Agenda $agenda,
                        AgendaRepository $agendaRepository,
                        AgendaStatusRepository $agendaStatusRepository,
                        CuentaRepository $cuentaRepository,
                        UsuarioRepository $usuarioRepository,
                        ReunionRepository $reunionRepository,
                        Request $request): Response
    {
        $this->denyAccessUnlessGranted('create','resumen');

        $user=$this->getUser();
        $form = $this->createForm(AgendaType::class, $agenda);
    
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $agenda->setStatus($agendaStatusRepository->find($request->request->get('chkStatus')));
            if(null !== $request->request->get('cboAbogado')){
                $agenda->setAbogado($usuarioRepository->find($request->request->get('cboAbogado')));
            }
            if(null !== $request->request->get('txtCiudad')){
                $agenda->setCiudadCliente($request->request->get('txtCiudad'));
            }
            if(null !== $request->request->get('txtFechaAgendamiento')){
                $agenda->setFechaAsignado(new \DateTime($request->request->get('txtFechaAgendamiento')));
            }
            if(null !== $request->request->get('txtMonto')){
                $agenda->setMonto($request->request->get('txtMonto'));
            }
            if(null !== $request->request->get('cboReunion')){
                $agenda->setReunion($reunionRepository->find($request->request->get('cboReunion')));
            }
            $entityManager = $this->getDoctrine()->getManager();
           

            $observacion=new AgendaObservacion();
            $observacion->setAgenda($agenda);
            $observacion->setUsuarioRegistro($usuarioRepository->find($user->getId()));
            $observacion->setStatus($agendaStatusRepository->find($request->request->get('chkStatus')));
            $observacion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
            $observacion->setObservacion($request->request->get('txtObservacion'));
           
            $entityManager->persist($observacion);
            $entityManager->flush();
            $entityManager->persist($agenda);
            $entityManager->flush();
            return $this->redirectToRoute('resumen_index');
        }
        return $this->render('resumen/new.html.twig', [
            'agenda'=>$agenda,
            'form' => $form->createView(),
            'pagina'=>'Asignar Agenda',
            'statues'=>$agendaStatusRepository->findBy(['perfil'=>[$agenda->getAgendador()->getUsuarioTipo()->getId(),0]],['orden'=>'asc']),
        ]);

    }
    /**
     * @Route("/{id}/engestion", name="resumen_engestion", methods={"GET","POST"})
     */
    public function engestion(Agenda $agenda):Response
    {
        return $this->render('resumen/engestion.html.twig');
    }

    /**
     * @Route("/{id}/abogados", name="resumen_abogados", methods={"GET","POST"})
     */
    public function abogados(Agenda $agenda,Request $request,UsuarioRepository $usuarioRepository,ReunionRepository $reunionRepository):Response
    {
        return $this->render('resumen/abogados.html.twig', [
            'abogados'=>$usuarioRepository->findByCuenta($agenda->getCuenta()->getId(),['usuarioTipo'=>6]),
            'agenda'=>$agenda,
            'reuniones'=>$reunionRepository->findAll(),
            
        ]);
    }

    /**
     * @Route("/{id}/calendario", name="resumen_calendario", methods={"GET","POST"})
     */
    public function calendario(Agenda $agenda,Request $request, UsuarioRepository $usuarioRepository,AgendaRepository $agendaRepository):Response
    {
        //$agendas=$agendaRepository->findBy(['cuenta'=>$agenda->getCuenta()->getId(),'status'=>[4,5]]);
        $agendas=$agendaRepository->findByPers(null,$agenda->getCuenta()->getEmpresa()->getId(),null,'4,5', null);
        $abogados=$usuarioRepository->findByCuenta($agenda->getCuenta()->getId(),['usuarioTipo'=>6]);
        return $this->render('resumen/calendario.html.twig',[
            'agendas'=>$agendas,
            'abogados'=>$abogados,
        ]);
    }
    /**
     * @Route("/{id}/edit", name="resumen_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Agenda $agenda): Response
    {
        $this->denyAccessUnlessGranted('edit','resumen');
        $form = $this->createForm(AgendaType::class, $agenda);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('resumen_index');
        }

        return $this->render('resumen/edit.html.twig', [
            'agenda' => $agenda,
            'form' => $form->createView(),
        ]);
    }
     
}
