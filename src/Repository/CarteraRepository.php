<?php

namespace App\Repository;

use App\Entity\Cartera;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cartera|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cartera|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cartera[]    findAll()
 * @method Cartera[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarteraRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cartera::class);
    }

    public function findHabilitados()
    {
        $query=$this->createQueryBuilder('c')
        ->leftJoin('c.usuarioCarteras','uc')
        ->andWhere('uc.id is null')
        ->andWhere('c.estado = true')
        ->orderBy('c.orden','ASC');
    
    return $query->getQuery()
        ->getResult()
    ;

    }

    public function findPrimerDisponible($materia, $cuenta_id = null): ?Cartera
    {
        $query=$this->createQueryBuilder('c');

        if($cuenta_id!=null){
            $query->join("c.usuarioCarteras","uc")
            ->join("uc.usuario","u")
            ->join("u.usuarioCuentas","ucuenta")
            ->join("ucuenta.cuenta","cue")
            ->andWhere("cue.id=".$cuenta_id);

        }
        $query->andWhere('c.materia='.$materia)
        ->andWhere('c.utilizado = false')
        ->andWhere('c.asignado = true')
        ->andWhere('c.estado = true')
        ->orderBy('c.orden','ASC');
    
    return $query->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult()
    ;



    }

    public function findGroupByMateria(string $materias){
        $query=$this->createQueryBuilder('c')
        ->andWhere('c.materia in ('.$materias.')')
        ->groupBy('c.materia');

        return $query->getQuery()
        ->getResult();
    }


    public function findCarterasDisponibles($materia, $cuenta_id = null)
    {
        $query=$this->createQueryBuilder('c');

        if($cuenta_id != null){
            $query->join("c.usuarioCarteras","uc")
            ->join("uc.usuario","u")
            ->join("u.usuarioCuentas","ucuenta")
            ->join("ucuenta.cuenta","cue")
            ->andWhere("cue.id=".$cuenta_id);


        }
        $query->andWhere('c.materia='.$materia)
        ->andWhere('c.estado = true')
        ->orderBy('c.orden','ASC');
    
    return $query->getQuery()
                ->getResult()
    ;



    }
    // /**
    //  * @return Cartera[] Returns an array of Cartera objects
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
    public function findOneBySomeField($value): ?Cartera
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
