<?php

namespace App\Repository;

use App\Entity\EquipoTrabajoVencimiento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EquipoTrabajoVencimiento>
 *
 * @method EquipoTrabajoVencimiento|null find($id, $lockMode = null, $lockVersion = null)
 * @method EquipoTrabajoVencimiento|null findOneBy(array $criteria, array $orderBy = null)
 * @method EquipoTrabajoVencimiento[]    findAll()
 * @method EquipoTrabajoVencimiento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EquipoTrabajoVencimientoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipoTrabajoVencimiento::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(EquipoTrabajoVencimiento $entity, bool $flush = true): void
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
    public function remove(EquipoTrabajoVencimiento $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return EquipoTrabajoVencimiento[] Returns an array of EquipoTrabajoVencimiento objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EquipoTrabajoVencimiento
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
