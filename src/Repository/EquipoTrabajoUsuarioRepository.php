<?php

namespace App\Repository;

use App\Entity\EquipoTrabajoUsuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EquipoTrabajoUsuario>
 *
 * @method EquipoTrabajoUsuario|null find($id, $lockMode = null, $lockVersion = null)
 * @method EquipoTrabajoUsuario|null findOneBy(array $criteria, array $orderBy = null)
 * @method EquipoTrabajoUsuario[]    findAll()
 * @method EquipoTrabajoUsuario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EquipoTrabajoUsuarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipoTrabajoUsuario::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(EquipoTrabajoUsuario $entity, bool $flush = true): void
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
    public function remove(EquipoTrabajoUsuario $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return EquipoTrabajoUsuario[] Returns an array of EquipoTrabajoUsuario objects
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
    public function findOneBySomeField($value): ?EquipoTrabajoUsuario
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
