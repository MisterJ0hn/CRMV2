<?php

namespace App\Repository;

use App\Entity\MailPendienteEnvio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MailPendienteEnvio|null find($id, $lockMode = null, $lockVersion = null)
 * @method MailPendienteEnvio|null findOneBy(array $criteria, array $orderBy = null)
 * @method MailPendienteEnvio[]    findAll()
 * @method MailPendienteEnvio[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MailPendienteEnvioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailPendienteEnvio::class);
    }

    
}
