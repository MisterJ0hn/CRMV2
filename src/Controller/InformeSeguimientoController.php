<?php

namespace App\Controller;

use App\Entity\InfAgendados;
use App\Entity\InfSeguimiento;
use App\Repository\AgendaObservacionRepository;
use App\Repository\InfSeguimientoRepository;
use App\Repository\AgendaRepository;
use App\Repository\InfAgendadosRepository;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
     * @Route("/informe_seguimiento")
     */
class InformeSeguimientoController extends AbstractController
{
    /**
     * @Route("/", name="informe_seguimiento_index", methods={"GET"})
     */
    public function index(Request $request,
                            AgendaRepository $agendaRepository,
                            InfSeguimientoRepository $infSeguimientoRepository,
                            InfAgendadosRepository $infAgendadosRepository,
                            AgendaObservacionRepository $agendaObservacionRepository,
                            PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('view','informe_seguimiento');

        $user=$this->getUser();
        
        $dateInicio=date("Y-m-d");
        $dateFin=date("Y-m-d")." 23:59";

        if(null !== $request->query->get('bFecha')){
            $aux_fecha=explode(" - ",$request->query->get('bFecha'));
            $dateInicio=$aux_fecha[0];
            $dateFin=$aux_fecha[1];
            
        }else{
            $dateInicio=date('Y-m-d',strtotime($dateInicio." - 11 days"));
            $dateFin=date('Y-m-d');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $infSeguimientoRepository->removeBySesion($user->getId());

        $date=$dateInicio;
        $qdias=0;
        for ($i=0; date('Y-m-d',strtotime($date)) <= date('Y-m-d', strtotime($dateFin)) ; $i++) { 
            
            $infSeguimiento=new InfSeguimiento();
            $infSeguimiento->setUsuario($user);
            $infSeguimiento->setFechaCarga(new DateTime($date));
            $infSeguimiento->setSinAtencion(0);
            $infSeguimiento->setA24h(0);
            $infSeguimiento->setA48h(0);
            $infSeguimiento->setMasDe48h(0);

            $entityManager->persist($infSeguimiento);
            $entityManager->flush();



            //$sinAtenciones=$agendaRepository->findByPers(null,$user->getEmpresaActual(),null,"1",null,null,"a.fechaCarga>='$date' and a.fechaCarga<='$dateFin 23:59'");
            $sinAtenciones=$agendaRepository->findByGroupSeguimiento(null,$user->getEmpresaActual(),null,"1",null,null,"a.fechaCarga>='$date' and a.fechaCarga<='$date 23:59'");


            foreach ($sinAtenciones as $sinAtencion) {
                $infSeguimiento=new InfSeguimiento();
                $infSeguimiento->setUsuario($user);
                $infSeguimiento->setFechaCarga(new DateTime($date));
                $infSeguimiento->setSinAtencion($sinAtencion['valor']);
                $infSeguimiento->setA24h(0);
                $infSeguimiento->setA48h(0);
                $infSeguimiento->setMasDe48h(0);

                $entityManager->persist($infSeguimiento);
                $entityManager->flush();
            }

            $a24hs=$agendaRepository->findByGroupSeguimiento(null,$user->getEmpresaActual(),null,"2",null,null,"a.fechaCarga>='$date' and a.fechaCarga<='$date 23:59'  and (DATEDIFF(now(), a.fechaSeguimiento)) <= 1 ");

            foreach ($a24hs as $a24h) {
                $infSeguimiento=new InfSeguimiento();
                $infSeguimiento->setUsuario($user);
                $infSeguimiento->setFechaCarga(new DateTime($date));
                $infSeguimiento->setSinAtencion(0);
                $infSeguimiento->setA24h($a24h['valor']);
                $infSeguimiento->setA48h(0);
                $infSeguimiento->setMasDe48h(0);

                $entityManager->persist($infSeguimiento);
                $entityManager->flush();

            }

            $a48hs=$agendaRepository->findByGroupSeguimiento(null,$user->getEmpresaActual(),null,"2",null,null,"a.fechaCarga>='$date' and a.fechaCarga<='$date 23:59'  and (DATEDIFF(now(),a.fechaSeguimiento)) <= 2 and  (DATEDIFF(now(), a.fechaSeguimiento)) > 1 ");

            foreach ($a48hs as $a48h) {
                $infSeguimiento=new InfSeguimiento();
                $infSeguimiento->setUsuario($user);
                $infSeguimiento->setFechaCarga(new DateTime($date));
                $infSeguimiento->setSinAtencion(0);
                $infSeguimiento->setA24h(0);
                $infSeguimiento->setA48h($a48h['valor']);
                $infSeguimiento->setMasDe48h(0);

                $entityManager->persist($infSeguimiento);
                $entityManager->flush();

            }

            $masde48hs=$agendaRepository->findByGroupSeguimiento(null,$user->getEmpresaActual(),null,"2",null,null,"a.fechaCarga>='$date' and a.fechaCarga<='$date 23:59'  and (DATEDIFF(now(),a.fechaSeguimiento)) > 2 ");

            foreach ($masde48hs as $masde48h) {
                $infSeguimiento=new InfSeguimiento();
                $infSeguimiento->setUsuario($user);
                $infSeguimiento->setFechaCarga(new DateTime($date));
                $infSeguimiento->setSinAtencion(0);
                $infSeguimiento->setA24h(0);
                $infSeguimiento->setA48h(0);
                $infSeguimiento->setMasDe48h($masde48h['valor']);

                $entityManager->persist($infSeguimiento);
                $entityManager->flush();

            }

            $date=date('Y-m-d',strtotime($date."+ 1 days"));
            $qdias=$i+1;

        }
        
        

        $query=$infSeguimientoRepository->findByGroupPersonalizado(['usuario'=>$user->getId()],['fechaCarga'=>'Asc'],['fechaCarga']);
        $infSeguimientosGraf=$infSeguimientoRepository->findByGroupPersonalizado(['usuario'=>$user->getId()],['fechaCarga'=>'Asc'],['fechaCarga']);
        



        $infSeguimientos=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            6 /*limit per page*/,
            array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));


            
            /**
             * busqueda de abogados agendados
             */

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


        $criterioAvanzado=[];

        array_push($criterioAvanzado,['a.fechaCarga','<=',"'$dateFin 23:59:59'"]);
        array_push($criterioAvanzado,['a.fechaCarga','>=',"'$dateInicio'"]);


        $nocalifica=$agendaRepository->findByNocalifica([
                                                            'status'=>9
                                                            ],
                                                        null,
                                                        null,
                                                        $criterioAvanzado
                                                        );
        $nocalificaNull=$agendaRepository->findByNocalificaSubStatusNulls([
                                                            'status'=>9
                                                            ],
                                                        null,
                                                        null,
                                                        $criterioAvanzado
                                                        );


        $totalLeads= $agendaRepository->findByPersGroup(null,$user->getEmpresaActual(),null,null,null,null,"a.fechaCarga <= '$dateFin 23:59:59' and a.fechaCarga >= '$dateInicio'");
        return $this->render('informe_seguimiento/index.html.twig', [
            'pagina' => 'Jefatura',
            'infSeguimientos'=>$infSeguimientos,
            'infSeguimientosGraf'=>$infSeguimientosGraf,
            'total_agendado'=>$totalAgendados,
            'total_agenda'=>$totalAgenda,
            'informeAgendados'=>$informes,
            'nocalifica'=>$nocalifica,
            'nocalificaNull'=>$nocalificaNull,
            'total_leads'=>$totalLeads,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'qdias'=>$qdias
        ]);
    }
}
