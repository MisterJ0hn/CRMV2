<?php

namespace App\Repository;

use App\Entity\DescargaTc;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DescargaTc>
 */
class DescargaTcRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DescargaTc::class);
    }

    public function findTodos(): array
    {
        return $this->createQueryBuilder('d')
            ->join('d.contrato', 'c')
            ->andWhere('c.fechaDesiste IS NULL')
            ->orderBy('d.fechaPago', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
