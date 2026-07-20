<?php

namespace App\Repository;

use App\Entity\EstadoDiarioAgenda;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EstadoDiarioAgenda|null find($id, $lockMode = null, $lockVersion = null)
 * @method EstadoDiarioAgenda|null findOneBy(array $criteria, array $orderBy = null)
 * @method EstadoDiarioAgenda[]    findAll()
 * @method EstadoDiarioAgenda[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EstadoDiarioAgendaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstadoDiarioAgenda::class);
    }

    /**
     * @return EstadoDiarioAgenda[]
     */
    public function findPendientesEnvio(): array
    {
        return $this->createQueryBuilder('a')
            ->addSelect('u', 'ed')
            ->join('a.usuarioRegistro', 'u')
            ->join('a.estadoDiario', 'ed')
            ->andWhere('a.enviado = false')
            ->andWhere('a.fechaHora <= now()')
          
            ->orderBy('a.fechaHora', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
