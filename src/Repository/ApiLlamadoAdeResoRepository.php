<?php

namespace App\Repository;

use App\Entity\ApiLlamadoAdereso;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ApiLlamadoAdeResoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiLlamadoAdereso::class);
    }
}
