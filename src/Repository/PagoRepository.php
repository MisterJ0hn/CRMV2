<?php

namespace App\Repository;

use App\Entity\Pago;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Pago|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pago|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pago[]    findAll()
 * @method Pago[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PagoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pago::class);
    }

    public function findByContrato($value, $esMulta=false)
    {
        $query=$this->createQueryBuilder('p')
        ->join('p.pagoCuotas','pc')
        ->join('pc.cuota','c')
        ->andWhere('c.contrato = :val');
        if($esMulta){
            $query->andWhere('c.isMulta = true');
        }

        $query->setParameter('val', $value)
        ->orderBy('p.id', 'ASC');

        return $query
            ->getQuery()
            ->getResult()
        ;
    }

    public function findUPByContrato($value)
    {
        return $this->createQueryBuilder('p')
            ->join('p.pagoCuotas','pc')
            ->join('pc.cuota','c')
            ->andWhere('c.contrato = :val')
            ->setParameter('val',$value)
            ->orderBy('p.fechaPago', 'Desc')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByPers($usuario=null,$empresa=null,$compania=null,$filtro=null,$otros=null,$usuarioRegistroFiltro=null){
        $query=$this->createQueryBuilder('p');
        $query->join('p.pagoCuotas','pc');
        $query->join('pc.cuota','c');
        $query->join('c.contrato','co');
        $query->join('co.agenda','a');
        $query->join('a.cuenta','cu');
        $query->join('p.usuarioRegistro','ur');
        if(!is_null($empresa)){

            $query->andWhere('cu.empresa = '.$empresa);
        }
        if(!is_null($usuario)){

            $query->andWhere('p.usuarioRegistro = '.$usuario);
        }
        if(!is_null($filtro)){
            $query->andWhere("(co.nombre like '%$filtro%' or co.rut like '%$filtro%')")
         ;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }

        if(!is_null($otros)){
            $query->andWhere($otros)
         ;

        }

        if(!is_null($usuarioRegistroFiltro)){
            $query->andWhere("ur.nombre like '%$usuarioRegistroFiltro%'");
        }


        return $query->getQuery()
            ->getResult()
        ;

    }
    
    public function findByPersCount($usuario=null,$empresa=null,$compania=null,$filtro=null,$otros=null,$usuarioRegistroFiltro=null){
        $query=$this->createQueryBuilder('p');
        $query->select(array('p','sum(pc.monto)'));
        $query->join('p.pagoCuotas','pc');
        $query->join('pc.cuota','c');
        $query->join('c.contrato','co');
        $query->join('co.agenda','a');
        $query->join('a.cuenta','cu');
        $query->join('p.usuarioRegistro','ur');
        if(!is_null($empresa)){

            $query->andWhere('cu.empresa = '.$empresa);
        }
        if(!is_null($usuario)){

            $query->andWhere('p.usuarioRegistro = '.$usuario);
        }
        if(!is_null($filtro)){
            $query->andWhere("(co.nombre like '%$filtro%' or co.rut like '%$filtro%')")
         ;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }

        if(!is_null($otros)){
            $query->andWhere($otros)
         ;

        }

        if(!is_null($usuarioRegistroFiltro)){
            $query->andWhere("ur.nombre like '%$usuarioRegistroFiltro%'");
        }


        return $query->getQuery()

            ->getOneOrNullResult()
        ;

    }


    public function findByTotalPorContrato($contrato_id){
        $query=$this->createQueryBuilder('p');
        $query->select(array('p','sum(pc.monto) as total'));
        $query->join('p.pagoCuotas','pc');
        $query->join('pc.cuota','c');
        
        $query->where('c.contrato = '.$contrato_id);
        $query->andWhere('(c.anular  is null or c.anular = false)');
        

        return $query->getQuery()
        
            ->getOneOrNullResult()
        ;

    }

     //Grafico Pagos
     public function findByPersCountPeriodoPagos($usuario=null,$empresa=null,$compania=null,$filtro=null,$otros=null)
     {
         $query=$this->createQueryBuilder('p');
         $query->select(array('p','sum(pc.monto) as valor'));
         $query->join('p.pagoCuotas','pc');
         $query->join('pc.cuota','c');
         $query->join('c.contrato','co');
         $query->join('co.agenda','a');
         $query->join('a.cuenta','cu');
         if(!is_null($empresa)){
             
             $query->andWhere('cu.empresa = '.$empresa);
         }
         if(!is_null($usuario)){
             
             $query->andWhere('p.usuarioRegistro = '.$usuario);
         }
         if(!is_null($filtro)){ 
             $query->andWhere("(co.nombre like '%$filtro%' or co.rut like '%$filtro%')")
          ;
 
         }
         if(!is_null($compania)){
             $query->andWhere('a.cuenta = '.$compania);
         }
         
         if(!is_null($otros)){ 
             $query->andWhere($otros)
          ;
 
         }
         
 
         return $query->getQuery()
         
             ->getOneOrNullResult()
         ;
 
     }
     public function findResumenTc($fechaInicio, $fechaFin)
    {
        $sql = "
            SELECT
                c.folio,
                cli.nombre AS contrato_nombre,
                rp.anio,
                rp.mes,
                rp.fecha_pago,
                rp.total_pagado_mes,
                COALESCE((
                    SELECT SUM(cu.monto)
                    FROM cuota cu
                    WHERE cu.contrato_id = rp.contrato_id
                      AND cu.monto > COALESCE(cu.pagado, 0)
                      AND cu.anular IS NULL
                ), 0) AS suma_cuotas_futuras,
                COALESCE((
                    SELECT SUM(cu.monto)
                    FROM cuota cu
                    WHERE cu.contrato_id = rp.contrato_id
                      AND cu.anular IS NULL
                ), 0) AS suma_cuotas_totales,
                u.nombre AS tramitador,
                materia.nombre AS materia,
                COALESCE((
                    SELECT cu2.fecha_pago
                    FROM cuota cu2
                    WHERE cu2.contrato_id = rp.contrato_id
                      AND COALESCE(cu2.pagado, 0) < cu2.monto
                      AND COALESCE(cu2.anular, 0) = 0
                    ORDER BY cu2.fecha_pago DESC
                    LIMIT 1
                ), '') AS fecha_vencimiento_ultima_cuota,
                COALESCE(
                        (
                        SELECT
                            COUNT(cu.id)
                        FROM
                            cuota cu
                        WHERE
                            cu.contrato_id = rp.contrato_id AND cu.anular IS NULL
                    ),
                    0
                    ) AS cuotas_totales,
                coalesce(( select max(fecha_creacion)  from contrato_anexo ca where ca.contrato_id= c.id and coalesce(ca.is_desiste,0) = 0),'') as fecha_ult_anexo,
                coalesce(( select max(folio)  from contrato_anexo ca where ca.contrato_id= c.id and coalesce(ca.is_desiste,0) = 0),'') as folio_anexo,
                coalesce(( select max(id)  from contrato_anexo ca where ca.contrato_id= c.id and coalesce(ca.is_desiste,0) = 0),'') as id_anexo,
                c.fecha_creacion as fecha_contrato
            FROM (
                SELECT
                    p.contrato_id,
                    YEAR(p.fecha_pago) AS anio,
                    MONTH(p.fecha_pago) AS mes,
                    MAX(p.fecha_pago) AS fecha_pago,
                    SUM(p.monto) AS total_pagado_mes
                FROM pago p
                WHERE p.fecha_pago BETWEEN :fechaInicio AND :fechaFin
                GROUP BY p.contrato_id, YEAR(p.fecha_pago), MONTH(p.fecha_pago)
            ) rp
            INNER JOIN contrato c ON rp.contrato_id = c.id
            LEFT JOIN cliente cli ON cli.id = c.cliente_id
            LEFT JOIN usuario u ON u.id = c.tramitador_id
            INNER JOIN agenda ON c.agenda_id = agenda.id
            INNER JOIN cuenta ON agenda.cuenta_id = cuenta.id
            INNER JOIN cuenta_materia ON cuenta.id = cuenta_materia.cuenta_id
            INNER JOIN materia ON cuenta_materia.materia_id = materia.id
            WHERE c.fecha_desiste IS NULL
            ORDER BY rp.contrato_id DESC, rp.anio DESC, rp.mes DESC
        ";

        $connection = $this->getEntityManager()->getConnection();

        return $connection->fetchAllAssociative($sql, [
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
        ]);
    }
    // /**
    //  * @return Pago[] Returns an array of Pago objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Pago
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
