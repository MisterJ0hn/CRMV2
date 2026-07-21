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

    public function findConFiltro(?int $jurisdiccion = null, ?string $fecha = null, ?string $rut = null, string $tab = 'no-leidos')
    {
        $query = $this->createQueryBuilder('ed')
            ->addSelect('origen', 'j')
            ->join('ed.estadoDiarioOrigen', 'origen')
            ->leftJoin('ed.jurisdiccion', 'j')
            ->orderBy('origen.fecha', 'DESC')
            ->addOrderBy('ed.id', 'DESC');

        if ($tab === 'resuelto') {
            $query->andWhere('ed.leido = true');
        } elseif ($tab === 'pendiente') {
            $query->andWhere('ed.leido = false')
                ->andWhere('ed.pendiente = true');
        } else {
            $query->andWhere('ed.leido = false')
                ->andWhere('ed.pendiente = false');
        }

        if ($jurisdiccion) {
            $query->andWhere('j.id = :jurisdiccion')
                ->setParameter('jurisdiccion', $jurisdiccion);
        }

        if ($fecha) {
            $query->andWhere('origen.fecha = :fecha')
                ->setParameter('fecha', new \DateTime($fecha));
        }

        if ($rut) {
            $query->andWhere('origen.rut LIKE :rut')
                ->setParameter('rut', '%' . $rut . '%');
        }

        return $query;
    }

    public function contarPorFiltro(?int $jurisdiccion = null, ?string $fecha = null, ?string $rut = null, string $tab = 'no-leidos'): int
    {
        $query = $this->findConFiltro($jurisdiccion, $fecha, $rut, $tab);

        return (int) $query->select('COUNT(ed.id)')->getQuery()->getSingleScalarResult();
    }
}
