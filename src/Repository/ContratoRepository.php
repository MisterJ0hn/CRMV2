<?php

namespace App\Repository;

use App\Entity\Contrato;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Contrato|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contrato|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contrato[]    findAll()
 * @method Contrato[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContratoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contrato::class);
    }

    public function findRange($inicio, $fin)
    {
        $query=$this->createQueryBuilder('c');
        $query->where("c.id between  ".$inicio. " and ".$fin);
        
        return $query->getQuery()
        ->getResult();

    }
    public function findLoteMax($empresa=null): ?Contrato
    {
        $query=$this->createQueryBuilder('c')
        ->join('c.agenda','a')
        ->join('a.cuenta','cu')
        ->andWhere('c.folio != 0');
        if(!is_null($empresa)){
            
            $query->andWhere('cu.empresa = '.$empresa);
        }

        return $query->setMaxResults(1)
        ->orderBy('c.id', 'DESC')
        ->getQuery()
        ->getOneOrNullResult();

    }
    
    public function findByPers($usuario=null,$empresa=null,$compania=null,$filtro=null,$agendador=null, $otros=null, $deuda = false)
    {


        $query=$this->createQueryBuilder('c');
        $query->join('c.agenda','a');
        $query->join('a.cuenta','cu');
        $query->leftJoin('c.estadoEncuesta','e');
        //$query->andWhere('(DATEDIFF(now(), c.fechaPrimerPago)/30)<c.vigencia');
        if(!is_null($empresa)){
            
            $query->andWhere('cu.empresa = '.$empresa);
        }
        if(!is_null($usuario)){
            $query->andWhere('a.abogado = '.$usuario);
        }
        if(!is_null($agendador)){
            
            $query->andWhere('a.agendador = '.$agendador);
        }
        if(!is_null($filtro)){ 
            $query->andWhere("(c.nombre like '%$filtro%' or c.telefono like '%$filtro%' or c.email like '%$filtro%')")
         ;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        
        return $query
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
      * @return Contrato[] Retorna un array de Agenda objects sin contrato creado
    */
    public function findByPersSinContr($usuario=null,$empresa=null,$compania=null,$status=null, $filtro=null,$esAbogado=null,$otros=null,$tipoFecha=null)
    {
        $query=$this->createQueryBuilder('c')
        ->rightJoin('c.agenda', 'a')
        ->andWhere('c.id is null');
        if(!is_null($status)){
            $query->andWhere('a.status in ('.$status.')');
        }
        if(!is_null($empresa)){
            $query->join('a.cuenta','i');
            $query->andWhere('i.empresa = '.$empresa);
        }
        switch($esAbogado){
            case 1:
                if(!is_null($usuario)){
                    $query->andWhere('a.abogado = '.$usuario);
                }else{
                    $query->andWhere('a.abogado is not null ');
                }
            break;
            case 0:
                if(!is_null($usuario)){
                    $query->andWhere('a.agendador = '.$usuario);
                }
            break;
            default:
                if(!is_null($usuario)){
                    $query->andWhere('a.agendador = '.$usuario);
                }
            break;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        if(!is_null($filtro)){ 
            $query->andWhere("(a.nombreCliente like '%$filtro%' or a.telefonoCliente like '%$filtro%' or a.emailCliente like '%$filtro%')")
         ;

        }

        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
    
        return $query->getQuery()
            ->getResult()
        ;
    }

    public function findByPersDeuda($usuario=null,$empresa=null,$compania=null,$filtro=null,$agendador=null, $otros=null)
    {
        $query=$this->createQueryBuilder('c');
        $query->join('c.agenda','a');
        $query->join('a.cuenta','cu');
        if(!is_null($empresa)){
            
            $query->andWhere('cu.empresa = '.$empresa);
        }
        if(!is_null($usuario)){
            $query->andWhere('a.abogado = '.$usuario);
        }
        if(!is_null($agendador)){
            
            $query->andWhere('a.agendador = '.$agendador);
        }
        if(!is_null($filtro)){ 
            $query->andWhere("(c.nombre like '%$filtro%' or c.telefono like '%$filtro%' or c.email like '%$filtro%')")
         ;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        return $query
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByCerradores(int $usuario = null, int $empresa = null, $compania=null,$otros=null, $array=null ){
        
        $query=$this->createQueryBuilder('c')
        ->select(array('c','cuo.monto as monto_cuota','sum(pc.monto) as monto_pagado','cuo.numero as cuota_numero','cuo.fechaPago as fecha_vencimiento', 'p.fechaPago as fecha_pago',"dateadd(cuo.fechaPago,30,'DAY') as dia_vencimiento",'datediff(p.fechaPago, cuo.fechaPago) as q_dias' ))
        ->join('c.agenda','a')
        ->join('a.cuenta','cu')
        ->join('c.detalleCuotas','cuo')
        ->join('cuo.pagoCuotas','pc')
        ->join('pc.pago','p');
        
        if($otros != null){
            $query->andWhere($otros);
        }
        if($usuario != null){
            $query->andWhere("a.abodado=$usuario");
        }
        if($array!=null){
            
            $query->orderBy($array['sort'],$array['direction']);

        }
        

        //$query->addGroupBy('p.id');
        $query->addGroupBy('cuo.id');
        return $query
            ->getQuery()
            ->getResult()
        ;
    }
    public function findByCerradoresGroup(int $usuario = null, int $empresa = null, $compania=null,$otros=null, $array=null ){
        
        $query=$this->createQueryBuilder('c')
        ->select(array('c','count(c) as cantidad','sum(pc.monto) as monto_pagado ','cuo.numero as cuota_numero','cuo.fechaPago as fecha_vencimiento', 'p.fechaPago as fecha_pago',"dateadd(cuo.fechaPago,30,'DAY') as dia_vencimiento",'datediff(p.fechaPago, cuo.fechaPago) as q_dias' ))
        ->join('c.agenda','a')
        ->join('a.cuenta','cu')
        ->join('c.detalleCuotas','cuo')
        ->join('cuo.pagoCuotas','pc')
        ->join('pc.pago','p');
        
        if($otros != null){
            $query->andWhere($otros);
        }
        if($usuario != null){
            $query->andWhere("a.abodado=$usuario");
        }
        if($array!=null){
            
            $query->orderBy($array['sort'],$array['direction']);

        }
        $query->groupBy('a.abogado');
        return $query
            
            ->getQuery()
            ->getResult()
        ;
    }
    public function findByCerradoresResumen(int $usuario = null, int $empresa = null, $compania=null,$otros=null, $array=null ){
        
        $query=$this->createQueryBuilder('c')
        ->select(array('c','count(DISTINCT c) as cantidad','sum(pc.monto) as monto_pagado ','cuo.numero as cuota_numero','cuo.fechaPago as fecha_vencimiento', 'p.fechaPago as fecha_pago',"dateadd(cuo.fechaPago,30,'DAY') as dia_vencimiento",'datediff(p.fechaPago, cuo.fechaPago) as q_dias' ))
        ->join('c.agenda','a')
        ->join('a.cuenta','cu')
        ->join('c.detalleCuotas','cuo')
        ->join('cuo.pagoCuotas','pc')
        ->join('pc.pago','p');
        
        if($otros != null){
            $query->andWhere($otros);
        }
        if($usuario != null){
            $query->andWhere("a.abodado=$usuario");
        }
        if($array!=null){
            
            $query->orderBy($array['sort'],$array['direction']);

        }
        
        return $query
            
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    public function findByCerradoresResumenCant(int $usuario = null, int $empresa = null, $compania=null,$otros=null, $array=null ){
        
        $query=$this->createQueryBuilder('c')
        ->select(array('c','count(c) as cantidad','sum(pc.monto) as monto_pagado ','cuo.numero as cuota_numero','cuo.fechaPago as fecha_vencimiento', 'p.fechaPago as fecha_pago',"dateadd(cuo.fechaPago,30,'DAY') as dia_vencimiento",'datediff(p.fechaPago, cuo.fechaPago) as q_dias' ))
        ->join('c.agenda','a')
        ->join('a.cuenta','cu')
        ->join('c.detalleCuotas','cuo')
        ->join('cuo.pagoCuotas','pc')
        ->join('pc.pago','p');
        
        if($otros != null){
            $query->andWhere($otros);
        }
        if($usuario != null){
            $query->andWhere("a.abodado=$usuario");
        }
        if($array!=null){
            
            $query->orderBy($array['sort'],$array['direction']);

        }
        
        return $query
            
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    public function findNombreRut($texto){
        $query=$this->createQueryBuilder('c')
        ->where("c.nombre like '%".$texto."%' or c.rut like '%".$texto."%' ");

        return $query
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByCaducados($usuario=null,$empresa=null,$compania=null,$filtro=null,$agendador=null, $otros=null, $deuda = false, $vigencia=24)
    {


        $query=$this->createQueryBuilder('c');
        $query->join('c.agenda','a');
        $query->join('a.cuenta','cu');
        $query->join('cu.cuentaMaterias','cm');
        $query->join('cm.materia','m');

       // $query->andWhere('(DATEDIFF(now(), c.fechaCreacion)/30)>'.$vigencia);

       $query->andWhere('timestampdiff(month,c.fechaCreacion,now())>'.$vigencia);
        if(!is_null($empresa)){
            
            $query->andWhere('cu.empresa = '.$empresa);
        }
        if(!is_null($usuario)){
            $query->andWhere('a.abogado = '.$usuario);
        }
        if(!is_null($agendador)){
            
            $query->andWhere('a.agendador = '.$agendador);
        }
        if(!is_null($filtro)){ 
            $query->andWhere("(c.nombre like '%$filtro%' or c.telefono like '%$filtro%' or c.email like '%$filtro%')")
         ;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        
        return $query
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function findByTerminados($usuario=null,$empresa=null,$compania=null,$filtro=null,$agendador=null, $otros=null, $deuda = false)
    {


        $query=$this->createQueryBuilder('c');
        $query->join('c.agenda','a');
        $query->join('a.cuenta','cu');
        $query->join('cu.cuentaMaterias','cm');
        $query->join('cm.materia','m');
    
        if(!is_null($empresa)){
            
            $query->andWhere('cu.empresa = '.$empresa);
        }
        if(!is_null($usuario)){
            $query->andWhere('a.abogado = '.$usuario);
        }
        if(!is_null($agendador)){
            
            $query->andWhere('a.agendador = '.$agendador);
        }
        if(!is_null($filtro)){ 
            $query->andWhere("(c.nombre like '%$filtro%' or c.telefono like '%$filtro%' or c.email like '%$filtro%')")
         ;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        
        return $query
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function findByEncuestaResumenCant(int $usuario = null, int $empresa = null, $compania=null,$otros=null, $filtro=null ){
        
        $query=$this->createQueryBuilder('c')
        ->select(array('c','e','count(c) as cantidad'))
        ->join('c.agenda','a')
        ->leftJoin('c.estadoEncuesta','e')
        ->where('c.estadoEncuesta in (1,2)');
        
        if($otros != null){
            $query->andWhere($otros);
        }
        if($usuario != null){
            $query->andWhere("a.abodado=$usuario");
        }
        if(!is_null($filtro)){ 
            $query->andWhere("(c.nombre like '%$filtro%' or c.telefono like '%$filtro%' or c.email like '%$filtro%')")
         ;

        }
        $query->groupBy('c.estadoEncuesta');
        
        return $query
            
            ->getQuery()
            ->getResult()
        ;
    }

    public function updateTramitadorCartera($tramitadorId, $carteraId)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'UPDATE App\Entity\Contrato c
            SET c.tramitador = :tramitadorId
            WHERE c.cartera = :carteraId'
        );
        $query->setParameter('tramitadorId', $tramitadorId);
        $query->setParameter('carteraId', $carteraId);

        return $query->execute();
    } 
     public function updateTicketPorCartera($tramitadorNuevoId, $carteraId)
    {

        $conn = $this->getEntityManager()->getConnection();
        $conn->beginTransaction();
        $conn->executeStatement('CALL sp_reasignar_tickets_por_cartera(:carteraId, :tramitadorNuevoId)',
                ['tramitadorId' => $tramitadorNuevoId, 'carteraId' => $carteraId]);
        $conn->commit();
    }  
    public function updateTicketMasivo($usuarioReasignadorId,$tramitadorId, $tramitadorNuevoId)
    {

        $conn = $this->getEntityManager()->getConnection();
        $conn->beginTransaction();
        $conn->executeStatement('CALL sp_reasignar_tickets_masivo(:usuarioReasignadorId, :tramitadorId,:tramitadorNuevoId)',
                ['usuarioReasignadorId' => $usuarioReasignadorId, 'tramitadorNuevoId' => $tramitadorNuevoId, 'tramitadorId' => $tramitadorId]);
        $conn->commit();
    }   
    // /**
    //  * @return Contrato[] Returns an array of Contrato objects
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
    public function findOneBySomeField($value): ?Contrato
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
