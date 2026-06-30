<?php

namespace App\Repository;

use App\Entity\Encuesta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Encuesta>
 *
 * @method Encuesta|null find($id, $lockMode = null, $lockVersion = null)
 * @method Encuesta|null findOneBy(array $criteria, array $orderBy = null)
 * @method Encuesta[]    findAll()
 * @method Encuesta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EncuestaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Encuesta::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Encuesta $entity, bool $flush = true): void
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
    public function remove(Encuesta $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findEncuestasByFechaRange(\DateTime $fechaInicio, \DateTime $fechaFin)
    {
        $query = $this->createQueryBuilder('e')
            ->join('e.contrato', 'c') // Asegurarse de que la encuesta esté relacionada con un contrato
            ->where("e.funcionRespuesta = 1")
            ->where("e.estado = 2") // Asegurarse de que el estado de la encuesta sea 2
            ->where('e.FechaCreacion BETWEEN :fechaInicio AND :fechaFin')
            ->setParameter('fechaInicio', $fechaInicio->format('Y-m-d'))
            ->setParameter('fechaFin', $fechaFin->format('Y-m-d')."  23:59:59") // Uso de un parámetro para evitar inyección SQL
            ->groupBy('e.contrato');
    
        return $query->getQuery()
            ->getResult();
    }
    public function findGestionesByFechaRange(\DateTime $fechaInicio, \DateTime $fechaFin): array
    {

        $query = $this->createQueryBuilder('e')
            ->where('e.FechaCreacion BETWEEN :fechaInicio AND :fechaFin')
            ->setParameter('fechaInicio', $fechaInicio->format('Y-m-d'))
            ->setParameter('fechaFin', $fechaFin->format('Y-m-d')."  23:59:59"); // Uso de un parámetro para evitar inyección SQL
            
        return $query->getQuery()
            ->getResult();

       /*     
        $qb = $this->createQueryBuilder('encuesta')
            ->select(
                'contrato.folio',
                'contrato.agenda AS agenda_id',
                'contrato.grupo AS grupo_id',
                'usuario.nombre AS usuario_nombre',
                'encuesta.fechaCreacion AS fecha_encuesta',
                'funcionEncuesta.nombre AS funcion_encuesta_nombre',
                'funcionRespuesta.nombre AS funcion_respuesta_nombre',
                'encuesta.observacion'
            )
            ->innerJoin('encuesta.contrato', 'contrato')
            ->innerJoin('encuesta.usuarioCreacion', 'usuario')
            ->innerJoin('encuesta.funcionEncuesta', 'funcionEncuesta')
            ->innerJoin('encuesta.funcionRespuesta', 'funcionRespuesta')
            ->where('encuesta.fechaCreacion BETWEEN :fechaInicio AND :fechaFin')
            ->setParameter('fechaInicio', $fechaInicio->format('Y-m-d'))
            ->setParameter('fechaFin', $fechaFin->format('Y-m-d'));

        return $qb->getQuery()->getResult();*/
    }
    // /**
    //  * @return Encuesta[] Returns an array of Encuesta objects
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
    public function findOneBySomeField($value): ?Encuesta
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
