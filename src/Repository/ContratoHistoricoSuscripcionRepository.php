<?php

namespace App\Repository;

use App\Entity\ContratoHistoricoSuscripcion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContratoHistoricoSuscripcion>
 *
 * @method ContratoHistoricoSuscripcion|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContratoHistoricoSuscripcion|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContratoHistoricoSuscripcion[]    findAll()
 * @method ContratoHistoricoSuscripcion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContratoHistoricoSuscripcionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContratoHistoricoSuscripcion::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(ContratoHistoricoSuscripcion $entity, bool $flush = true): void
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
    public function remove(ContratoHistoricoSuscripcion $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return ContratoHistoricoSuscripcion[] Returns an array of ContratoHistoricoSuscripcion objects
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
    public function findOneBySomeField($value): ?ContratoHistoricoSuscripcion
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
