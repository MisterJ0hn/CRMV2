<?php

namespace App\Repository;

use App\Entity\VwContratosVencidos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VwContratosVencidos>
 *
 * @method VwContratosVencidos|null find($id, $lockMode = null, $lockVersion = null)
 * @method VwContratosVencidos|null findOneBy(array $criteria, array $orderBy = null)
 * @method VwContratosVencidos[]    findAll()
 * @method VwContratosVencidos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VwContratosVencidosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VwContratosVencidos::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(VwContratosVencidos $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(VwContratosVencidos $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findByPers($usuario=null,$empresa=null,$compania=null,$filtro=null,$agendador=null, $otros=null, $deuda = false, $status=null)
    {
        $query=$this->createQueryBuilder('c');
        $query->join('c.contrato','co');
        $query->join('co.agenda','a');
        $query->join('a.cuenta','cu');

        //$query->andWhere('(DATEDIFF(now(), c.fechaPrimerPago)/30)<c.vigencia');
        if(!is_null($empresa)){
            
            $query->andWhere('cu.empresa = '.$empresa);
        }
        if(!is_null($usuario)){
            $query->andWhere('a.abogado = '.$usuario);
        }
        if(!is_null($agendador)){
            
            $query->andWhere('a.agendador = '.$agendador);
        }
        if(!is_null($filtro)){ 
            $query->andWhere("(co.nombre like '%$filtro%' or co.telefono like '%$filtro%' or co.email like '%$filtro%')")
         ;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        if(!is_null($status)){
            if($status==0){
                $query->andWhere('c.FechaEncuesta is not null');
            }
            if($status==1){
                $query->andWhere('c.FechaGestion is not null');
            }
        }
        return $query
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findIndexQuery($usuario=null, $empresa=null, $compania=null, $filtro=null, $agendador=null, $otros=null)
    {
        $query = $this->createQueryBuilder('c');
        $query->join('c.contrato','co');
        $query->join('co.agenda', 'a');
        $query->join('a.cuenta', 'cu');
        // Eager loading para evitar N+1 queries en la template
        $query->leftJoin('a.agendador', 'agendador');
        $query->leftJoin('a.abogado', 'abogado');
        $query->leftJoin('co.tramitador', 'tramitador');
        $query->addSelect('co, a, cu, agendador, abogado, tramitador');

        if (!is_null($empresa)) {
            $query->andWhere('cu.empresa = ' . $empresa);
        }
        if (!is_null($usuario)) {
            $query->andWhere('a.abogado = ' . $usuario);
        }
        if (!is_null($agendador)) {
            $query->andWhere('a.agendador = ' . $agendador);
        }
        if (!is_null($filtro)) {
            $query->setParameter('filtro', '%' . $filtro . '%');
            $query->andWhere("(co.nombre like :filtro or co.telefono like :filtro or co.email like :filtro)");
        }
        if (!is_null($compania)) {
            $query->andWhere('a.cuenta = ' . $compania);
        }
        if (!is_null($otros)) {
            $query->andWhere($otros);
        }

        return $query->orderBy('c.id', 'DESC')->getQuery();
    }

    /**
     * Enriquece los registros VwContratosVencidos con datos calculados desde vistas y tablas
     * Strategy: Batch query enrichment (una sola query por página vs N queries)
     *
     * @param array $items - Array de VwContratosVencidos objects
     * @param \Doctrine\DBAL\Connection $connection
     * @return void
     */
    public function enrichItems(array $items, \Doctrine\DBAL\Connection $connection): void
    {
        if (empty($items)) {
            return;
        }

        $idsList = implode(',', array_map(fn($item) => $item->getId(), $items));

        try {
            $sql = "
                SELECT
                    c.id,
                    coalesce(ca.fecha_creacion, c.fecha_creacion) AS fecha_creacion_vista,
                    coalesce(concat(ca.id, '-', c.folio, '-', ca.folio), c.folio) AS folio_vista,
                    CASE WHEN cpt.contrato_id IS NOT NULL THEN 1 ELSE 0 END AS pagado,
                    CASE
                        WHEN vm.folio IS NOT NULL THEN 1
                        WHEN vr.folio IS NOT NULL THEN 1
                        WHEN vu.folio IS NOT NULL THEN 1
                        ELSE 0
                    END AS vip,
                    vuolt.fecha_registro,
                    COALESCE(DATEDIFF(NOW(), vuolt.fecha_registro), 0) AS dias_ult_observacion,
                    CASE WHEN TIMESTAMPDIFF(MONTH, c.fecha_creacion, NOW()) <= c.vigencia THEN 1 ELSE 0 END AS vigencia_contrato,
                    CASE WHEN ca.id IS NOT NULL AND TIMESTAMPDIFF(MONTH, ca.fecha_creacion, NOW()) <= ca.vigencia THEN 1
                         WHEN ca.id IS NOT NULL THEN 0 ELSE NULL END AS vigencia_anexo
                FROM vw_contratos_vencidos vc
                INNER JOIN contrato c ON c.id = vc.contrato_id
                LEFT JOIN vw_contrato_pagado_total cpt ON cpt.contrato_id = c.id
                LEFT JOIN vw_vip_mayor_2mm vm ON vm.contrato_id = c.id
                LEFT JOIN vw_vip_referidos vr ON vr.contrato_id = c.id
                LEFT JOIN vw_vip_una_cuota vu ON vu.contrato_id = c.id
                LEFT JOIN vw_ult_observacion_linea_tiempo vuolt ON vuolt.contrato_id = c.id
                LEFT JOIN vista_contrato_anexo_max ca ON ca.contrato_id = c.id
                WHERE vc.id IN ($idsList)
            ";

            $rows = $connection->fetchAllAssociative($sql);
            $rowsById = array_column($rows, null, 'id');

            foreach ($items as $vwContrato) {
                $row = $rowsById[$vwContrato->getId()] ?? null;
                if ($row) {
                    $vwContrato->setDiasUltObservacion((int)$row['dias_ult_observacion']);
                    $vwContrato->setVigenciaContrato((int)$row['vigencia_contrato']);
                    $vwContrato->setVigenciaAnexo($row['vigencia_anexo'] !== null ? (int)$row['vigencia_anexo'] : null);
                    $vwContrato->setVip((int)$row['vip']);
                    $vwContrato->setFolio($row['folio_vista']);
                    if ($row['fecha_creacion_vista']) {
                        $vwContrato->setFechaCreacionVista(new \DateTime($row['fecha_creacion_vista']));
                    }
                }
            }
        } catch (\Exception $e) {
            // Silencio: continuar con valores por defecto si el enriquecimiento falla
        }
    }

    // /**
    //  * @return VwContratosVencidos[] Returns an array of VwContratosVencidos objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?VwContratosVencidos
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
