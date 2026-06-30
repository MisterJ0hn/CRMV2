<?php

namespace App\Repository;

use App\Entity\CausaObservacionArchivo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CausaObservacionArchivo>
 *
 * @method CausaObservacionArchivo|null find($id, $lockMode = null, $lockVersion = null)
 * @method CausaObservacionArchivo|null findOneBy(array $criteria, array $orderBy = null)
 * @method CausaObservacionArchivo[]    findAll()
 * @method CausaObservacionArchivo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CausaObservacionArchivoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CausaObservacionArchivo::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(CausaObservacionArchivo $entity, bool $flush = true): void
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
    public function remove(CausaObservacionArchivo $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return CausaObservacionArchivo[] Returns an array of CausaObservacionArchivo objects
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
    public function findOneBySomeField($value): ?CausaObservacionArchivo
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
