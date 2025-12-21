<?php

namespace App\Repository;

use App\Entity\VwContrato;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use LDAP\Result;
use Symfony\Component\Validator\Constraints\IsNull;

/**
 * @method Contrato|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contrato|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contrato[]    findAll()
 * @method Contrato[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VwContratoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VwContrato::class);
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
    
    public function findByPers($usuario=null,$empresa=null,$compania=null,$filtro=null,$agendador=null, $otros=null, $deuda = false, $status=null)
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
        if(!is_null($status)){
            if($status==0){
                $query->andWhere('c.FechaEncuesta is not null');
            }
            if($status==1){
                $query->andWhere('c.FechaGestion is not null');
            }
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
            $query->andWhere("(c.nombre like '%$filtro%')")
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
            $query->andWhere("(c.nombre like '%$filtro%')")
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
            $query->andWhere("(c.nombre like '%$filtro%')")
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
            $query->andWhere("(c.nombre like '%$filtro%')")
         ;

        }
        $query->groupBy('c.estadoEncuesta');
        
        return $query
            
            ->getQuery()
            ->getResult()
        ;
    }
    public function findByEncuestaResumenFechas(int $usuario = null, int $empresa = null, $compania=null,$otros=null, $filtro=null,$tipoFecha, $status, $fechaInicio,$fechaFin )
    {

        
       
        if(is_null($status) or $status==0){
            $query1 = $this->createQueryBuilder('c')
            ->select('c', 'COUNT(c.id) as cantidad')
            ->join('c.agenda', 'a')
            ->leftJoin('c.estadoEncuesta', 'e')
            ->where('c.estadoEncuesta IN (2)')
            ->andWhere($otros)
            ->andWhere('c.FechaEncuesta is not null')
            ->groupBy('c.estadoEncuesta');

            $result1 = $query1->getQuery()->getResult();
                // Agregar el campo 'tipo' manualmente
            foreach ($result1 as &$row) {
                $row['tipo'] = 'Encuestas';
            }
        }
        if(is_null($status) or $status==1){
        $query2 = $this->createQueryBuilder('c')
            ->select('c', 'COUNT(c.id) as cantidad')
            ->join('c.agenda', 'a')
            ->leftJoin('c.estadoEncuesta', 'e')
            ->where('c.estadoEncuesta IN (2)')
            ->andWhere($otros)
            ->andWhere('c.FechaGestion is not null')
            ->groupBy('c.estadoEncuesta');

            $result2 = $query2->getQuery()->getResult();
            foreach ($result2 as &$row) {
                $row['tipo'] = 'Gestiones';
            }
        }
        // Ejecutar las consultas por separado
        
        
        
        
        if(is_null($status)){
            // Combinar los resultados
            return array_merge($result1, $result2);
        }
        if($status==0){
            return $result1;
        }

        if($status==1){
            return $result2;
        }
      

    }

    public function findByEncuestaResumenEncuestador($fecha,$status){
        
        $query=$this->createQueryBuilder('c')
        ->select(array('count(c) as valor,c.usuarioCalidad'))
        ->join('c.agenda','a')
        ->where('c.estadoEncuesta in (2)')
        ->andWhere($fecha);

        if($status==0){
            $query->andWhere('c.FechaEncuesta is not null');
        }else if($status == 1){
            $query->andWhere('c.FechaGestion is not null');
        }

        $query->groupBy('c.usuarioCalidad');
        
        return $query
            ->getQuery()
            ->getResult();
    }

    public function findGestionesByFechaRange(\DateTime $fechaInicio, \DateTime $fechaFin): array
    {

        $query = $this->createQueryBuilder('c')
            ->where('c.FechaGestion BETWEEN :fechaInicio AND :fechaFin')
            ->setParameter('fechaInicio', $fechaInicio->format('Y-m-d'))
            ->setParameter('fechaFin', $fechaFin->format('Y-m-d')); // Uso de un parámetro para evitar inyección SQL
    
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

    public function findVencimientoGroup($usuario=null,$empresa=null,$compania=null,$filtro=null,$tipoUsuario=null,$vigente=true, $otros=null,$conrestriccion=true,$esCobranza=false){
        $query=$this->createQueryBuilder('c');

        $query->select(array('c','count(c.id)','sum(c.monto)'));
       
        $query->join('c.agenda','a');
        $query->join('a.cuenta','cu');
        
        if($conrestriccion==true){
            if($vigente){
               
                $query->andWhere(' c.isFinalizado = false or c.isFinalizado is null'); 

            }else{
                $query->andWhere(' c.isFinalizado=true');
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
                    $query->andWhere('c.tramitador = '.$usuario);
                break;
        }
        
        
        if(!is_null($filtro)){ 
            $query->andWhere("(c.nombre like '%$filtro%' or c.rut like '%$filtro%')")
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
