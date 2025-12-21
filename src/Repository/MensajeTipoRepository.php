<?php

namespace App\Repository;

use App\Entity\MensajeTipo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MensajeTipo|null find($id, $lockMode = null, $lockVersion = null)
 * @method MensajeTipo|null findOneBy(array $criteria, array $orderBy = null)
 * @method MensajeTipo[]    findAll()
 * @method MensajeTipo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MensajeTipoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MensajeTipo::class);
    }

    // /**
    //  * @return MensajeTipo[] Returns an array of MensajeTipo objects
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
    public function findOneBySomeField($value): ?MensajeTipo
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
