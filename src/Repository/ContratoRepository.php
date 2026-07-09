<?php

namespace App\Repository;

use App\Entity\Configuracion;
use App\Entity\Contrato;
use App\Entity\Vencimiento;
use App\Entity\VwCuotaPendiente;
use DateTime;
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

    /**
     * @return Contrato[]
     */
    public function findByTelefono(string $telefono): array
    {
        $sinPlus  = ltrim($telefono, '+');
        $conPlus  = '+' . $sinPlus;
        $query=$this->createQueryBuilder('c')
            ->leftJoin('c.cartera', 'car')
            ->leftJoin('c.cliente', 'cli')
            ->leftJoin('car.materia', 'mat')->addSelect('car', 'mat')->where('cli.telefono IN (:variantes) OR c.telefonoRecado IN (:variantes)')
            ->setParameter('variantes', [$sinPlus, $conPlus]);

        $query->andWhere(" ((c.fechaCreacion between  date_sub(current_timestamp(),c.vigencia,'month') and current_timestamp()) )
                        or ((c.vigenciaUltAnexo is not null and  c.fechaCreacionUltAnexo between  date_sub(current_timestamp(),c.vigenciaUltAnexo,'month') and current_timestamp()))
                        and c.fechaDesiste is null");

        return $query->getQuery()
            ->getResult();
    }

    /**
     * @return Contrato[]
     */
    public function findActiveByRut(string $rut): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.cliente', 'cli')
            ->where(" REPLACE(REPLACE(cli.rut, '.', ''), '-', '') = REPLACE(REPLACE(:rut, '.', ''), '-', '') ")
            ->andWhere('c.isFinalizado = false OR c.isFinalizado IS NULL')
            ->setParameter('rut', $rut)
            ->orderBy('c.fechaCreacion', 'DESC')
            ->getQuery()
            ->getResult();
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
    
    /**
     * Query para el listado de contratos consultor (prime / preferente / morosos).
     * Devuelve un Query de Doctrine listo para KnpPaginator.
     * Alias DQL: c=Contrato, a=Agenda, cu=Cuenta
     */
    public function findConsultorQuery($usuario=null, $empresa=null, $compania=null, $filtro=null, $otros=null)
    {
        $query = $this->createQueryBuilder('c');
        $query->join('c.agenda', 'a');
        $query->join('a.cuenta', 'cu');
        $query->leftJoin('c.cliente', 'cli');
        $query->andWhere('a.status IN (7,14)');
        $query->andWhere('c.isFinalizado = false OR c.isFinalizado IS NULL');
        $query->andWhere('c.esVip=false');
        if (!is_null($empresa)) {
            $query->andWhere('cu.empresa = ' . $empresa);
        }
        if (!is_null($usuario)) {
            $query->andWhere('a.abogado = ' . $usuario);
        }
        if (!is_null($filtro)) {
            $query->andWhere("(cli.nombre LIKE '%$filtro%' OR cli.telefono LIKE '%$filtro%' OR cli.correo LIKE '%$filtro%')");
        }
        if (!is_null($compania)) {
            $query->andWhere('a.cuenta in (' . $compania.')');
        }
        if (!is_null($otros)) {
            $query->andWhere($otros);
        }

        return $query->orderBy('c.id', 'DESC')->getQuery();
    }

    
    public function findIndexQuery($usuario=null, $empresa=null, $compania=null, $filtro=null, $agendador=null, $otros=null)
    {
        $query = $this->createQueryBuilder('c')
        ->join('c.agenda', 'a')
        ->join('a.cuenta', 'cu')
        ->join('a.agendador','ag')
        ->join('a.abogado','ab')
        ->leftJoin('c.tramitador', 't')
        ->leftJoin('c.sucursal','s')
        ->leftJoin('c.cliente','cli');

        if (!is_null($empresa)) {
            $query->andWhere('cu.empresa = ' . $empresa);
        }
        if (!is_null($usuario)) {
            $query->andWhere('a.abogado = ' . $usuario);
        }
        if (!is_null($agendador)) {
            $query->andWhere('a.agendador = ' . $agendador);
        }
        if (!is_null($filtro)) {
            $query->andWhere("(cli.nombre like '%$filtro%' or cli.telefono like '%$filtro%' or cli.correo like '%$filtro%')");
        }
        if (!is_null($compania)) {
            $query->andWhere('a.cuenta = ' . $compania);
        }
        if (!is_null($otros)) {
            $query->andWhere($otros);
        }

        return $query->orderBy('c.id', 'DESC')->getQuery();
    }

    public function findIndexQueryVencidos($usuario=null, $empresa=null, $compania=null, $filtro=null, $agendador=null, $otros=null)
    {
        $query = $this->createQueryBuilder('c');
        $query->join('c.agenda', 'a');
        $query->join('a.cuenta', 'cu');
        $query->leftJoin('c.cliente', 'cli');

        $query->andWhere(" EXISTS(
        SELECT 1 
        FROM App\\Entity\\Cuota cuo 
        WHERE cuo.contrato = c 
        AND cuo.anular IS NULL
        GROUP BY cuo.contrato
        HAVING SUM(cuo.monto) <= SUM(cuo.pagado)
    ) ");
        $query->andWhere(" a.status IN (7,14)");
        if (!is_null($empresa)) {
            $query->andWhere('cu.empresa = ' . $empresa);
        }
        if (!is_null($usuario)) {
            $query->andWhere('a.abogado = ' . $usuario);
        }
        if (!is_null($agendador)) {
            $query->andWhere('a.agendador = ' . $agendador);
        }
        if (!is_null($filtro)) {
            $query->andWhere("(cli.nombre like '%$filtro%' or cli.telefono like '%$filtro%' or cli.correo like '%$filtro%')");
        }
        if (!is_null($compania)) {
            $query->andWhere('a.cuenta = ' . $compania);
        }
        if (!is_null($otros)) {
            $query->andWhere($otros);
        }

        return $query->orderBy('c.id', 'DESC')->getQuery();
    }

    public function findByPers($usuario=null,$empresa=null,$compania=null,$filtro=null,$agendador=null, $otros=null, $deuda = false)
    {


        $query=$this->createQueryBuilder('c');
        $query->join('c.agenda','a');
        $query->join('a.cuenta','cu');
        $query->leftJoin('c.estadoEncuesta','e');
        $query->leftJoin('c.cliente','cli');
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
            $query->andWhere("(cli.nombre like '%$filtro%' or cli.telefono like '%$filtro%' or cli.correo like '%$filtro%')")
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
        $query->leftJoin('c.cliente','cli');
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
            $query->andWhere("(cli.nombre like '%$filtro%' or cli.telefono like '%$filtro%' or cli.correo like '%$filtro%')")
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
        ->leftJoin('c.cliente', 'cli')
        ->where("cli.nombre like '%".$texto."%' or cli.rut like '%".$texto."%' ");

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
        $query->leftJoin('c.cliente','cli');

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
            $query->andWhere("(cli.nombre like '%$filtro%' or cli.telefono like '%$filtro%' or cli.correo like '%$filtro%')")
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
        $query->leftJoin('c.cliente','cli');

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
            $query->andWhere("(cli.nombre like '%$filtro%' or cli.telefono like '%$filtro%' or cli.correo like '%$filtro%')")
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
        ->leftJoin('c.cliente','cli')
        ->where('c.estadoEncuesta in (1,2)');

        if($otros != null){
            $query->andWhere($otros);
        }
        if($usuario != null){
            $query->andWhere("a.abodado=$usuario");
        }
        if(!is_null($filtro)){
            $query->andWhere("(cli.nombre like '%$filtro%' or cli.telefono like '%$filtro%' or cli.correo like '%$filtro%')")
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
     public function updateTicketPorCartera(int $tramitadorNuevoId,int $carteraId,string $usuarioReasignador,DateTime $fechaReasignacion)
    {

        $conn = $this->getEntityManager()->getConnection();
        $conn->beginTransaction();
        $conn->executeStatement('CALL sp_reasignar_tickets_por_cartera(:carteraId, :tramitadorNuevoId, :usuarioReasignador, :fechaReasignacion)',
                ['carteraId' => $carteraId, 'tramitadorNuevoId' => $tramitadorNuevoId, 'usuarioReasignador' => $usuarioReasignador, 'fechaReasignacion' => $fechaReasignacion->format("Y-m-d H:i:s")]);
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


    public function obtenerContratosPendientesSuscripcion(){
        $query=$this->createQueryBuilder('c')
        ->andWhere('c.estadoSuscripcion is null')
        ->andWhere('c.suscripcionId is not null or c.suscripcionId = "" ')
        ->andWhere('c.aceptaSuscripcion = 1')
        ->andWhere('c.sesionSuscripcionActiva = 1')
        ->andWhere('c.cancelaSuscripcion is null')
        ;
        
         return $query
            ->getQuery()
            ->getResult()
        ;
    }
    public function findAbogadoContratoStats($empresa=null, $compania=null, $dateInicio=null, $dateFin=null, $usuario=null)
    {
        $qb = $this->createQueryBuilder('c')
            ->select(
                'u.id as abogado_id',
                'COUNT(DISTINCT c.id) as contratos',
                'SUM(c.MontoContrato) as suma_monto',
                'SUM(c.cuotas) as suma_cuotas',
                'SUM(CASE WHEN IDENTITY(c.contratoEstadoSuscripcion) = 2 THEN 1 ELSE 0 END) as cierre_virtualpos',
                'SUM(CASE WHEN c.cuotas = 1 THEN 1 ELSE 0 END) as pagos_totales_candidatos'
            )
            ->join('c.agenda', 'a')
            ->join('a.abogado', 'u')
            ->join('a.cuenta', 'cu')
            ->andWhere('a.abogado IS NOT NULL')
            ->andWhere("c.fechaCreacion BETWEEN '$dateInicio' AND '$dateFin 23:59:59'")
            ->andWhere("a.status=7")
            ->groupBy('u.id');

        if (!is_null($empresa)) {
            $qb->andWhere('cu.empresa = ' . $empresa);
        }
        if (!is_null($compania)) {
            $qb->andWhere('a.cuenta = ' . $compania);
        }
        if (!is_null($usuario)) {
            $qb->andWhere('a.abogado = ' . $usuario);
        }

        return $qb->getQuery()->getArrayResult();
    }
    
    /**
     * Enriquece los registros VwContratosVencidos con datos calculados desde vistas y tablas
     * Strategy: Batch query enrichment (una sola query por página vs N queries)
     *
     * @param array $items - Array de VwContratosVencidos objects
     * @param \Doctrine\DBAL\Connection $connection
     * @return void
     */
    public function enrichItems(array $items, \Doctrine\DBAL\Connection $connection): void
    {
        if (empty($items)) {
            return;
        }

        $idsList = implode(',', array_map(fn($item) => $item->getId(), $items));

        try {
            $sql = "
                SELECT
                    c.id,
                    coalesce(ca.fecha_creacion, c.fecha_creacion) AS fecha_creacion_vista,
                    coalesce(concat(ca.id, '-', c.folio, '-', ca.folio), c.folio) AS folio_vista,
                    CASE WHEN cpt.contrato_id IS NOT NULL THEN 1 ELSE 0 END AS pagado,
                    CASE
                        WHEN vm.folio IS NOT NULL THEN 1
                        WHEN vr.folio IS NOT NULL THEN 1
                        WHEN vu.folio IS NOT NULL THEN 1
                        ELSE 0
                    END AS vip,
                    vuolt.fecha_registro,
                    COALESCE(DATEDIFF(NOW(), vuolt.fecha_registro), 0) AS dias_ult_observacion,
                    CASE WHEN TIMESTAMPDIFF(MONTH, c.fecha_creacion, NOW()) <= c.vigencia THEN 1 ELSE 0 END AS vigencia_contrato,
                    CASE WHEN ca.id IS NOT NULL AND TIMESTAMPDIFF(MONTH, ca.fecha_creacion, NOW()) <= ca.vigencia THEN 1
                         WHEN ca.id IS NOT NULL THEN 0 ELSE NULL END AS vigencia_anexo
                FROM vw_contratos_vencidos vc
                INNER JOIN contrato c ON c.id = vc.contrato_id
                LEFT JOIN vw_contrato_pagado_total cpt ON cpt.contrato_id = c.id
                LEFT JOIN vw_vip_mayor_2mm vm ON vm.contrato_id = c.id
                LEFT JOIN vw_vip_referidos vr ON vr.contrato_id = c.id
                LEFT JOIN vw_vip_una_cuota vu ON vu.contrato_id = c.id
                LEFT JOIN vw_ult_observacion_linea_tiempo vuolt ON vuolt.contrato_id = c.id
                LEFT JOIN vista_contrato_anexo_max ca ON ca.contrato_id = c.id
                WHERE vc.id IN ($idsList)
            ";

            $rows = $connection->fetchAllAssociative($sql);
            $rowsById = array_column($rows, null, 'id');

            foreach ($items as $vwContrato) {
                $row = $rowsById[$vwContrato->getId()] ?? null;
                if ($row) {
                    $vwContrato->setDiasUltObservacion((int)$row['dias_ult_observacion']);
                    $vwContrato->setVigenciaContrato((int)$row['vigencia_contrato']);
                    $vwContrato->setVigenciaAnexo($row['vigencia_anexo'] !== null ? (int)$row['vigencia_anexo'] : null);
                    $vwContrato->setVip((int)$row['vip']);
                    $vwContrato->setFolioContrato($row['folio_vista']);
                    if ($row['fecha_creacion_vista']) {
                        $vwContrato->setFechaCreacionVista(new \DateTime($row['fecha_creacion_vista']));
                    }
                }
            }
        } catch (\Exception $e) {
            // Silencio: continuar con valores por defecto si el enriquecimiento falla
        }
    }
    /**
     * Query DBAL para el listado Mis Clientes.
     * Retorna un DBAL QueryBuilder compatible con KnpPaginator.
     * Todas las columnas calculadas (materia, servicio, lt, pago, actividad) son ordenables.
     */
    public function findMisClientesQuery(
                                        $empresa        = null,
                                        $materia        = null,
                                        $filtro         = null,
                                        $usuario        = null,
                                        $otros          = null,
                                        $atrasado       = null,
                                        $tramitador     = null,
                                        $tipoCliente    = null,
                                        $prioridad      = null,
                                        $primeraCuota   = null,
                                        $fechaInicio = null,
                                        $fechaFin = null,
                                        $servicio       = null
                                    ) 
    {
        
        
        $configuracion = $this->getEntityManager()->getRepository(Configuracion::class)->find(1);

        $query = $this->createQueryBuilder('c');
        $query->join('c.agenda', 'a');
        $query->join('a.cuenta', 'cu')
        ->leftJoin('c.cliente', 'cli')
        ->andWhere('c.fechaDesiste is null');



     

       // $query->andWhere(' (c.fecha_creacion between (now() - interval c.vigencia month) and now() )
       //                 or exists ( select 1 from App\Entity\ContratoAnexo ca where ca.fecha_creacion between (now() - interval ca.vigencia month) and now() and ca.contrato = c and ca.isDesiste is null) ');
       $query->andWhere(" ((c.fechaCreacion between  date_sub(current_timestamp(),c.vigencia,'month') and current_timestamp()) and 
                        c.fechaCreacion between '$fechaInicio' and '$fechaFin 23:59:59' and c.vigenciaUltAnexo is null)
                        or ((c.vigenciaUltAnexo is not null and  c.fechaCreacionUltAnexo between  date_sub(current_timestamp(),c.vigenciaUltAnexo,'month') and current_timestamp()) and 
                        c.fechaCreacionUltAnexo between '$fechaInicio' and '$fechaFin 23:59:59')");

        $query->andWhere(" a =  (select max(cau1.agenda) from App\Entity\Causa cau1 where cau1.estado=1 and cau1.agenda=a)");
        if(!is_null($atrasado)){
            $vencimiento = $this->getEntityManager()->getRepository(Vencimiento::class)->find(1);

            if($atrasado==1){
                
                $query->andWhere(" ((c.diasMorosidad >= ".$vencimiento->getValMin()." and c.esVip=0 and c.diasMorosidad <= ".$configuracion->getMorosidadTramitadorMax().") or (c.diasMorosidad >= ".$configuracion->getDiasMorisidadVip()." and c.esVip=1))   ");
            }else{
                $query->andWhere(" (c.diasMorosidad < ".$vencimiento->getValMin()." and c.esVip=0) or (c.diasMorosidad < ".$configuracion->getDiasMorisidadVip()." and c.esVip=1)");             
            }

        }else{
            $query->andWhere(' c.diasMorosidad <= '.$configuracion->getMorosidadTramitadorMax());
        }

        if(!is_null($tipoCliente)){
            if($tipoCliente==1){
                $query->andWhere(" c.esVip = 1 ");
            }else{
                $query->andWhere(" c.esVip = 0 ");
            }

        }
        
        if (!is_null($empresa)) {
            $query->andWhere('cu.empresa = ' . $empresa);
        }
        if (!is_null($usuario)) {
            $query->andWhere('a.abogado = ' . $usuario);
        }
      if (!is_null($tramitador)) {
            $query->andWhere('c.tramitador = ' . $tramitador);
        }
        if (!is_null($filtro)) {
            $query->andWhere("(cli.nombre LIKE :filtro OR REPLACE(REPLACE(cli.rut, '.', ''), '-', '') = REPLACE(REPLACE(:filtroRut, '.', ''), '-', '') OR c.folio LIKE :filtro)")
               ->setParameter('filtro', '%' . $filtro . '%')
               ->setParameter('filtroRut', $filtro);
        }
        if (!is_null($materia)) {
            $query->andWhere("EXISTS (
                SELECT 1
                FROM App\Entity\CuentaMateria cm
                WHERE cm.cuenta = a.cuenta
                  AND cm.materia = $materia
            )");
        }
        if (!is_null($otros)) {
            $query->andWhere($otros);
        }
        
        if(!is_null($prioridad)){
            $query->andWhere(" exists (
                    SELECT c9
                    FROM App\Entity\Mensaje m2
                    Join m2.mensajePrioridad mp1
                    JOIN m2.causa c9
                    WHERE 
                        m2.fechaAviso in (
                            SELECT MIN(m3.fechaAviso)
                            FROM App\Entity\Mensaje m3
                            JOIN m3.causa c10
                            WHERE 
                                m3.fechaAviso >= now()
                                AND c10.agenda = c.agenda
                            group by c10
                        )
                        AND c9.agenda = c.agenda
                        and mp1.id=$prioridad) ");
        }
        if(!is_null($primeraCuota)){
            if($primeraCuota==2){
                $query->andWhere("exists ( select c3 from App\Entity\Cuota c3 where c3.contrato=c and (c3.anular is null  OR c3.anular=0) and (c3.monto>=(c3.pagado+".$configuracion->getDeudaminima().") or c3.pagado is null) and c3.numero=1 )");
            }else{
                $query->andWhere("not exists ( select c3 from App\Entity\Cuota c3 where c3.contrato=c and (c3.anular is null  OR c3.anular=0) and (c3.monto>=(c3.pagado+".$configuracion->getDeudaminima().") or c3.pagado is null) and c3.numero=1 )");
            }
        }

        if (!is_null($servicio)) {
           // $query->andWhere('c.estrategiaJuridica = ' . $servicio);
            $query->andWhere(" exists (select ca1 from App\Entity\Causa ca1 
                                 join App\Entity\MateriaEstrategia me1 
                                join App\Entity\EstrategiaJuridica ej1 
                                where me1=ca1.materiaEstrategia and  ej1 = me1.estrategiaJuridica and ca1.agenda=c.agenda and ej1.id=$servicio and (ca1.causaFinalizada is null or ca1.causaFinalizada=0))");
        }

        return $query->orderBy('c.folio', 'asc')->getQuery();
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

    /**
     * Evalúa si el contrato es VIP según 3 criterios y actualiza el campo esVip.
     * Un contrato es VIP si aparece en al menos una de las 3 consultas:
     *   1. Tiene referido (agenda_contacto_id = 4)
     *   2. Monto contrato >= 2.000.000
     *   3. Solo tiene 1 cuota vigente
     */
    public function actualizarEsVip(int $contratoId): bool
    {
        $conn = $this->getEntityManager()->getConnection();

        $sqlReferido = "
            SELECT c.id AS contrato_id
            FROM contrato c
            JOIN agenda ag ON c.agenda_id = ag.id
            JOIN cuenta_materia cm ON ag.cuenta_id = cm.cuenta_id
            JOIN materia m ON cm.materia_id = m.id
            WHERE c.fecha_desiste IS NULL
              AND ag.agenda_contacto_id = 4
              AND c.id = :id
            LIMIT 1
        ";

        $sqlMonto = "
            SELECT c.id AS contrato_id
            FROM contrato c
            JOIN agenda ag ON c.agenda_id = ag.id
            JOIN cuenta_materia cm ON ag.cuenta_id = cm.cuenta_id
            JOIN materia m ON cm.materia_id = m.id
            WHERE c.monto_contrato >= 2000000
              AND c.id = :id
            LIMIT 1
        ";

        $sqlUnaCuota = "
            SELECT c.id AS contrato_id
            FROM contrato c
            WHERE c.fecha_desiste IS NULL
              AND (SELECT COUNT(0) FROM cuota ct WHERE ct.contrato_id = c.id AND ct.anular IS NULL) = 1
              AND c.id = :id
            LIMIT 1
        ";

        $esVip =
            !empty($conn->fetchOne($sqlReferido, ['id' => $contratoId])) ||
            !empty($conn->fetchOne($sqlMonto,    ['id' => $contratoId])) ||
            !empty($conn->fetchOne($sqlUnaCuota, ['id' => $contratoId]));

        $conn->executeStatement(
            'UPDATE contrato SET es_vip = :vip WHERE id = :id',
            ['vip' => $esVip ? 1 : 0, 'id' => $contratoId]
        );

        return $esVip;
    }

    /**
     * Query paginable para el módulo Encuesta, sin depender de vw_contrato.
     * Retorna un Query object (no resultado) para que KnpPaginator use COUNT+LIMIT SQL.
     */
    public function findEncuestaQuery(
        ?int $usuario = null,
        ?int $empresa = null,
        ?int $compania = null,
        ?string $filtro = null,
        ?string $folio = null,
        ?string $dateInicio = null,
        ?string $dateFin = null,
        int $tipoFecha = 0,
        ?int $status = null,
        ?array $grupos = null
    ): \Doctrine\ORM\Query {
        $qb = $this->createQueryBuilder('c')
            ->addSelect('a', 'cu', 'g')
            ->join('c.agenda', 'a')
            ->join('a.cuenta', 'cu')
            ->leftJoin('c.estadoEncuesta', 'ee')
            ->leftJoin('c.grupo', 'g')
            ->leftJoin('c.cliente', 'cli')
            ->andWhere('a.status IN (7, 14)');

        if (!is_null($empresa)) {
            $qb->andWhere('cu.empresa = :empresa')->setParameter('empresa', $empresa);
        }
        if (!is_null($usuario)) {
            $qb->andWhere('a.abogado = :usuario')->setParameter('usuario', $usuario);
        }
        if (!is_null($compania)) {
            $qb->andWhere('a.cuenta = :compania')->setParameter('compania', $compania);
        }
        if (!is_null($filtro)) {
            $qb->andWhere('(cli.nombre LIKE :filtro OR cli.telefono LIKE :filtro OR cli.correo LIKE :filtro)')
               ->setParameter('filtro', '%' . $filtro . '%');
        }
        if (!is_null($folio) && $folio !== '') {
            $qb->andWhere('(c.folio = :folio OR a.id = :folio)')
               ->setParameter('folio', $folio);
        }
        if (!is_null($grupos)) {
            if (count($grupos) > 0) {
                $qb->andWhere('c.grupo IN (:grupos)')->setParameter('grupos', $grupos);
            } else {
                $qb->andWhere('c.grupo IS NULL');
            }
        }

        if ($dateInicio && $dateFin) {
            $dateFin23 = $dateFin . ' 23:59:59';
            switch ($tipoFecha) {
                case 1: // filtrar por FechaEncuesta (almacenada en contrato)
                    $qb->andWhere('c.FechaEncuesta BETWEEN :dateInicio AND :dateFin')
                       ->setParameter('dateInicio', $dateInicio)
                       ->setParameter('dateFin', $dateFin23)
                       ->andWhere('ee.id = 2');
                    break;
                case 2: // filtrar por fecha de la última gestión (en tabla encuesta)
                    $qb->andWhere('EXISTS(SELECT ge.id FROM App\Entity\Encuesta ge WHERE ge.contrato = c AND ge.FechaCreacion BETWEEN :dateInicio AND :dateFin AND ge.funcionRespuesta != 1 AND ge.estado = 2)')
                       ->setParameter('dateInicio', $dateInicio)
                       ->setParameter('dateFin', $dateFin23)
                       ->andWhere('ee.id = 2');
                    break;
                default: // filtrar por fechaCreacion del contrato
                    $qb->andWhere('c.fechaCreacion BETWEEN :dateInicio AND :dateFin')
                       ->setParameter('dateInicio', $dateInicio)
                       ->setParameter('dateFin', $dateFin23);
                    break;
            }
        }

        if (!is_null($status)) {
            if ($status === 0) {
                $qb->andWhere('c.FechaEncuesta IS NOT NULL');
            } elseif ($status === 1) {
                $qb->andWhere('EXISTS(SELECT ge2.id FROM App\Entity\Encuesta ge2 WHERE ge2.contrato = c AND ge2.funcionRespuesta != 1 AND ge2.estado = 2)');
            }
        }

        return $qb->orderBy('c.id', 'DESC')->getQuery();
    }

    /**
     * Retorna un mapa [contrato_id => ultimaNota] para un conjunto de IDs.
     * Ejecuta 1 sola query batch en lugar de N+1.
     */
    public function getUltimaNotaByContratoIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $rows = $this->getEntityManager()->createQuery(
            'SELECT IDENTITY(e.contrato) as contrato_id, MAX(ep.nota) as nota
             FROM App\Entity\EncuestaPreguntas ep
             JOIN ep.encuesta e
             WHERE IDENTITY(e.contrato) IN (:ids) AND ep.tipoPregunta = 1
             GROUP BY e.contrato'
        )->setParameter('ids', $ids)->getResult();

        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['contrato_id']] = $row['nota'] !== null ? (int)$row['nota'] : null;
        }
        return $map;
    }

    /**
     * Retorna un mapa [contrato_id => DateTime] con la fecha de la última gestión.
     * Ejecuta 1 sola query batch en lugar de N+1.
     */
    public function getFechaGestionByContratoIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $rows = $this->getEntityManager()->createQuery(
            'SELECT IDENTITY(ge.contrato) as contrato_id, MAX(ge.FechaCreacion) as fecha
             FROM App\Entity\Encuesta ge
             WHERE IDENTITY(ge.contrato) IN (:ids) AND ge.funcionRespuesta != 1 AND ge.estado = 2
             GROUP BY ge.contrato'
        )->setParameter('ids', $ids)->getResult();

        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['contrato_id']] = $row['fecha'] !== null ? new \DateTime($row['fecha']) : null;
        }
        return $map;
    }
}
