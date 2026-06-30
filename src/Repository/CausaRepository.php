<?php

namespace App\Repository;

use App\Entity\Causa;
use App\Entity\Configuracion;
use App\Entity\Vencimiento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Causa>
 *
 * @method Causa|null find($id, $lockMode = null, $lockVersion = null)
 * @method Causa|null findOneBy(array $criteria, array $orderBy = null)
 * @method Causa[]    findAll()
 * @method Causa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CausaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Causa::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Causa $entity, bool $flush = true): void
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
    public function remove(Causa $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
    
    /**
     * Query DBAL para el listado Mis Clientes.
     * Retorna un DBAL QueryBuilder compatible con KnpPaginator.
     * Todas las columnas calculadas (materia, servicio, lt, pago, actividad) son ordenables.
     */
    public function countMisClientes(
                                        $empresa        = null,
                                        $materia        = null,
                                        $filtro         = null,
                                        $usuario        = null,
                                        $otros          = null,
                                        $atrasado       = null,
                                        $tramitador     = null,
                                        $tipoCliente    = null,
                                        $prioridad      = null,
                                        $primeraCuota   = null,
                                        $fechaInicio    = null,
                                        $fechaFin       = null,
                                        $servicio       = null
                                    ) 
    {
       
        $configuracion = $this->getEntityManager()->getRepository(Configuracion::class)->find(1);
        $query = $this->createQueryBuilder('c');
        $query->select('count(c.id) as total');
        $query->join('c.agenda', 'a');
        $query->join('a.contrato', 'co');
        $query->join('a.cuenta', 'cu')
        ->andWhere('co.fechaDesiste is null');


        $query->andWhere('c.estado = 1 and COALESCE(c.causaFinalizada, 0) = 0');

       
        

        $query->andWhere(" ((co.fechaCreacion between  date_sub(current_timestamp(),co.vigencia,'month') and current_timestamp()) and 
                        co.fechaCreacion between '$fechaInicio' and '$fechaFin 23:59:59' and co.vigenciaUltAnexo is null)
                        or ((co.vigenciaUltAnexo is not null and  co.fechaCreacionUltAnexo between  date_sub(current_timestamp(),co.vigenciaUltAnexo,'month') and current_timestamp()) and 
                        co.fechaCreacionUltAnexo between '$fechaInicio' and '$fechaFin 23:59:59')");


        if(!is_null($atrasado)){
            $vencimiento = $this->getEntityManager()->getRepository(Vencimiento::class)->find(1);
            if($atrasado==1){
                
                $query->andWhere("  ((co.diasMorosidad >= ".$vencimiento->getValMin()." and c.esVip=0 and co.diasMorosidad <= ".$configuracion->getMorosidadTramitadorMax().") or (co.diasMorosidad >= ".$configuracion->getDiasMorisidadVip()." and co.esVip=1)) ");
            }else{
                 $query->andWhere(" (co.diasMorosidad < ".$vencimiento->getValMin()." and co.esVip=0) or (co.diasMorosidad < ".$configuracion->getDiasMorisidadVip()." and co.esVip=1)");             
            
            }

        }else{
            $query->andWhere(" co.diasMorosidad <= ".$configuracion->getMorosidadTramitadorMax());
        }

        if(!is_null($tipoCliente)){
            if($tipoCliente==1){
                $query->andWhere(" co.esVip = 1");
            }else if($tipoCliente==2){
                $query->andWhere(" co.esVip = 0");

            }
        }
        if (!is_null($empresa)) {
            $query->andWhere('cu.empresa = ' . $empresa);
        }
        if (!is_null($usuario)) {
            $query->andWhere('a.abogado = ' . $usuario);
        }
        if (!is_null($tramitador)) {
            $query->andWhere('co.tramitador = ' . $tramitador);
        }
        if (!is_null($filtro)) {
            $query->andWhere("(co.nombre LIKE :filtro OR REPLACE(REPLACE(co.rut, '.', ''), '-', '') = REPLACE(REPLACE(:filtroRut, '.', ''), '-', '') OR co.folio LIKE :filtro)")
               ->setParameter('filtro', '%' . $filtro . '%')
               ->setParameter('filtroRut', $filtro);
        }
        if (!is_null($materia)) {
            $query->andWhere("EXISTS (
                SELECT 1
                FROM App\Entity\CuentaMateria cm
                WHERE cm.cuenta = a.cuenta
                  AND cm.materia = $materia
            )");
        }
        if (!is_null($otros)) {
            $query->andWhere($otros);
        }
        
     
            
        if(!is_null($prioridad)){
            $query->andWhere(" exists (
                    SELECT c9
                    FROM App\Entity\Mensaje m2
                    Join m2.mensajePrioridad mp1
                    JOIN m2.causa c9
                    WHERE 
                        m2.fechaAviso in (
                            SELECT MIN(m3.fechaAviso)
                            FROM App\Entity\Mensaje m3
                            JOIN m3.causa c10
                            WHERE 
                                m3.fechaAviso >= now()
                                AND c10.agenda = c.agenda
                            group by c10
                        )
                        AND c9.agenda = co.agenda
                        and mp1.id=$prioridad
                ) ");
        }
         if(!is_null($primeraCuota)){
            if($primeraCuota==2){
                $query->andWhere("exists ( select c3 from App\Entity\Cuota c3 where c3.contrato=co and (c3.anular is null  OR c3.anular=0) and (c3.monto>=(c3.pagado+".$configuracion->getDeudaminima().") or c3.pagado is null) and c3.numero=1 )");
            }else{
                $query->andWhere("not exists ( select c3 from App\Entity\Cuota c3 where c3.contrato=co and (c3.anular is null  OR c3.anular=0) and (c3.monto>=(c3.pagado+".$configuracion->getDeudaminima().") or c3.pagado is null) and c3.numero=1 )");
            }
        }
        if (!is_null($servicio)) {
            $query->andWhere("exists (select ca1 from App\Entity\Causa ca1 
                                join App\Entity\MateriaEstrategia me1 
                                join App\Entity\EstrategiaJuridica ej1 
                                where me1=ca1.materiaEstrategia and  ej1 = me1.estrategiaJuridica and ca1.agenda=co.agenda and ej1.id=$servicio and (ca1.causaFinalizada is null or ca1.causaFinalizada=0))");
        }
        return $query
                ->getQuery()
                ->getResult();
    }

    // /**
    //  * @return Causa[] Returns an array of Causa objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Causa
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
