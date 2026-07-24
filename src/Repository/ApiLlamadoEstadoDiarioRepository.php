<?php

namespace App\Repository;

use App\Entity\ApiLlamadoEstadoDiario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ApiLlamadoEstadoDiarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiLlamadoEstadoDiario::class);
    }
}
