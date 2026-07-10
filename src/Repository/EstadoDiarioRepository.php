<?php

namespace App\Repository;

use App\Entity\EstadoDiario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EstadoDiario|null find($id, $lockMode = null, $lockVersion = null)
 * @method EstadoDiario|null findOneBy(array $criteria, array $orderBy = null)
 * @method EstadoDiario[]    findAll()
 * @method EstadoDiario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EstadoDiarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstadoDiario::class);
    }
}
