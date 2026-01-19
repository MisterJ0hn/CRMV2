<?php

namespace App\Repository;

use App\Entity\Configuracion;
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
}
