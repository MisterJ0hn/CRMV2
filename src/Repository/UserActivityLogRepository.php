<?php

namespace App\Repository;

use App\Entity\UserActivityLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserActivityLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserActivityLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserActivityLog[]    findAll()
 * @method UserActivityLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserActivityLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserActivityLog::class);
    }

    public function findByFiltros(?int $usuarioId, ?string $desde, ?string $hasta, ?string $modulo): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.usuario', 'u')
            ->addSelect('u')
            ->orderBy('l.fechaRegistro', 'DESC');

        if ($usuarioId) {
            $qb->andWhere('l.usuario = :uid')
               ->setParameter('uid', $usuarioId);
        }

        if ($desde) {
            $qb->andWhere('l.fechaRegistro >= :desde')
               ->setParameter('desde', new \DateTime($desde . ' 00:00:00'));
        }

        if ($hasta) {
            $qb->andWhere('l.fechaRegistro <= :hasta')
               ->setParameter('hasta', new \DateTime($hasta . ' 23:59:59'));
        }

        if ($modulo) {
            $qb->andWhere('l.controlador LIKE :modulo')
               ->setParameter('modulo', '%' . $modulo . '%');
        }

        return $qb->setMaxResults(500)->getQuery()->getResult();
    }

    public function findUltimossinGeo($usuario, int $limit = 5): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.usuario = :u')
            ->andWhere('l.latitud IS NULL')
            ->setParameter('u', $usuario)
            ->orderBy('l.fechaRegistro', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getModulosDistintos(): array
    {
        $rows = $this->createQueryBuilder('l')
            ->select('DISTINCT l.controlador')
            ->where('l.controlador IS NOT NULL')
            ->orderBy('l.controlador', 'ASC')
            ->getQuery()
            ->getScalarResult();

        $modulos = [];
        foreach ($rows as $row) {
            $ctrl = $row['controlador'];
            // Extraer solo el nombre del controlador sin namespace ni método
            if (preg_match('#\\\\(\w+Controller)::#', $ctrl, $m)) {
                $nombre = str_replace('Controller', '', $m[1]);
                if (!in_array($nombre, $modulos)) {
                    $modulos[] = $nombre;
                }
            }
        }
        sort($modulos);
        return $modulos;
    }
}
