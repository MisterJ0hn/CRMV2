<?php

namespace App\Repository;

use App\Entity\AgendaRol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AgendaRol|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgendaRol|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgendaRol[]    findAll()
 * @method AgendaRol[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaRolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgendaRol::class);
    }

    // /**
    //  * @return AgendaRol[] Returns an array of AgendaRol objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AgendaRol
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
