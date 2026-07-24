<?php

namespace App\Command;

use App\Entity\UserActivityLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ArchivarActivityLogCommand extends Command
{
    protected static $defaultName        = 'app:archivar-activity-log';
    protected static $defaultDescription = 'Archiva en CSV los registros de actividad con más de 60 días y los elimina de la BD';

    private const DAYS_TO_KEEP = 53;
    private const BATCH_SIZE   = 500;

    private EntityManagerInterface $em;
    private string $activityLogsDir;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em              = $em;
        $this->activityLogsDir = __DIR__ . '/../../var/activity_logs';
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simula sin escribir ni borrar registros');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = (bool) $input->getOption('dry-run');
        $corte  = new \DateTime('-' . self::DAYS_TO_KEEP . ' days');
        $corte->setTime(0, 0, 0);

        $output->writeln(sprintf(
            '[%s] Archivando registros anteriores a %s%s',
            date('Y-m-d H:i:s'),
            $corte->format('Y-m-d'),
            $dryRun ? ' [DRY-RUN — sin cambios en disco ni BD]' : ''
        ));

        if (!$dryRun && !is_dir($this->activityLogsDir)) {
            mkdir($this->activityLogsDir, 0755, true);
        }

        $conn    = $this->em->getConnection();
        $handles = [];   // 'YYYY-MM' => resource
        $total   = 0;
        $offset  = 0;

        $qb = $this->em->createQueryBuilder()
            ->select('l')
            ->from(UserActivityLog::class, 'l')
            ->leftJoin('l.usuario', 'u')
            ->where('l.fechaRegistro < :corte')
            ->setParameter('corte', $corte)
            ->orderBy('l.fechaRegistro', 'ASC')
            ->setMaxResults(self::BATCH_SIZE);

        while (true) {
            // En dry-run avanzamos el offset; en producción siempre desde 0 porque
            // los registros del batch anterior ya fueron eliminados.
            $qb->setFirstResult($dryRun ? $offset : 0);

            /** @var UserActivityLog[] $registros */
            $registros = $qb->getQuery()->getResult();

            if (empty($registros)) {
                break;
            }

            $batchIds = [];

            foreach ($registros as $log) {
                $mes = $log->getFechaRegistro()->format('Y-m');

                if (!$dryRun) {
                    if (!isset($handles[$mes])) {
                        $archivo = $this->activityLogsDir . '/' . $mes . '.csv';
                        $esNuevo = !file_exists($archivo);
                        $fh      = fopen($archivo, 'a');
                        if ($esNuevo) {
                            fputcsv($fh, [
                                'fecha_registro', 'usuario_id', 'nombre', 'correo',
                                'metodo', 'ruta', 'controlador',
                                'ip', 'status_code', 'ciudad', 'pais',
                            ]);
                        }
                        $handles[$mes] = $fh;
                    }

                    $usuario = $log->getUsuario() ? $log->getUsuario() : null;
                    if($usuario!=null){
                        fputcsv($handles[$mes], [
                            $log->getFechaRegistro()->format('Y-m-d H:i:s'),
                            $usuario ? $usuario->getId() : null,
                            $usuario->getNombre() ? $usuario->getNombre() : null,
                            $usuario->getCorreo() ? $usuario->getCorreo() : null,
                            $log->getMetodo(),
                            $log->getRuta(),
                            $log->getControlador(),
                            $log->getIp(),
                            $log->getStatusCode(),
                            $log->getCiudad(),
                            $log->getPais(),
                        ]);
                    }
                }

                $batchIds[] = $log->getId();
                $total++;
            }

            if (!$dryRun && !empty($batchIds)) {
                $placeholders = implode(',', array_fill(0, count($batchIds), '?'));
                $conn->executeQuery(
                    "DELETE FROM user_activity_log WHERE id IN ($placeholders)",
                    array_values($batchIds)
                );
            }

            $this->em->clear();

            $output->writeln(sprintf('  Procesados %d registros...', $total), OutputInterface::VERBOSITY_VERBOSE);

            if (count($registros) < self::BATCH_SIZE) {
                break;
            }

            $offset += self::BATCH_SIZE;
        }

        foreach ($handles as $fh) {
            fclose($fh);
        }

        $archivos = array_keys($handles);

        $output->writeln(sprintf(
            '[%s] %s: %d registros — %d archivos CSV (%s).',
            date('Y-m-d H:i:s'),
            $dryRun ? 'Simulación completada' : 'Archivado completado',
            $total,
            count($archivos),
            $archivos ? implode(', ', $archivos) : 'ninguno'
        ));

        return 0;
    }
}
