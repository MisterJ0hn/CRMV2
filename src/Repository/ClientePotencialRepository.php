<?php

namespace App\Repository;

use App\Entity\ClientePotencial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ClientePotencial|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientePotencial|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientePotencial[]    findAll()
 * @method ClientePotencial[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientePotencialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClientePotencial::class);
    }

    // /**
    //  * @return ClientePotencial[] Returns an array of ClientePotencial objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ClientePotencial
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
