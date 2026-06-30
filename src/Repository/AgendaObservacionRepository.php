<?php

namespace App\Repository;

use App\Entity\AgendaObservacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AgendaObservacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgendaObservacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgendaObservacion[]    findAll()
 * @method AgendaObservacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaObservacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgendaObservacion::class);
    }

    public function findByAgendados(array $criterio,
                                    array $groupBy=null,
                                    array $joins=null, 
                                    $limit = null, 
                                    $offset = null,
                                    array $criterioAvanzado=null ){
        
        $query=$this->createQueryBuilder('a')
        ->select(array('a','count(a.abogadoDestino) as agendados'));
        
        foreach ($criterio as $campo => $valor) {
            $query->andWhere('a.'.$campo.' = '.$valor);
        }

        //$query->addGroupBy('p.id');
        //var_dump($groupBy);
        if($groupBy !== null){
            foreach ($groupBy as $group) {
                $query->addGroupBy('a.'.$group);
            }
        }
        

        foreach ($criterioAvanzado as $criavanzado) {
            $query->andWhere($criavanzado[0]." ".$criavanzado[1]." ".$criavanzado[2]);
        }

        return $query
            ->getQuery()
            ->getResult()
        ;
    }

    
    public function findByNocalifica(array $criterio,
                                
                                    $limit = null, 
                                    $offset = null,
                                    array $criterioAvanzado=null ){
        
        $query=$this->createQueryBuilder('a')
        ->select(array('a','sbt.nombre as nombre','sbt.color as color','count(a.id) as cantidad'))
        ->join('a.subStatus','sbt');
        foreach ($criterio as $campo => $valor) {
            $query->andWhere('a.'.$campo.' = '.$valor);
        }

        //$query->addGroupBy('p.id');
        //var_dump($groupBy);
    

        foreach ($criterioAvanzado as $criavanzado) {
            $query->andWhere($criavanzado[0]." ".$criavanzado[1]." ".$criavanzado[2]);
        }

        $query->addGroupBy('sbt.id');
        return $query
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByNocalificaSubStatusNulls(array $criterio,
                                
                                    $limit = null, 
                                    $offset = null,
                                    array $criterioAvanzado=null ){
        
        $query=$this->createQueryBuilder('a')
        ->select(array('a',"'S/S' as nombre","'000000' as color",'count(a.id) as cantidad'))

        ->andWhere("a.subStatus is null");
        
        foreach ($criterio as $campo => $valor) {
            $query->andWhere('a.'.$campo.' = '.$valor);
        }

        //$query->addGroupBy('p.id');
        //var_dump($groupBy);
    

        foreach ($criterioAvanzado as $criavanzado) {
            $query->andWhere($criavanzado[0]." ".$criavanzado[1]." ".$criavanzado[2]);
        }

        
        return $query
            ->getQuery()
            ->getResult()
        ;
    }

    

    // /**
    //  * @return AgendaObservacion[] Returns an array of AgendaObservacion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AgendaObservacion
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
