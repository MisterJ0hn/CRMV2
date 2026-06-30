<?php

namespace App\Repository;

use App\Entity\Colaborador;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Colaborador|null find($id, $lockMode = null, $lockVersion = null)
 * @method Colaborador|null findOneBy(array $criteria, array $orderBy = null)
 * @method Colaborador[]    findAll()
 * @method Colaborador[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ColaboradorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Colaborador::class);
    }

    public function findByTipo(int $tipoId): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.usuario', 'u')
            ->join('u.usuarioTipo', 'ut')
            ->andWhere('ut.id = :tipoId')
            ->setParameter('tipoId', $tipoId)
            ->orderBy('u.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
