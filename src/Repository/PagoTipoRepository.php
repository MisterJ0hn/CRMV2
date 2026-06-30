<?php

namespace App\Repository;

use App\Entity\PagoTipo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PagoTipo|null find($id, $lockMode = null, $lockVersion = null)
 * @method PagoTipo|null findOneBy(array $criteria, array $orderBy = null)
 * @method PagoTipo[]    findAll()
 * @method PagoTipo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PagoTipoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PagoTipo::class);
    }

    public function obtenerTiposPago(){
        return $this->createQueryBuilder('p')
            ->andWhere('p.estado = 1')
            ->andWhere('p.id != 7')
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    // /**
    //  * @return PagoTipo[] Returns an array of PagoTipo objects
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
    public function findOneBySomeField($value): ?PagoTipo
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
