<?php

namespace App\Repository;

use App\Entity\Movimientospjud;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Movimientospjud|null find($id, $lockMode = null, $lockVersion = null)
 * @method Movimientospjud|null findOneBy(array $criteria, array $orderBy = null)
 * @method Movimientospjud[]    findAll()
 * @method Movimientospjud[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovimientospjudRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movimientospjud::class);
    }

    // /**
    //  * @return Movimientospjud[] Returns an array of Movimientospjud objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Movimientospjud
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
