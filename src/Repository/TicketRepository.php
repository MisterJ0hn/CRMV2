<?php

namespace App\Repository;

use App\Entity\Ticket;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ticket>
 *
 * @method Ticket|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ticket|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ticket[]    findAll()
 * @method Ticket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Ticket $entity, bool $flush = true): void
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
    public function remove(Ticket $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function ultimoTicket(): ?Ticket
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.folio','Desc')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByPers($usuario=null,int $empresa=null,string $status=null,int $origen=1,string $otros=null)
    {
        $query=$this->createQueryBuilder('t')
        ->join('t.estado','e')
        ->join('t.contrato','c')
        ->join('c.agenda','a')
        ->join('t.encargado','u');

        if(!is_null($status)){
            $query->andWhere('e.id in ('.$status.')');
        }
        if(!is_null($empresa)){
            $query->andWhere('t.empresa = '.$empresa);
        }
        switch($origen){
            case 1:
                if(!is_null($usuario)){
                    $query->andWhere('t.origen='.$usuario.' or t.encargado='.$usuario);
                }
                break;
            case 0:
                if(!is_null($usuario)){
                    $query->andWhere('t.encargado='.$usuario.' or t.origen='.$usuario);
                }
                break;
            case 3:
                if(!is_null($usuario)){
                    $query->andWhere('(t.encargado='.$usuario.' and u.usuarioTipo=3) or (t.encargado='.$usuario.' or t.origen='.$usuario.' or u.usuarioTipo!=3 )');
                }
                break;                        
            default:
                break;
        }
        
       
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        return $query->getQuery()
        ->getResult()
    ;


    }
    public function findByPersGroup( $usuario=null,int $empresa=null,string $status=null,string $filtro=null,int $origen=1,string $otros=null)
    {
        $query=$this->createQueryBuilder('t');
        $query->select(array('t','e','count(e.id) as valor'));
        $query->join('t.estado','e')
        ->join('t.contrato','c')
        ->join('c.agenda','a')
        ->join('t.encargado','u');

        if(!is_null($status)){
            $query->andWhere('e.id in ('.$status.')');
        }
        if(!is_null($empresa)){
            $query->andWhere('t.empresa = '.$empresa);
        }
        switch($origen){
            case 1:
                if(!is_null($usuario)){
                    $query->andWhere('t.origen='.$usuario.' or t.encargado='.$usuario);
                }
                break;
            case 0:
                if(!is_null($usuario)){
                    $query->andWhere('t.encargado='.$usuario.' or t.origen='.$usuario);
                }
                break;
            case 3:
                if(!is_null($usuario)){
                    $query->andWhere('(t.encargado='.$usuario.' and u.usuarioTipo=3) or ( t.encargado='.$usuario.' or t.origen='.$usuario.' or u.usuarioTipo!=3)');
                }
                break;            
            default:
                break;
        }
        
        if(!is_null($filtro)){ 
            $query->andWhere("(a.nombreCliente like '%$filtro%' or a.telefonoCliente like '%$filtro%' or a.emailCliente like '%$filtro%')")
         ;

        }
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        $query->addGroupBy('e.id');

        return $query->getQuery()
            ->getResult()
        ;


    }

    public function findByTicketGroup($usuario=null,int $empresa=null,string $status=null,int $origen=1,string $otros=null,$folio='')
    {
        $query=$this->createQueryBuilder('t');
        $query->select(array('t','count(t.id) as valor'))
        ->join('t.encargado','u')
        ->join('t.estado','e');
        
       
        if($folio!='' and !is_null($folio)){
            $query->join('t.contrato','c')
            ->andWhere("c.folio = $folio");
        }
        if(!is_null($status)){
            $query->andWhere('e.id in ('.$status.')');
        }
        if(!is_null($empresa)){
            $query->andWhere('t.empresa = '.$empresa);
        }
        switch($origen){
            case 1:
                if(!is_null($usuario)){
                    $query->andWhere('t.origen='.$usuario.' or t.encargado='.$usuario);
                }
                break;
            case 0:
                if(!is_null($usuario)){
                    $query->andWhere('t.encargado='.$usuario.' or t.origen='.$usuario);
                }
                break;
             case 3:
                if(!is_null($usuario)){
                    $query->andWhere('(t.encargado='.$usuario.' and u.usuarioTipo=3) or ( t.encargado='.$usuario.' or t.origen='.$usuario.' or u.usuarioTipo!=3)');
                }
                break;            
            default:
                break;
        }
        
        
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        $query->addGroupBy('u.id');

        return $query->getQuery()
            ->getResult()
        ;

    }

   
    public function findAbierto($folio)
    {
        $query=$this->createQueryBuilder('t')
        ->join('t.contrato','c')
        ->join('t.estado','e')
        ->where('c.folio='.$folio)
        ->andWhere('e.id in (1,2,3) ');
        
        return $query->getQuery()
                ->getResult()
        ;
    }

    public function findTicketsByFechaRange(DateTime $fechaInicio, DateTime $fechaFIn){
        return $this->createQueryBuilder('t')
            ->join('t.contrato','c')
            ->join('t.estado','e')
            ->andWhere('t.fechaNuevo BETWEEN :fechaInicio AND :fechaFin')
            ->setParameter('fechaInicio', $fechaInicio)
            ->setParameter('fechaFin', $fechaFIn)
            ->andWhere('t.estado = 2')
            ->orderBy('t.fechaNuevo', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    // /**
    //  * @return Ticket[] Returns an array of Ticket objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Ticket  
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
