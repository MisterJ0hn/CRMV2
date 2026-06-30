<?php

namespace App\Repository;

use App\Entity\Mee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Mee|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mee|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mee[]    findAll()
 * @method Mee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mee::class);
    }

    // /**
    //  * @return Mee[] Returns an array of Mee objects
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
    public function findOneBySomeField($value): ?Mee
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
