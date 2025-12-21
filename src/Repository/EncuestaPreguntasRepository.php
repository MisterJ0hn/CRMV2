<?php

namespace App\Repository;

use App\Entity\EncuestaPreguntas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EncuestaPreguntas>
 *
 * @method EncuestaPreguntas|null find($id, $lockMode = null, $lockVersion = null)
 * @method EncuestaPreguntas|null findOneBy(array $criteria, array $orderBy = null)
 * @method EncuestaPreguntas[]    findAll()
 * @method EncuestaPreguntas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EncuestaPreguntasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EncuestaPreguntas::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(EncuestaPreguntas $entity, bool $flush = true): void
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
    public function remove(EncuestaPreguntas $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findEncuestasByFechaRange(\DateTime $fechaInicio, \DateTime $fechaFin)
    {
        $query = $this->createQueryBuilder('e')
        ->join('e.encuesta', 'enc')
            ->where('enc.FechaCreacion BETWEEN :fechaInicio AND :fechaFin')
            ->setParameter('fechaInicio', $fechaInicio->format('Y-m-d'))
            ->setParameter('fechaFin', $fechaFin->format('Y-m-d')); // Uso de un parámetro para evitar inyección SQL
    
        return $query->getQuery()
            ->getResult();
    }
    // /**
    //  * @return EncuestaPreguntas[] Returns an array of EncuestaPreguntas objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EncuestaPreguntas
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
