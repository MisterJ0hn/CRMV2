<?php

namespace App\Repository;

use App\Entity\Configuracion;
use App\Entity\VwPrimeraCuotaDeContrato;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VwPrimeraCuotaDeContrato|null find($id, $lockMode = null, $lockVersion = null)
 * @method VwPrimeraCuotaDeContrato|null findOneBy(array $criteria, array $orderBy = null)
 * @method VwPrimeraCuotaDeContrato[]    findAll()
 * @method VwPrimeraCuotaDeContrato[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VwPrimeraCuotaDeContratoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VwPrimeraCuotaDeContrato::class);
    }
    
    public function findByFechas($fechaInicio,$fechaFin){
        $query=$this->createQueryBuilder('c')
           ->join('c.contrato','co')
           ->andWhere("date(co.fechaCreacion) >= '$fechaInicio'")
           ->andWhere("date(co.fechaCreacion) <= '$fechaFin'");

        return $query->getQuery()
            ->getResult();

    }
}
