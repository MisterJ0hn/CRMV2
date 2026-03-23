<?php

namespace App\Repository;

use App\Entity\TicketTipoSolicitud;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TicketTipoSolicitud>
 *
 * @method TicketTipoSolicitud|null find($id, $lockMode = null, $lockVersion = null)
 * @method TicketTipoSolicitud|null findOneBy(array $criteria, array $orderBy = null)
 * @method TicketTipoSolicitud[]    findAll()
 * @method TicketTipoSolicitud[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TicketTipoSolicitudRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TicketTipoSolicitud::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(TicketTipoSolicitud $entity, bool $flush = true): void
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
    public function remove(TicketTipoSolicitud $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return TicketTipoSolicitud[] Returns an array of TicketTipoSolicitud objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TicketTipoSolicitud
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
