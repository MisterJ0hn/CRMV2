<?php

namespace App\Repository;

use App\Entity\ContratoAnexo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContratoAnexo|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContratoAnexo|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContratoAnexo[]    findAll()
 * @method ContratoAnexo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContratoAnexoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContratoAnexo::class);
    }

    public function findUltimosAnexosReporteQuery($empresa = null, $filtro = null, $folio = null, $dateInicio = null, $dateFin = null)
    {
        $sub = $this->createQueryBuilder('ca2')
            ->select('MAX(ca2.id)')
            ->where('ca2.estado IS NULL')
            ->andWhere('ca2.isDesiste = :isDesiste')
            ->groupBy('ca2.contrato');

        $qb = $this->createQueryBuilder('ca')
            ->join('ca.contrato', 'con')
            ->join('con.agenda', 'a')
            ->join('a.cuenta', 'cu')
            ->where('ca.estado IS NULL')
            ->andWhere('ca.isDesiste = :isDesiste')
            ->andWhere('ca.id IN (' . $sub->getDQL() . ')')
            ->setParameter('isDesiste', false);

        if (!is_null($empresa)) {
            $qb->andWhere('cu.empresa = :empresa')->setParameter('empresa', $empresa);
        }
        if (!is_null($filtro)) {
            $qb->andWhere("con.nombre LIKE :filtro")->setParameter('filtro', '%' . $filtro . '%');
        }
        if (!is_null($folio)) {
            $qb->andWhere('(con.folio = :folio OR a.id = :folio)')->setParameter('folio', $folio);
        }
        if (!is_null($dateInicio) && !is_null($dateFin)) {
            $qb->andWhere("ca.fechaCreacion BETWEEN :dateInicio AND :dateFin")
               ->setParameter('dateInicio', $dateInicio . ' 00:00:00')
               ->setParameter('dateFin', $dateFin . ' 23:59:59');
        }

        return $qb->orderBy('ca.id', 'DESC');
    }

    public function findByCaducados(int $contrato, $vigencia=24)
    {


        $query=$this->createQueryBuilder('c');
        
        $query->andWhere('(DATEDIFF(now(), c.fechaCreacion)/30)>'.$vigencia);
        
        $query->andWhere('c.contrato='.$contrato);
        
        return $query
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return ContratoAnexo[] Returns an array of ContratoAnexo objects
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
    public function findOneBySomeField($value): ?ContratoAnexo
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
