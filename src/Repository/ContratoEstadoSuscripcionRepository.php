<?php

namespace App\Repository;

use App\Entity\ContratoEstadoSuscripcion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContratoEstadoSuscripcion>
 *
 * @method ContratoEstadoSuscripcion|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContratoEstadoSuscripcion|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContratoEstadoSuscripcion[]    findAll()
 * @method ContratoEstadoSuscripcion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContratoEstadoSuscripcionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContratoEstadoSuscripcion::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(ContratoEstadoSuscripcion $entity, bool $flush = true): void
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
    public function remove(ContratoEstadoSuscripcion $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return ContratoEstadoSuscripcion[] Returns an array of ContratoEstadoSuscripcion objects
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
    public function findOneBySomeField($value): ?ContratoEstadoSuscripcion
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
