<?php

namespace App\Repository;

use App\Entity\VwContratoPagoAutomatico;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VwContratoPagoAutomatico>
 *
 * @method VwContratoPagoAutomatico|null find($id, $lockMode = null, $lockVersion = null)
 * @method VwContratoPagoAutomatico|null findOneBy(array $criteria, array $orderBy = null)
 * @method VwContratoPagoAutomatico[]    findAll()
 * @method VwContratoPagoAutomatico[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VwContratoPagoAutomaticoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VwContratoPagoAutomatico::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(VwContratoPagoAutomatico $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(VwContratoPagoAutomatico $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findBySuscripcion($filtro=null, $folio=null, $estado=null, $fecha=null, $cerrador=null){
        $query=$this->createQueryBuilder('c');
        $query->join('c.contrato','co');
        $query->join('co.agenda','a');
        $query->join('a.cuenta','cu');

        $query->andWhere('a.status not in (13,15)');


        if(!is_null($estado) && $estado != ''){
            
           $query->andWhere("c.estadoSuscripcionId = $estado");
    
        }
        if(!is_null($cerrador) && $cerrador != ''){
            $query->andWhere("a.abogado = '$cerrador'");
        }

        if(!is_null($filtro)){
            $query->andWhere("(co.nombre like '%$filtro%' or co.telefono like '%$filtro%')");
        }

        if(!is_null($folio) && $folio != ''){
            $query->andWhere("(co.folio = '$folio' or co.agenda = '$folio')");
        }

        if(!is_null($fecha) && $fecha != ''){
            $query->andWhere($fecha);
        }

        $query->orderBy('co.id','DESC');
        $query->groupBy('c.contrato');

        return $query->getQuery()->getResult();
    }

    public function findBySuscripcionTotal($filtro=null, $folio=null, $estado=null, $fecha=null, $cerrador=null){
        $query=$this->createQueryBuilder('c');
        $query->select('sum(c.montoPagado) as total');
        $query->join('c.contrato','co');
        $query->join('co.agenda','a');
        $query->join('a.cuenta','cu');
        $query->andWhere('a.status not in (13,15)');
        $query->andWhere('co.aceptaSuscripcion = true');


        if(!is_null($estado) && $estado != ''){
           
                $query->andWhere("c.estadoSuscripcionId = $estado");
           
        }
        if(!is_null($cerrador) && $cerrador != ''){
            $query->andWhere("a.abogado = '$cerrador'");
        }


        if(!is_null($filtro)){
            $query->andWhere("(co.nombre like '%$filtro%' or co.telefono like '%$filtro%')");
        }

        if(!is_null($folio) && $folio != ''){
            $query->andWhere("(co.folio = '$folio' or co.agenda = '$folio')");
        }

        if(!is_null($fecha) && $fecha != ''){
            $query->andWhere($fecha);
        }

        return $query->getQuery()->getOneOrNullResult();
    }

    public function totalPorEstado($filtro=null, $folio=null, $estado=null, $fecha=null, $cerrador=null){
        $query=$this->createQueryBuilder('c');
        $query->select('c,count(c.id) as total');
        $query->join('c.contrato','co');
        $query->join('co.agenda','a');
        $query->join('a.cuenta','cu');
        $query->andWhere('a.status not in (13,15)');
        $query->andWhere('co.aceptaSuscripcion = true');


        if(!is_null($estado) && $estado != ''){

            $query->andWhere("c.estadoSuscripcionId = $estado");
        }
        if(!is_null($cerrador) && $cerrador != ''){
            $query->andWhere("a.abogado = '$cerrador'");
        }


        if(!is_null($filtro)){
            $query->andWhere("(co.nombre like '%$filtro%' or co.telefono like '%$filtro%')");
        }

        if(!is_null($folio) && $folio != ''){
            $query->andWhere("(co.folio = '$folio' or co.agenda = '$folio')");
        }

        if(!is_null($fecha) && $fecha != ''){
            $query->andWhere($fecha);
        }
        $query->groupBy("c.estadoSuscripcion");
        $query->orderBy("c.estadoSuscripcionOrden","Asc");
        
        return $query->getQuery()->getResult();
    }

    public function totalPorAbogado($filtro=null, $folio=null, $estado=null, $fecha=null, $cerrador=null){
        $query=$this->createQueryBuilder('c');
        $query->select('c,count(c.id) as total');
        $query->join('c.contrato','co');
        $query->join('co.agenda','a');
        $query->join('a.cuenta','cu');
        $query->andWhere('a.status not in (13,15)');
        $query->andWhere('co.aceptaSuscripcion = true');


        if(!is_null($estado) && $estado != ''){
       
                $query->andWhere("c.estadoSuscripcionId = $estado");

        }
        if(!is_null($cerrador) && $cerrador != ''){
            $query->andWhere("a.abogado = '$cerrador'");
        }


        if(!is_null($filtro)){
            $query->andWhere("(co.nombre like '%$filtro%' or co.telefono like '%$filtro%')");
        }

        if(!is_null($folio) && $folio != ''){
            $query->andWhere("(co.folio = '$folio' or co.agenda = '$folio')");
        }

        if(!is_null($fecha) && $fecha != ''){
            $query->andWhere($fecha);
        }
        $query->groupBy("a.abogado");
        return $query->getQuery()->getResult();
    }
    // /**
    //  * @return VwContratoPagoAutomatico[] Returns an array of VwContratoPagoAutomatico objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?VwContratoPagoAutomatico
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
