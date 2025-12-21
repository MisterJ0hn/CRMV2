<?php

namespace App\Repository;

use App\Entity\EstrategiaJuridicaCuaderno;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EstrategiaJuridicaCuaderno>
 *
 * @method EstrategiaJuridicaCuaderno|null find($id, $lockMode = null, $lockVersion = null)
 * @method EstrategiaJuridicaCuaderno|null findOneBy(array $criteria, array $orderBy = null)
 * @method EstrategiaJuridicaCuaderno[]    findAll()
 * @method EstrategiaJuridicaCuaderno[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EstrategiaJuridicaCuadernoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstrategiaJuridicaCuaderno::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(EstrategiaJuridicaCuaderno $entity, bool $flush = true): void
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
    public function remove(EstrategiaJuridicaCuaderno $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return EstrategiaJuridicaCuaderno[] Returns an array of EstrategiaJuridicaCuaderno objects
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
    public function findOneBySomeField($value): ?EstrategiaJuridicaCuaderno
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
