<?php

namespace App\Repository;

use App\Entity\ContratoTramitador;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContratoTramitador|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContratoTramitador|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContratoTramitador[]    findAll()
 * @method ContratoTramitador[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContratoTramitadorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContratoTramitador::class);
    }

    // /**
    //  * @return ContratoTramitador[] Returns an array of ContratoTramitador objects
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
    public function findOneBySomeField($value): ?ContratoTramitador
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
