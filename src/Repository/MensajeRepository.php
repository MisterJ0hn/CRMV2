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

        $query->andWhere('m.leido = false');

        return $query->getQuery()
        ->getOneOrNullResult();
    }

    public function findConFitro(int $usuario, int $tipo_mensaje, String $fecha_inicio,String $fecha_fin){
        $query=$this->createQueryBuilder('m')
        ->andWhere('m.usuarioDestino = '.$usuario)
        ->andWhere("m.fechaAviso between '$$fecha_inicio' and '$fecha_fin'");

        if($tipo_mensaje != 0){
            $query->andWhere('m.mensajeTipo = '.$tipo_mensaje);
        }
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
