<?php

namespace App\Repository;

use App\Entity\PrimeraCuota;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PrimeraCuota|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrimeraCuota|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrimeraCuota[]    findAll()
 * @method PrimeraCuota[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrimeraCuotaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrimeraCuota::class);
    }

    // /**
    //  * @return PrimeraCuota[] Returns an array of PrimeraCuota objects
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
    public function findOneBySomeField($value): ?PrimeraCuota
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
