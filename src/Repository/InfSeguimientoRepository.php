<?php

namespace App\Repository;

use App\Entity\InfSeguimiento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InfSeguimiento|null find($id, $lockMode = null, $lockVersion = null)
 * @method InfSeguimiento|null findOneBy(array $criteria, array $orderBy = null)
 * @method InfSeguimiento[]    findAll()
 * @method InfSeguimiento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method InfSeguimiento[]    findByGroupPersonalizado(array $criteros,array $orderBy, array $groupBy)
 */
class InfSeguimientoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InfSeguimiento::class);
    }

    public function findByGroupPersonalizado(array $criterios=[],array $orderBy=[],array $groupBy=[]){
        $query=$this->createQueryBuilder('i')
        ->select(array('i','sum(i.sinAtencion)','sum(i.a24h)','sum(i.a48h)','sum(i.masDe48h),DATE(i.fechaCarga) as fecha'));

       
        foreach ($criterios as $campo => $valor ) {
        
            $query->andWhere('i.'.$campo.' = '.$valor);
        }
        
        foreach($groupBy as $valor){
            $query->addGroupBy('i.'.$valor);
        }

        foreach ($orderBy as $campo => $valor) {
            $query->addOrderBy('i.'.$campo,$valor);
        }

       
        $query->addGroupBy('fecha');
        return $query->getQuery()
                ->getResult();
    }


    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(InfSeguimiento $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }


    public function removeBySesion(int $sesion): void
    {
        $q = $this->_em->createQuery('delete from App:InfSeguimiento tb where tb.usuario = '.$sesion);
       
        $numDeleted = $q->execute();
    }
    

    // /**
    //  * @return InfSeguimiento[] Returns an array of InfSeguimiento objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?InfSeguimiento
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
