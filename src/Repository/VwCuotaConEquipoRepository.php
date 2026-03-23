<?php

namespace App\Repository;

use App\Entity\Configuracion;
use App\Entity\VwCuotaConEquipo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VwCuotaConEquipo|null find($id, $lockMode = null, $lockVersion = null)
 * @method VwCuotaConEquipo|null findOneBy(array $criteria, array $orderBy = null)
 * @method VwCuotaConEquipo[]    findAll()
 * @method VwCuotaConEquipo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VwCuotaConEquipoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VwCuotaConEquipo::class);
    }
    public function findVencimiento($usuario=null,$empresa=null,$compania=null,$filtro=null,$tipoUsuario=null,$vigente=true, $otros=null,$conrestriccion=true,$esCobranza=false,$segmento=null){
        
         /**JRM: 2025-11-13 - Se agrega función de si la deuda es menor a la deuda minima, 
        *devuelva la fecha de la siguiente cuota
        */
        $configuracion = $this->getEntityManager()->getRepository(Configuracion::class)->find(1);
        
        $query=$this->createQueryBuilder('c');
        $query->join('c.contrato','co');
        $query->join('co.agenda','a');
        $query->join('a.cuenta','cu');
        
        if($conrestriccion==true){
            if($vigente){
                if($esCobranza){
                               
                    $query->andWhere('(c.monto>(c.pagado+'.$configuracion->getDeudaMinima().') or c.pagado is null)');
                   

                }else{
                    $query->andWhere('c.monto>=(c.pagado+'.$configuracion->getDeudaMinima().') or c.pagado is null');
                }
                
                $query->andWhere('c.anular is null or c.anular = false');
                $query->andWhere(' co.isFinalizado = false or co.isFinalizado is null'); 

            }else{
                $query->andWhere(' co.isFinalizado=true');
            }
        }
        

        if(!is_null($empresa)){
            
            $query->andWhere('cu.empresa = '.$empresa);
        }
        switch($tipoUsuario){
            case 6://Abogado
                if(!is_null($usuario)){
                    $query->andWhere('a.abogado = '.$usuario)
                    ->andWhere("DATEDIFF(now(),c.fechaPago)<=30")
                    ->andWhere("c.numero=1");

                }
                break;
            case 7://Tramitador
                if(!is_null($usuario))
                    $query->andWhere('co.tramitador = '.$usuario);
                break;
        }
        
        
        if(!is_null($filtro)){ 
            $query->andWhere("(co.nombre like '%$filtro%' or co.rut like '%$filtro%')")
         ;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        
        if(!is_null($otros) && $otros!=''){ 
            $query->andWhere($otros)
         ;

        }
        $query->orderBy("co.fechaCreacion","Desc");
        $query->groupBy('c.contrato');

        return $query->getQuery()
            ->getResult()
        ;
    }


    

    public function findVencimientoGroup($usuario=null,$empresa=null,$compania=null,$filtro=null,$tipoUsuario=null,$vigente=true, $otros=null,$conrestriccion=true,$esCobranza=false){
        $query=$this->createQueryBuilder('c');

        $query->select(array('c','count(distinct c.id)','sum(c.monto)'));
        $query->join('c.contrato','co');
        $query->join('co.agenda','a');
        $query->join('a.cuenta','cu');
        
        if($conrestriccion==true){
            if($vigente){
                if($esCobranza){
                    $query->andWhere('c.monto>c.pagado or c.pagado is null');
                }else{
                    $query->andWhere('c.monto>=c.pagado or c.pagado is null');
                }
                
                $query->andWhere('c.anular is null or c.anular = false');
                $query->andWhere(' co.isFinalizado = false or co.isFinalizado is null'); 

            }else{
                $query->andWhere(' co.isFinalizado=true');
            }
        }
        

        if(!is_null($empresa)){
            
            $query->andWhere('cu.empresa = '.$empresa);
        }
        switch($tipoUsuario){
            case 6://Abogado
                if(!is_null($usuario)){
                    $query->andWhere('a.abogado = '.$usuario)
                    ->andWhere("DATEDIFF(now(),c.fechaPago)<=30")
                    ->andWhere("c.numero=1");

                }
                break;
            case 7://Tramitador
                if(!is_null($usuario))
                    $query->andWhere('co.tramitador = '.$usuario);
                break;
        }
        
        
        if(!is_null($filtro)){ 
            $query->andWhere("(co.nombre like '%$filtro%' or co.rut like '%$filtro%')")
         ;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        
        if(!is_null($otros) && $otros!=''){ 
            $query->andWhere($otros)
         ;

        }
    
        return $query->getQuery()
            ->getResult()
        ;
    }

    public function findPagado($usuario=null,$empresa=null,$compania=null,$filtro=null,$tipoUsuario=null,$vigente=true, $otros=null,$conrestriccion=true,$esCobranza=false){
        $query=$this->createQueryBuilder('c')
        ->join('c.contrato','co')
        ->join('co.agenda','a')
        ->join('a.cuenta','cu')
        ->join('c.pagoCuotas','pc')
        ->join('pc.pago','p');
        
        if($conrestriccion==true){
            if($vigente){
                if($esCobranza){
                    $query->andWhere('c.monto>c.pagado or c.pagado is null');
                }else{
                    $query->andWhere('c.monto>=c.pagado or c.pagado is null');
                }
                
                $query->andWhere('c.anular is null or c.anular = false');
                $query->andWhere(' co.isFinalizado = false or co.isFinalizado is null'); 

            }else{
                $query->andWhere(' co.isFinalizado=true');
            }
        }
        

        if(!is_null($empresa)){
            
            $query->andWhere('cu.empresa = '.$empresa);
        }
        switch($tipoUsuario){
            case 6://Abogado
                if(!is_null($usuario)){
                    $query->andWhere('a.abogado = '.$usuario)
                    ->andWhere("DATEDIFF(now(),c.fechaPago)<=30")
                    ->andWhere("c.numero=1");

                }
                break;
            case 7://Tramitador
                if(!is_null($usuario))
                    $query->andWhere('co.tramitador = '.$usuario);
                break;
        }
        
        
        if(!is_null($filtro)){ 
            $query->andWhere("(co.nombre like '%$filtro%' or co.rut like '%$filtro%')")
         ;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        
        if(!is_null($otros) && $otros!=''){ 
            $query->andWhere($otros)
         ;

        }
        $query->orderBy("co.fechaCreacion","Desc");
        $query->groupBy('c.contrato');
        
        return $query->getQuery()
            ->getResult()
        ;
    }
    public function findOneByPrimeraVigente($contrato,$isMulta=false): ?Cuota
    {
        
        /**JRM: 2025-11-13 - Se agrega función de si la deuda es menor a la deuda minima, 
        *devuelva la fecha de la siguiente cuota
        */
        $configuracion = $this->getEntityManager()->getRepository(Configuracion::class)->find(1);


        $query=$this->createQueryBuilder('c')
        ->join("c.contrato","con")
        ->andWhere('con.id=:contra')
        ->setParameter('contra', $contrato)
        ->andWhere('c.monto>(c.pagado+'.$configuracion->getDeudaMinima().') or c.pagado is null')
        ;
        $query->andWhere('c.anular is null or c.anular = false');
       if($isMulta){
            $query->andWhere('c.isMulta = true');
        }
        $query
        ->setMaxResults(1);
        return $query->getQuery()
            ->getOneOrNullResult()
        ;
    }
    public function findOneByUltimaPagada($contrato): ?Cuota
    {
        $query=$this->createQueryBuilder('c')
        ->andWhere('c.contrato=:contra')
        ->andWhere('c.pagado>0')
        ->setParameter('contra', $contrato)
        ->orderBy('c.numero', 'Desc')
        ->setMaxResults(1);
        return $query->getQuery()
            ->getOneOrNullResult()
        ;
    }

    // /**
    //  * @return Cuota[] Returns an array of Cuota objects
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
    public function findOneBySomeField($value): ?Cuota
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
