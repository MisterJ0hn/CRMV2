<?php

namespace App\Repository;

use App\Entity\MensajePrioridad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MensajePrioridad>
 *
 * @method MensajePrioridad|null find($id, $lockMode = null, $lockVersion = null)
 * @method MensajePrioridad|null findOneBy(array $criteria, array $orderBy = null)
 * @method MensajePrioridad[]    findAll()
 * @method MensajePrioridad[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MensajePrioridadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MensajePrioridad::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(MensajePrioridad $entity, bool $flush = true): void
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
    public function remove(MensajePrioridad $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return MensajePrioridad[] Returns an array of MensajePrioridad objects
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
    public function findOneBySomeField($value): ?MensajePrioridad
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
