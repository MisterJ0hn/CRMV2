<?php

namespace App\Repository;

use App\Entity\Mensaje;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Mensaje|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mensaje|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mensaje[]    findAll()
 * @method Mensaje[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MensajeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mensaje::class);
    }

    public function findByVencidas(int $usuario,string $fechaVencimiento)
    {
        $query=$this->createQueryBuilder('m');
        $query->andWhere('m.usuarioDestino = '.$usuario)
        ->andWhere("m.fechaAviso<= '$fechaVencimiento' ");
         $query->andWhere('m.mensajeTipo = 2');
        $query->andWhere('m.leido = false')
        ->orderBy('m.fechaAviso','Asc');
        
        return $query->getQuery()
            ->getResult();
    }
    /**
    * @return Mensaje[] Returns an array of Mensajes objects
    */
    public function findByVencidasCount(int $usuario,string $fechaVencimiento)
    {
        $query=$this->createQueryBuilder('m')
        ->select(array('count(m.id) as pendientes'));

        $query->andWhere('m.usuarioDestino = '.$usuario)
        ->andWhere("m.fechaAviso<= '$fechaVencimiento' ");

        $query->andWhere('m.mensajeTipo = 2');
        $query->andWhere('m.leido = false');

        return $query->getQuery()
        ->getOneOrNullResult();
    }

    public function findConFiltro(int $usuario=null, String $fecha_inicio,String $fecha_fin, int $prioridad=null, int $leido=null, int $tipo=null, int $tipoUsuario=null)
    {
       
        $query=$this->createQueryBuilder('m')        
        ->andWhere("m.fechaAviso between '$fecha_inicio' and '$fecha_fin'");

        if(!is_null($usuario)){
            $query->andWhere('m.usuarioDestino = '.$usuario);
        }

        
        if(!is_null($prioridad)){
            $query->andWhere('m.mensajePrioridad = '.$prioridad);
        }
        
        if(!is_null($leido)){
            if($leido==1){
                $query->andWhere('m.leido = false');
            }else{
                $query->andWhere('m.leido = true');
            }
                        
        }
        if($tipoUsuario!=null){
            $query->join("m.usuarioDestino","u")
            ->andWhere("u.usuarioTipo=$tipoUsuario");
        }
       
        $query->andWhere('m.mensajeTipo = 2');
        
       
        
        return $query->getQuery();
    }
    public function findConFiltroCalendario(int $usuarioDestino=0, int $tipoAsignacion=0,int $tipoUsuario =null){
        $query=$this->createQueryBuilder('m');
        

        $query->andWhere('m.mensajeTipo = 2');
        if($usuarioDestino!=0){
            $query->andWhere('m.usuarioDestino = '.$usuarioDestino);
        }

        if($tipoAsignacion!=0){
            if($tipoAsignacion==1){
                $query->andWhere("m.usuarioDestino = m.usuarioRegistro");
            }else{
                $query->andWhere("m.usuarioDestino != m.usuarioRegistro");
            
            }            
        }

        if($tipoUsuario!=null){
            $query->join("m.usuarioDestino","u")
            ->andWhere("u.usuarioTipo=$tipoUsuario");
        }
        $query->andWhere('m.leido = false');

        
        return $query->getQuery()
        ->getResult();
    }
    // /**
    //  * @return Mensaje[] Returns an array of Mensaje objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Mensaje
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
