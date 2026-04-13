<?php

namespace App\Repository;

use App\Entity\VwPrimeraCuotaDeContratoMasSuspendidos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VwPrimeraCuotaDeContratoMasSuspendidos|null find($id, $lockMode = null, $lockVersion = null)
 * @method VwPrimeraCuotaDeContratoMasSuspendidos|null findOneBy(array $criteria, array $orderBy = null)
 * @method VwPrimeraCuotaDeContratoMasSuspendidos[]    findAll()
 * @method VwPrimeraCuotaDeContratoMasSuspendidos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VwPrimeraCuotaDeContratoMasSuspendidosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VwPrimeraCuotaDeContratoMasSuspendidos::class);
    }
    
    public function findByFechas($fechaInicio,$fechaFin){
        $query=$this->createQueryBuilder('c')
        ->join("c.contrato","co")
           ->andWhere("date(co.fechaCreacion) >= '$fechaInicio'")
           ->andWhere("date(co.fechaCreacion) <= '$fechaFin'");

        return $query->getQuery()
            ->getResult();

    }

    public function findAbogadoPrimeraCuota($empresa=null, $compania=null, $dateInicio=null, $dateFin=null, $usuario=null)
    {
        $qb = $this->createQueryBuilder('vpc')
            ->select(
                'u.id as abogado_id',
                'SUM(CASE WHEN vpc.numero = 1 AND date(vpc.fechaPago) = date(vpc.fechaVencimiento) THEN 1 ELSE 0 END) as pagos_primera_cuota',
                'SUM(CASE WHEN vpc.numero = 1 AND con.cuotas = 1 AND vpc.pagado > 0 THEN 1 ELSE 0 END) as pagos_totales'
            )
            ->join('vpc.contrato', 'con')
            ->join('con.agenda', 'a')
            ->join('a.abogado', 'u')
            ->join('a.cuenta', 'cu')
            ->andWhere('a.abogado IS NOT NULL')
            ->andWhere("con.fechaCreacion BETWEEN '$dateInicio' AND '$dateFin 23:59:59'")
            ->groupBy('u.id');

        if (!is_null($empresa)) {
            $qb->andWhere('cu.empresa = ' . $empresa);
        }
        if (!is_null($compania)) {
            $qb->andWhere('a.cuenta = ' . $compania);
        }
        if (!is_null($usuario)) {
            $qb->andWhere('a.abogado = ' . $usuario);
        }

        return $qb->getQuery()->getArrayResult();
    }
}
