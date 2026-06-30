<?php

namespace App\Repository;

use App\Entity\Contrato;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class ConsultasRepository
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    private function getContratoIdsClientesActivos(): array
    {
        return $this->em->getConnection()->fetchFirstColumn(
            'SELECT DISTINCT contrato_id FROM temp_clientes_activos_final WHERE contrato_id IS NOT NULL'
        );
    }

    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select(
                'MIN(m.nombre) AS materia',
                'c.folio',
                'c.fechaCreacion AS fechaContrato',
                'MAX(ca.fechaCreacion) AS fechaUltAnexo',
                'c.nombre AS cliente',
                'cerrador.nombre AS nombre_cerrador',
                'tramitador.nombre AS nombre_tramitador',
                'a.id as agenda_id'
            )
            ->from(Contrato::class, 'c')
            ->join('c.agenda', 'a')
            ->join('a.cuenta', 'cu')
            ->join('cu.cuentaMaterias', 'cm')
            ->join('cm.materia', 'm')
            ->join('a.abogado', 'cerrador')
            ->join('c.tramitador', 'tramitador')
            ->leftJoin('c.contratoAnexos', 'ca')
            ->andWhere('c.fechaDesiste IS NULL')
            ->andWhere('IDENTITY(a.status) NOT IN (13, 15)')
            ->andWhere(" ((c.fechaCreacion between  date_sub(current_timestamp(),c.vigencia,'month') and current_timestamp()) and 
                        c.vigenciaUltAnexo is null)
                        or ((c.vigenciaUltAnexo is not null and  c.fechaCreacionUltAnexo between  date_sub(current_timestamp(),c.vigenciaUltAnexo,'month') and current_timestamp()) )")
            ->groupBy('c.id, c.folio, c.fechaCreacion, c.nombre, cerrador.id, cerrador.nombre, tramitador.id, tramitador.nombre')
            ;
    }

    public function findActivos(): array
    {
        return $this->baseQueryBuilder()
            ->andWhere('c.diasMorosidad <= 45')
            ->andWhere('EXISTS (SELECT cu2.id FROM App\Entity\Cuota cu2 WHERE cu2.contrato = c AND cu2.pagado IS NULL AND COALESCE(cu2.anular, 0) = 0)')

            ->getQuery()
            ->getScalarResult();
    }

    public function findAbandonados(): array
    {
        return $this->baseQueryBuilder()
            ->andWhere('c.diasMorosidad > 45')
            ->andWhere('EXISTS (SELECT SUM(COALESCE(cu2.pagado, 0)) FROM App\Entity\Cuota cu2 WHERE cu2.contrato = c AND COALESCE(cu2.anular, 0) = 0 GROUP BY cu2.contrato HAVING SUM(COALESCE(cu2.pagado, 0)) > 0 AND (SUM(cu2.monto) - SUM(COALESCE(cu2.pagado, 0))) > 0)')
            ->andWhere(" ((c.fechaCreacion between  date_sub(current_timestamp(),c.vigencia,'month') and current_timestamp()) and 
                        c.vigenciaUltAnexo is null)
                        or ((c.vigenciaUltAnexo is not null and  c.fechaCreacionUltAnexo between  date_sub(current_timestamp(),c.vigenciaUltAnexo,'month') and current_timestamp()) )")
            ->getQuery()
            ->getScalarResult();
    }

    public function findCompletados(): array
    {
        return $this->em->createQueryBuilder()
            ->select(
                'MIN(m.nombre) AS materia',
                'c.folio',
                'c.fechaCreacion AS fechaContrato',
                'MAX(ca.fechaCreacion) AS fechaUltAnexo',
                'c.nombre AS cliente',
                'cerrador.nombre AS nombre_cerrador',
                'tramitador.nombre AS nombre_tramitador',
                'TIMESTAMPDIFF(MONTH, c.fechaCreacion, CURRENT_TIMESTAMP()) AS mesesSistema',
                'a.id as agenda_id'
            )
            ->from(Contrato::class, 'c')
            ->join('c.agenda', 'a')
            ->join('a.cuenta', 'cu')
            ->join('cu.cuentaMaterias', 'cm')
            ->join('cm.materia', 'm')
            ->join('a.abogado', 'cerrador')
            ->join('c.tramitador', 'tramitador')
            ->leftJoin('c.contratoAnexos', 'ca')
            ->andWhere('c.fechaDesiste IS NULL')
            ->andWhere('IDENTITY(a.status) NOT IN (13, 15)')
            ->andWhere('NOT EXISTS (SELECT cu2.id FROM App\Entity\Cuota cu2 WHERE cu2.contrato = c AND cu2.pagado IS NULL AND COALESCE(cu2.anular, 0) = 0)')
            ->andWhere(" ((c.fechaCreacion between  date_sub(current_timestamp(),c.vigencia,'month') and current_timestamp()) and 
                        c.vigenciaUltAnexo is null)
                        or ((c.vigenciaUltAnexo is not null and  c.fechaCreacionUltAnexo between  date_sub(current_timestamp(),c.vigenciaUltAnexo,'month') and current_timestamp()) )")
            ->groupBy('c.id, c.folio, c.fechaCreacion, c.nombre, cerrador.id, cerrador.nombre, tramitador.id, tramitador.nombre')
            
            ->getQuery()
            ->getScalarResult();
    }

    public function findFantasmas(): array
    {
        return $this->baseQueryBuilder()
            ->andWhere('NOT EXISTS (SELECT cu2.id FROM App\Entity\Cuota cu2 WHERE cu2.contrato = c AND COALESCE(cu2.anular, 0) = 0 AND cu2.pagado IS NOT NULL)')
             ->andWhere(" ((c.fechaCreacion between  date_sub(current_timestamp(),c.vigencia,'month') and current_timestamp()) and 
                        c.vigenciaUltAnexo is null)
                        or ((c.vigenciaUltAnexo is not null and  c.fechaCreacionUltAnexo between  date_sub(current_timestamp(),c.vigenciaUltAnexo,'month') and current_timestamp()) )")
            ->getQuery()
            ->getScalarResult();
    }
}
