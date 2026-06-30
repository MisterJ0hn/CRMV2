<?php

namespace App\Repository;

use App\Entity\Contrato;
use App\Entity\ErrorSistemaLog;
use App\Entity\Pago;
use App\Entity\PagoCuotas;
use App\Service\Toku;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PagoCuotas|null find($id, $lockMode = null, $lockVersion = null)
 * @method PagoCuotas|null findOneBy(array $criteria, array $orderBy = null)
 * @method PagoCuotas[]    findAll()
 * @method PagoCuotas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PagoCuotasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PagoCuotas::class);
    }

    public function findByContrato($value)
    {
        return $this->createQueryBuilder('p')
            ->join('p.cuota','c')
            ->andWhere('c.contrato = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function findByPago($pago)
    {
        return $this->createQueryBuilder('p')
            ->addSelect('sum(p.monto) as total')
            ->andWhere('p.pago = :val')
            ->setParameter('val', $pago)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByPers($usuario=null,$empresa=null,$compania=null,$filtro=null,$otros=null)
    {
        $query=$this->createQueryBuilder('p')
        ->join('p.pago','pa')
        ->join('p.cuota','c')
        ->join('c.contrato','co')
        ->join('co.agenda','a')
        ->join('a.cuenta','cu')
        ->join('pa.usuarioRegistro','u');
        

        if(!is_null($usuario)){
            $query->andWhere('u.id = '.$usuario);
        }
        if(!is_null($empresa)){
            $query->andWhere('cu.empresa = '.$empresa);
        }
        
        if(!is_null($filtro)){ 
            $query->andWhere("(co.nombre like '%$filtro%' or co.rut like '%$filtro%')");

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        
        if(!is_null($otros)){ 
            $query->andWhere($otros);
        }
        $query->orderBy('co.id desc, pa.fechaPago', 'Desc')
        ;
     
        return $query->getQuery()
        ->getResult()
        ;

    }

    public function asociarPagos($contrato,$cuotaRepository,$pagoCuotasRepository,$pago,$esMulta=false){
        
        $entityManager = $this->_em;
        $contrato->setIsFinalizado(false);
        try{
        
            do{
                $pagostatus=false;
                $cuota=$cuotaRepository->findOneByPrimeraVigenteAPagar($contrato->getId(),$esMulta);
                $pagoCuotas=$pagoCuotasRepository->findByPago($pago->getId());

                if(null == $pagoCuotas["total"]){
                    $total=0;
                }else{
                    $total=$pagoCuotas["total"];
                }
            
                if($cuota){
                    //cuando el pago es menor o igual a la deuda.
                    if(($pago->getMonto()-$total)<=($cuota->getMonto()-$cuota->getPagado())){
                        
                        $cuota->setPagado($cuota->getPagado()+($pago->getMonto()-$total));

                        $entityManager->persist($cuota);
                        $entityManager->flush();

                        $pagoCuota=new PagoCuotas();
                        $pagoCuota->setCuota($cuota);
                        $pagoCuota->setPago($pago);
                        $pagoCuota->setMonto($pago->getMonto()-$total);
                        $entityManager->persist($pagoCuota);
                        $entityManager->flush();

                    }else{
                        
                        //si pago es mayo o igual al monto de la cuota
                        if(($pago->getMonto()-$total)>=($cuota->getMonto()-$cuota->getPagado())){
                            
                            
                            $pagoCuota=new PagoCuotas();
                            $pagoCuota->setCuota($cuota);
                            $pagoCuota->setPago($pago);
                            $pagoCuota->setMonto($cuota->getMonto()-$cuota->getPagado());
                            $entityManager->persist($pagoCuota);
                            $entityManager->flush();

                            $cuota->setPagado($cuota->getMonto());
                            $entityManager->persist($cuota);
                            $entityManager->flush();
                            $pagostatus=true;
                        }else if(($pago->getMonto()-$total)<($cuota->getMonto()-$cuota->getPagado())){
                            
                            
                            $pagoCuota=new PagoCuotas();
                            $pagoCuota->setCuota($cuota);
                            $pagoCuota->setPago($pago);
                            $pagoCuota->setMonto(($pago->getMonto()-$total));
                            $entityManager->persist($pagoCuota);
                            $entityManager->flush();

                            $cuota->setPagado(($pago->getMonto()-$total)+$cuota->getPagado());

                            $entityManager->persist($cuota);
                            $entityManager->flush();
                        }
                    }

                    //tomamos las cuotas y reseteamos el q_mov de las cobranzas
                    $cobranzas = $contrato->getCobranzas();
                    $qMov=0;
                    foreach($cobranzas as $cobranza){
                        $qMov++;
                    }
                    if($contrato->getQMov()-$qMov>0){
                        $contrato->setQMov($contrato->getQMov()-$qMov);
                        $entityManager->persist($contrato);
                        $entityManager->flush();
                    }
                }else{
                    //si estan todas las cuotas pagadas, buscamos la ultima cuota pagada y agregamos el monto sobrante a esa cuota
                    
                    
                    if($pago->getMonto()-$total>0){
                        $cuota=$cuotaRepository->findOneByUltimaPagada($contrato->getId());
                        if($cuota){

                            $pagoCuota=new PagoCuotas();
                            $pagoCuota->setCuota($cuota);
                            $pagoCuota->setPago($pago);
                            $pagoCuota->setMonto(($pago->getMonto()-$total));
                            $entityManager->persist($pagoCuota);
                            $entityManager->flush();

                            $cuota->setPagado(($pago->getMonto()-$total)+$cuota->getPagado());

                            $entityManager->persist($cuota);
                            $entityManager->flush();
                            //tomamos las cuotas y reseteamos el q_mov de las cobranzas
                            $cobranzas = $contrato->getCobranzas();
                            $qMov=0;
                            foreach($cobranzas as $cobranza){
                                $qMov++;
                            }
                            if($contrato->getQMov()-$qMov>0){
                                $contrato->setQMov($contrato->getQMov()-$qMov);
                                $entityManager->persist($contrato);
                                $entityManager->flush();
                            }
                        }
                    }
                    if($pago->getPagoTipo()->getId()==7){

            
                        $cuota=$cuotaRepository->findOneByUltimaPagada($contrato->getId());
                        if($cuota){
        
                            $pagoCuota=new PagoCuotas();
                            $pagoCuota->setCuota($cuota);
                            $pagoCuota->setPago($pago);
                            $pagoCuota->setMonto(($pago->getMonto()-$total));
                            $entityManager->persist($pagoCuota);
                            $entityManager->flush();
        
                            $cuota->setPagado(($pago->getMonto()-$total)+$cuota->getPagado());
        
                            $entityManager->persist($cuota);
                            $entityManager->flush();
                            //tomamos las cuotas y reseteamos el q_mov de las cobranzas
                            $cobranzas = $contrato->getCobranzas();
                            $qMov=0;
                            foreach($cobranzas as $cobranza){
                                $qMov++;
                            }
                            if($contrato->getQMov()-$qMov>0){
                                $contrato->setQMov($contrato->getQMov()-$qMov);
                                $entityManager->persist($contrato);
                                $entityManager->flush();
                            }
                        }
                    }
                }
            

            }while($pagostatus);
            $cuota=$cuotaRepository->findOneByPrimeraVigente($contrato->getId(),$esMulta);
            $entityManager->persist($contrato);
            $entityManager->flush();
        }catch(\Exception $e){
            $errorSistema = new ErrorSistemaLog();            
            $errorSistema->setMensaje($e->getMessage());
            $errorSistema->setFecha(new \DateTime(date("Y-m-d H:i:s")));
            $errorSistema->setModulo("asociarPagos");
            $entityManager->persist($errorSistema);
            $entityManager->flush();
        }
        return true;
    }
    public function asociarNC(Contrato $contrato,Pago $pago){

        $entityManager = $this->_em;
        $cuotaRepository = $entityManager->getRepository('App\Entity\Cuota');
        //obtewngo el monto de la nc, y la transformo a valor positivo, puesto que este se registra en negativo
        $valor_nc = $pago->getMonto()*-1;
        
        do{
            $cuota_ultimo_pago=$cuotaRepository->findOneByUltimaPagada($contrato->getId(),false);
            if($cuota_ultimo_pago){
                if($cuota_ultimo_pago->getPagado()>0){
                    $pagoCuota=new PagoCuotas();

                    if($valor_nc>=$cuota_ultimo_pago->getPagado()){
                        $valor_nc = $valor_nc - $cuota_ultimo_pago->getPagado();
                        

                        $pagoCuota->setCuota($cuota_ultimo_pago);
                        $pagoCuota->setPago($pago);
                        //El pago se registra en negativo puesto que es un descuento a la deuda
                        $pagoCuota->setMonto($cuota_ultimo_pago->getPagado()*-1);
                        $entityManager->persist($pagoCuota);
                        $entityManager->flush();
                        $cuota_ultimo_pago->setPagado(null);
                    }else{
                        
                        $pagoCuota->setCuota($cuota_ultimo_pago);
                        $pagoCuota->setPago($pago);
                        $pagoCuota->setMonto($valor_nc *-1);
                        $entityManager->persist($pagoCuota);
                        $entityManager->flush();                        
                        $cuota_ultimo_pago->setPagado($cuota_ultimo_pago->getPagado() - $valor_nc);
                        $valor_nc = 0;
                    }
                    $entityManager->persist($cuota_ultimo_pago);
                    $entityManager->flush();
                }
            }else{
                //$valor_nc = 0;
                $primera_cuota_a_pagar=$cuotaRepository->findOneByPrimeraVigenteAPagar($contrato->getId(),false);
                if($primera_cuota_a_pagar){
                    $pagoCuota=new PagoCuotas();
                    $pagoCuota->setCuota($primera_cuota_a_pagar);
                    $pagoCuota->setPago($pago); 
                    $pagoCuota->setMonto($valor_nc *-1);
                    $entityManager->persist($pagoCuota);
                    $entityManager->flush();
                    
                    $primera_cuota_a_pagar->setPagado($valor_nc*-1);
                    $entityManager->persist($primera_cuota_a_pagar);
                    $entityManager->flush();
                    
                }
                $valor_nc = 0;
            }
    
        }while($valor_nc>0);

       
        return true;
    }


    public function findPagos(){
        $query=$this->createQueryBuilder('p')
        ->join('p.cuota','cu')
        ->join('p.pago','pa')
        ->where("pa.fechaRegistro>='2022-08-10'")
        ->andWhere('cu.invoiceId is not null')
        ->andWhere("pa.pagoCanal<4");

        return $query->getQuery()
                ->getResult();
    }

    // /**
    //  * @return PagoCuotas[] Returns an array of PagoCuotas objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PagoCuotas
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
