<?php

namespace App\Repository;

use App\Entity\VwContratoConsultor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VwContratoConsultor>
 *
 * @method VwContratoConsultor|null find($id, $lockMode = null, $lockVersion = null)
 * @method VwContratoConsultor|null findOneBy(array $criteria, array $orderBy = null)
 * @method VwContratoConsultor[]    findAll()
 * @method VwContratoConsultor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VwContratoConsultorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VwContratoConsultor::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(VwContratoConsultor $entity, bool $flush = true): void
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
    public function remove(VwContratoConsultor $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

     public function findByPers($usuario=null,$empresa=null,$compania=null,$filtro=null,$agendador=null, $otros=null, $deuda = false)
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
        
        return $query->orderBy('c.id', 'DESC');
    }

    /**
     * @param int|int[] $vencimientoIds  ID o array de IDs de vencimiento a incluir
     */
    public function findMorososByVencimiento($vencimientoIds, $usuario=null, $empresa=null, $compania=null, $filtro=null, $otros=null)
    {
        $vencimientoIds = (array) $vencimientoIds;

        // Step 1: get contrato IDs that have cuotas pendientes with ANY of the given vencimientos
        $cpQuery = $this->getEntityManager()->createQueryBuilder();
        $cpQuery->select('IDENTITY(cp.contrato) as contrato_id')
            ->from(\App\Entity\VwCuotaPendiente::class, 'cp')
            ->andWhere('cp.vencimiento IN (:vencimientoIds)')
            ->setParameter('vencimientoIds', $vencimientoIds)
            ->distinct();
        $rows = $cpQuery->getQuery()->getScalarResult();
        $contratoIds = array_column($rows, 'contrato_id');

        if (empty($contratoIds)) {
            return [];
        }

        // Step 2: query VwContratoConsultor for those contracts
        $query = $this->createQueryBuilder('c');
        $query->join('c.contrato', 'co');
        $query->join('co.agenda', 'a');
        $query->join('a.cuenta', 'cu');
        $query->andWhere('co.id IN (:contratoIds)')->setParameter('contratoIds', $contratoIds);
        $query->andWhere('a.status in (7,14)');
        $query->andWhere('co.isFinalizado = false or co.isFinalizado is null');

        if (!is_null($empresa)) {
            $query->andWhere('cu.empresa = ' . $empresa);
        }
        if (!is_null($usuario)) {
            $query->andWhere('a.abogado = ' . $usuario);
        }
        if (!is_null($filtro)) {
            $query->andWhere("(co.nombre like '%$filtro%' or co.telefono like '%$filtro%' or co.email like '%$filtro%')");
        }
        if (!is_null($compania)) {
            $query->andWhere('a.cuenta = ' . $compania);
        }
        if (!is_null($otros)) {
            $query->andWhere($otros);
        }

        return $query->orderBy('c.id', 'DESC');
    }

    // /**
    //  * @return VwContratoConsultor[] Returns an array of VwContratoConsultor objects
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
    public function findOneBySomeField($value): ?VwContratoConsultor
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
