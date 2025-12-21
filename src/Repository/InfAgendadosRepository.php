<?php

namespace App\Repository;

use App\Entity\InfAgendados;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InfAgendados|null find($id, $lockMode = null, $lockVersion = null)
 * @method InfAgendados|null findOneBy(array $criteria, array $orderBy = null)
 * @method InfAgendados[]    findAll()
 * @method InfAgendados[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InfAgendadosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InfAgendados::class);
    }

    
    public function removeBySesion(int $sesion): void
    {
        $q = $this->_em->createQuery('delete from App:InfAgendados tb where tb.usuario = '.$sesion);
       
        $numDeleted = $q->execute();
    }
    


    public function findByGroupPersonalizado(array $criterios=[],array $orderBy=[],array $groupBy=[]){
        $query=$this->createQueryBuilder('i')
        ->select(array('i','sum(i.agendados) as agendados','sum(i.prospectos) as prospectos'));

       
        foreach ($criterios as $campo => $valor ) {
        
            $query->andWhere('i.'.$campo.' = '.$valor);
        }
        
        foreach($groupBy as $valor){
            $query->addGroupBy('i.'.$valor);
        }

        foreach ($orderBy as $campo => $valor) {
            $query->addOrderBy('i.'.$campo,$valor);
        }

       
        $query->addGroupBy('i.abogado');
        return $query->getQuery()
                ->getResult();
    }


    // /**
    //  * @return InfAgendados[] Returns an array of InfAgendados objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?InfAgendados
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
