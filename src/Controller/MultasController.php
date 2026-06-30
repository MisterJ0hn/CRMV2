<?php

namespace App\Controller;


use App\Entity\Pago;
use App\Entity\Usuario;
use App\Entity\Cuota;
use App\Entity\PagoCuotas;
use App\Entity\Contrato;
use App\Entity\Importacion;
use App\Form\PagoType;
use App\Form\CuotaType;
use App\Form\ImportacionType;
use App\Repository\ImportacionRepository;
use App\Repository\ContratoRepository;
use App\Repository\ContratoRolRepository;
use App\Repository\PagoRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\CuotaRepository;
use App\Repository\CuentaRepository;
use App\Repository\PagoCuentasRepository;
use App\Repository\PagoCanalRepository;
use App\Repository\PagoTipoRepository;
use App\Repository\CuentaCorrienteRepository;
use App\Repository\PagoCuotasRepository;
use App\Repository\UsuarioRepository;
use App\Service\Toku;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;


/**
 * @Route("/multas")
 */
class MultasController extends AbstractController
{
    /**
     * @Route("/", name="multas_index", methods={"GET","POST"})
     */
    public function index(Request $request, Contrato $contrato,CuotaRepository $cuotaRepository,ModuloPerRepository $moduloPerRepository): Response
    {
        //$this->denyAccessUnlessGranted('view','multas');
        $user=$this->getUser();
        //$pagina=$moduloPerRepository->findOneByName('multas',$user->getEmpresaActual());
    
        $cuota=$cuotaRepository->findBy(['contrato'=>$contrato,'isMulta'=>true]);

        return $this->render('pago/verpagos.html.twig', [
            'pagos' => $cuota,
            'pagina'=>"Multas ",
            'contrato'=>$contrato,
            'error_toast'=>$error_toast,
        ]);
    }
    /**
     * @Route("/{id}", name="multas_view", methods={"GET","POST"})
     */
    public function view(Request $request, Contrato $contrato,CuotaRepository $cuotaRepository,PagoRepository $pagoRepository,ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','multas');
        $user=$this->getUser();

        $error_toast="";
        if(null !== $request->query->get('error_toast')){
            $error_toast=$request->query->get('error_toast');
        }

        $pagina=$moduloPerRepository->findOneByName('multas',$user->getEmpresaActual());
    
        $cuota=$cuotaRepository->findOneByPrimeraVigente($contrato->getId(),true);
        $pagos=$pagoRepository->findByContrato($contrato,true);
        return $this->render('multas/index.html.twig', [
            'cuota' => $cuota,
            'pagina'=>"Multas ",
            'pagos'=>$pagos,
            'contrato'=>$contrato,
            'error_toast'=>$error_toast,
        ]);
    }
    /**
     * @Route("/{id}/new", name="multa_new", methods={"GET","POST"})
     */
    public function new(Request $request,
                        Cuota $cuota,
                        CuotaRepository $cuotaRepository,
                        PagoTipoRepository $pagoTipoRepository,
                        PagoCuotasRepository $pagoCuotasRepository,
                        ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('create','multas');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('multas',$user->getEmpresaActual());
        $contrato=$cuota->getContrato();

        $tipoPago=false;
        if(isset($_POST['cboTipo']))
            $tipoPago=$_POST['cboTipo'];
        

        $pago = new Pago();

        if($tipoPago){
            $tipo=$pagoTipoRepository->find($tipoPago);
            $pago->setPagoTipo($tipo);
            $pago->setComprobante("nodisponible.png");
        }
        $pago->setFechaRegistro(new \DateTime(date('Y-m-d H:i:s')));
        $pago->setUsuarioRegistro($user);
        $pago->setMonto($cuota->getMonto());
        $form = $this->createForm(PagoType::class, $pago);
       
       
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fechaPago=$request->request->get('fechaPago');
            $entityManager = $this->getDoctrine()->getManager();
            $pago->setFechaPago(new \DateTime(date('Y-m-d H:i',strtotime($fechaPago))));
            $pago->setHoraPago(new \DateTime(date('H:i',strtotime($fechaPago))));
            $entityManager->persist($pago);
            $entityManager->flush();
            $error_toast="Toast.fire({
                icon: 'success',
                title: 'Multa Pagada!!'
              })";
            $pagoCuotasRepository->asociarPagos($contrato,$cuotaRepository,$pagoCuotasRepository,$pago,true);
            
           
            return $this->redirectToRoute('verpagos_index',['id'=>$contrato,'error_toast'=>$error_toast]);
           
        }

        if($tipoPago){
            return $this->render('multas/new.html.twig', [
                'pago' => $pago,
                'contrato'=>$contrato,
                'pagina'=>"Pagar ".$pagina->getNombre(),
                'form' => $form->createView(),
                'isBoucher'=>$tipo->getIsBoucher(),
                'etapa'=>1,
            ]);
        }else{

            return $this->render('multas/tipoPago.html.twig', [
                
                'contrato'=>$contrato,
                'pagoTipos'=>$pagoTipoRepository->findAll(),
             ] );
        }
    }

    /**
     * @Route("/{id}/edit", name="multa_edit", methods={"GET","POST"})
     */
    public function edit(Request $request,Cuota $cuota,CuotaRepository $cuotaRepository,PagoCuotasRepository $pagoCuotasRepository,ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','multas');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('multas',$user->getEmpresaActual());
        $contrato=$cuota->getContrato();
        $error_toast='';
        $form = $this->createForm(CuotaType::class, $cuota);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$toku = new Toku();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($cuota);
            $entityManager->flush();

            //$cuotaResultToku=$toku->crearInvoice($contrato->getCliente()->getTokuId(),strval($contrato->getFolio()."_Multa"),$cuota->getMonto(),$cuota->getFechaPago()->format('Y-m-d'));
            //error_log("<br><br> Modifica Cuota : ".$cuota->getNumero()." Invoice id ".$cuota->getInvoiceId()." monto ".$cuota->getMonto(),3,"/home/micrm.cl/test/TokuWebhook_log");
            
            

            $error_toast="Toast.fire({
                icon: 'success',
                title: 'Multa modificada con exito!!'
              })";
            return $this->redirectToRoute('verpagos_index',['id'=>$contrato,'error_toast'=>$error_toast]);

        }

        return $this->render('multas/edit.html.twig', [
            'cuota' => $cuota,
            'contrato'=>$contrato,
            'pagina'=>"Editar ".$pagina->getNombre(),
            'form' => $form->createView(),
        ]);

    }
     
    /**
     * @Route("/{id}", name="multa_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Pago $pago): Response
    {
        $this->denyAccessUnlessGranted('full','multas');
        $user=$this->getUser();
        //$toku = new Toku();
        if ($this->isCsrfTokenValid('delete'.$pago->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $pago->setUsuarioAnulacion($user);
            $pago->setFechaAnulacion(new \DateTime(date("Y-m-d H:i")));
            $pago->setAnulado(true);
            $entityManager->persist($pago);
            $entityManager->flush();
            $contrato=null;
            $pagoCuotas=$pago->getPagoCuotas();
            foreach($pagoCuotas as $pagoCuota){
                $cuota=$pagoCuota->getCuota();
                $contrato=$cuota->getContrato();
                $cuota->setPagado($cuota->getPagado()-$pagoCuota->getMonto());
                $contrato=$cuota->getContrato();
                if($cuota->getIsMulta()==true){
                    $productId=$contrato->getFolio()."_M";
                }else{
                    $productId=$contrato->getFolio();
                }
                //$cuotaResultToku=$toku->crearInvoice($contrato->getCliente()->getTokuId(),$productId,($cuota->getMonto()-$cuota->getPagado()),$cuota->getFechaPago()->format('Y-m-d'),false,false);
                
                $entityManager->remove($pagoCuota);
                $entityManager->flush();

                $contrato->setIsFinalizado(false);
                $entityManager->persist($contrato);
                $entityManager->flush();

            }
        }
        if(null != $contrato){
            return $this->redirectToRoute('verpagos_index',['id'=>$contrato->getId()]);
        }else{
            return $this->redirectToRoute('pago_index');
        }
    }
}
