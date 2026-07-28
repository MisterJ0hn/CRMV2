<?php

namespace App\Repository;

use App\Entity\ModuloPer;
use App\Entity\PrivilegioTipousuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ModuloPer|null find($id, $lockMode = null, $lockVersion = null)
 * @method ModuloPer|null findOneBy(array $criteria, array $orderBy = null)
 * @method ModuloPer[]    findAll()
 * @method ModuloPer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModuloPerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModuloPer::class);
    }

    public function findOneByName($value, $empresa): ?ModuloPer
    {
        return $this->createQueryBuilder('m')
            ->join('m.modulo','mo')
            ->andWhere('mo.nombre = :val')
            ->andWhere('m.empresa = :val2')
            ->setParameter('val', $value)
            ->setParameter('val2', $empresa)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    /**
     * Módulos de la empresa que aún NO tienen privilegio asignado para el tipo de usuario.
     *
     * @return ModuloPer[]
     */
    public function findDisponiblesPorTipoUsuario($empresa, $tipoUsuario): array
    {
        $qb = $this->createQueryBuilder('m');

        $sub = $this->getEntityManager()->createQueryBuilder()
            ->select('IDENTITY(p.moduloPer)')
            ->from(PrivilegioTipousuario::class, 'p')
            ->andWhere('p.tipousuario = :tipoUsuario');

        return $qb
            ->andWhere('m.empresa = :empresa')
            ->andWhere($qb->expr()->notIn('m.id', $sub->getDQL()))
            ->setParameter('empresa', $empresa)
            ->setParameter('tipoUsuario', $tipoUsuario)
            ->orderBy('m.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return ModuloPer[] Returns an array of ModuloPer objects
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
    public function findOneBySomeField($value): ?ModuloPer
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
