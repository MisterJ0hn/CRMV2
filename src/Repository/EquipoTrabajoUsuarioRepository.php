<?php

namespace App\Repository;

use App\Entity\EquipoTrabajoUsuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EquipoTrabajoUsuario>
 *
 * @method EquipoTrabajoUsuario|null find($id, $lockMode = null, $lockVersion = null)
 * @method EquipoTrabajoUsuario|null findOneBy(array $criteria, array $orderBy = null)
 * @method EquipoTrabajoUsuario[]    findAll()
 * @method EquipoTrabajoUsuario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EquipoTrabajoUsuarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipoTrabajoUsuario::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(EquipoTrabajoUsuario $entity, bool $flush = true): void
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
    public function remove(EquipoTrabajoUsuario $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Retorna el usuario_id del cobrador asignado a un contrato,
     * según el lote del contrato y el vencimiento actual de su cuota pendiente.
     */
    public function findCobradorByContrato(int $contratoId): ?int
    {
        $sql = "
            SELECT etv.cobrador_id
            FROM contrato co
            JOIN cuota c ON c.contrato_id = co.id
                AND (c.monto > c.pagado + (SELECT cfg.deuda_minima FROM configuracion cfg WHERE cfg.id = 1) OR c.pagado IS NULL)
                AND (c.anular IS NULL OR c.anular = 0)
            LEFT JOIN vencimiento v ON (TO_DAYS(NOW()) - TO_DAYS(c.fecha_pago)) BETWEEN v.val_min AND COALESCE(v.val_max, 1000000)
            LEFT JOIN vw_equipo_trabajo_vencimiento etv ON etv.contrato_id = co.id AND etv.vencimiento_id = v.id
            WHERE co.id = :contrato_id
            ORDER BY c.fecha_pago ASC
            LIMIT 1
        ";

        $conn = $this->getEntityManager()->getConnection();
        $result = $conn->fetchOne($sql, ['contrato_id' => $contratoId]);

        return $result !== false ? (int) $result : null;
    }
    /**
     * Retorna un array de vencimiento_id del cobrador,
     * según el lote del contrato y el vencimiento actual de su cuota pendiente.
     */
    public function findVencimientoByCobrador(int $cobradorId)
    {
        $query = $this->createQueryBuilder('etu')
            ->select('IDENTITY(etv.vencimiento) AS vencimiento_id')
            ->join('etu.equipoTrabajo', 'et')
            ->join('et.equipoTrabajoVencimientos', 'etv')
            ->where('etu.usuario = :cobradorId')
            ->setParameter('cobradorId', $cobradorId)
            ->getQuery()
            ->getResult();

            return array_column($query, 'vencimiento_id');
    }
}
