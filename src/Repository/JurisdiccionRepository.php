<?php

namespace App\Repository;

use App\Entity\Jurisdiccion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Jurisdiccion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Jurisdiccion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Jurisdiccion[]    findAll()
 * @method Jurisdiccion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JurisdiccionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Jurisdiccion::class);
    }

    public function findOrCreateByNombre(string $nombre): Jurisdiccion
    {
        $nombre = trim($nombre);
        $jurisdiccion = $this->findOneBy(['nombre' => $nombre]);

        if (!$jurisdiccion) {
            $jurisdiccion = new Jurisdiccion();
            $jurisdiccion->setNombre($nombre);
            $this->_em->persist($jurisdiccion);
            $this->_em->flush();
        }

        return $jurisdiccion;
    }
}
