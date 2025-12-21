<?php

namespace App\Repository;

use App\Entity\VwResumenCausas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method VwResumenCausas|null find($id, $lockMode = null, $lockVersion = null)
 * @method VwResumenCausas|null findOneBy(array $criteria, array $orderBy = null)
 * @method VwResumenCausas[]    findAll()
 * @method VwResumenCausas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VwResumenCausasRepository extends ServiceEntityRepository
{
     public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VwResumenCausas::class);
    }

    /**
     * @return VwResumenCausas[] Returns an array of VwResumenCausas objects
     */
    public function findGroupByCuenta($cuentaId)
    {
        $query= $this->createQueryBuilder('v')
            ->select('SUM(v.causasActivas) as causasActivas, 
                     SUM(v.causasAlDia) as causasAlDia, 
                     SUM(v.clientesActivos) as clientesActivos, 
                     SUM(v.ClientesAlDia) as clientesAlDia, 
                     SUM(v.clientesMorosos) as clientesMorosos, 
                     SUM(v.clientesActivosVIP) as clientesActivosVIP, 
                     SUM(v.clientesAlDiaVIP) as clientesAlDiaVIP, 
                     SUM(v.causasActivasConRol) as causasActivasConRol, 
                     SUM(v.causasActivasSinRol) as causasActivasSinRol, 
                     SUM(v.causasActivasFinalizadas) as causasActivasFinalizadas')
            ->where('v.cuentaId = :cuentaId')
            ->setParameter('cuentaId', $cuentaId)
            ->groupBy('v.cuentaId')

            ->getQuery()
            ->getResult();
    }
    public function findGroupByTramitador($cuentaId, $tramitadorId)
    {
        $query= $this->createQueryBuilder('v')
            ->select('SUM(v.causasActivas) as causasActivas, 
                     SUM(v.causasAlDia) as causasAlDia, 
                     SUM(v.clientesActivos) as clientesActivos, 
                     SUM(v.ClientesAlDia) as clientesAlDia, 
                     SUM(v.clientesMorosos) as clientesMorosos, 
                     SUM(v.clientesActivosVIP) as clientesActivosVIP, 
                     SUM(v.clientesAlDiaVIP) as clientesAlDiaVIP, 
                     SUM(v.causasActivasConRol) as causasActivasConRol, 
                     SUM(v.causasActivasSinRol) as causasActivasSinRol, 
                     SUM(v.causasActivasFinalizadas) as causasActivasFinalizadas')
            ->where('v.cuentaId = :cuentaId')
            ->where('v.tramitadorId = :tramitadorId')
            ->setParameter('cuentaId', $cuentaId)
             ->setParameter('tramitadorId', $tramitadorId)
            ->groupBy('v.tramitador')
            ->getQuery()
            ->getResult();
    }
    public function findGroupByTodo($cuentaId=null)
    {
        $query= $this->createQueryBuilder('v')
            ->select('SUM(v.causasActivas) as causasActivas, 
                     SUM(v.causasAlDia) as causasAlDia, 
                     SUM(v.clientesActivos) as clientesActivos, 
                     SUM(v.ClientesAlDia) as clientesAlDia, 
                     SUM(v.clientesMorosos) as clientesMorosos, 
                     SUM(v.clientesActivosVIP) as clientesActivosVIP, 
                     SUM(v.clientesAlDiaVIP) as clientesAlDiaVIP, 
                     SUM(v.causasActivasConRol) as causasActivasConRol, 
                     SUM(v.causasActivasSinRol) as causasActivasSinRol, 
                     SUM(v.causasActivasFinalizadas) as causasActivasFinalizadas');
        if(!is_null($cuentaId)){
            $query->where('v.cuentaId in ('.$cuentaId.')');    
        
        }
        
        


        return $query->getQuery()
        ->getResult();
    }

}
