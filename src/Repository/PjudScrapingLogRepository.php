<?php

namespace App\Repository;

use App\Entity\PjudScrapingLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PjudScrapingLog>
 *
 * @method PjudScrapingLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method PjudScrapingLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method PjudScrapingLog[]    findAll()
 * @method PjudScrapingLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PjudScrapingLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PjudScrapingLog::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(PjudScrapingLog $entity, bool $flush = true): void
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
    public function remove(PjudScrapingLog $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

   
}
