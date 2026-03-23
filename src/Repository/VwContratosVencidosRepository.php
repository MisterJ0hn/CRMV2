<?php

namespace App\Repository;

use App\Entity\VwContratosVencidos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VwContratosVencidos>
 *
 * @method VwContratosVencidos|null find($id, $lockMode = null, $lockVersion = null)
 * @method VwContratosVencidos|null findOneBy(array $criteria, array $orderBy = null)
 * @method VwContratosVencidos[]    findAll()
 * @method VwContratosVencidos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VwContratosVencidosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VwContratosVencidos::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(VwContratosVencidos $entity, bool $flush = true): void
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
    public function remove(VwContratosVencidos $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findByPers($usuario=null,$empresa=null,$compania=null,$filtro=null,$agendador=null, $otros=null, $deuda = false, $status=null)
    {
        $query=$this->createQueryBuilder('c');
        $query->join('c.contrato','co');
        $query->join('co.agenda','a');
        $query->join('a.cuenta','cu');

        //$query->andWhere('(DATEDIFF(now(), c.fechaPrimerPago)/30)<c.vigencia');
        if(!is_null($empresa)){
            
            $query->andWhere('cu.empresa = '.$empresa);
        }
        if(!is_null($usuario)){
            $query->andWhere('a.abogado = '.$usuario);
        }
        if(!is_null($agendador)){
            
            $query->andWhere('a.agendador = '.$agendador);
        }
        if(!is_null($filtro)){ 
            $query->andWhere("(co.nombre like '%$filtro%' or co.telefono like '%$filtro%' or co.email like '%$filtro%')")
         ;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        if(!is_null($status)){
            if($status==0){
                $query->andWhere('c.FechaEncuesta is not null');
            }
            if($status==1){
                $query->andWhere('c.FechaGestion is not null');
            }
        }
        return $query
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return VwContratosVencidos[] Returns an array of VwContratosVencidos objects
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
    public function findOneBySomeField($value): ?VwContratosVencidos
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
