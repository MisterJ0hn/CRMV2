<?php

namespace App\Command;

use App\Entity\EstadoDiarioOrigen;
use App\Repository\EstadoDiarioOrigenRepository;
use App\Service\EstadoDiarioImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportarEstadoDiarioCommand extends Command
{
    protected static $defaultName        = 'app:importar-estado-diario';
    protected static $defaultDescription = 'Importa los Excel de Estado Diario que se encuentren en la carpeta configurada y que aún no hayan sido cargados';

    private EntityManagerInterface $em;
    private EstadoDiarioImportService $importService;
    private EstadoDiarioOrigenRepository $origenRepository;
    private string $carpetaOrigen;
    private string $carpetaDestino;

    public function __construct(
        EntityManagerInterface $em,
        EstadoDiarioImportService $importService,
        EstadoDiarioOrigenRepository $origenRepository,
        string $carpetaOrigen,
        string $carpetaDestino
    ) {
        $this->em               = $em;
        $this->importService    = $importService;
        $this->origenRepository = $origenRepository;
        $this->carpetaOrigen    = $carpetaOrigen;
        $this->carpetaDestino   = $carpetaDestino;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!is_dir($this->carpetaOrigen)) {
            $io->error(sprintf('La carpeta de origen no existe: %s', $this->carpetaOrigen));

            return 1;
        }

        if (!is_dir($this->carpetaDestino)) {
            mkdir($this->carpetaDestino, 0755, true);
        }

        $archivos = array_merge(
            glob(rtrim($this->carpetaOrigen, '/\\') . DIRECTORY_SEPARATOR . '*.xlsx'),
            glob(rtrim($this->carpetaOrigen, '/\\') . DIRECTORY_SEPARATOR . '*.xls')
        );

        $importados = 0;
        $omitidos   = 0;
        $noReconocidos = 0;
        $errores    = 0;

        foreach ($archivos as $rutaOrigen) {
            $nombreArchivo = basename($rutaOrigen);

            if (0 === strpos($nombreArchivo, '~$')) {
                continue;
            }

            $nombreSinExtension = pathinfo($rutaOrigen, PATHINFO_FILENAME);
            $datosNombre = $this->importService->parseNombreArchivo($nombreSinExtension);

            if (!$datosNombre['guid']) {
                $io->warning(sprintf('Nombre de archivo no reconocido, se omite: %s', $nombreArchivo));
                $noReconocidos++;
                continue;
            }

            if ($this->origenRepository->existeGuid($datosNombre['guid'])) {
                $io->text(sprintf('Ya importado (guid %s): %s', $datosNombre['guid'], $nombreArchivo));
                $omitidos++;
                continue;
            }

            try {
                $nuevoNombre = $nombreSinExtension . '-' . uniqid() . '.' . pathinfo($rutaOrigen, PATHINFO_EXTENSION);
                $rutaDestino = rtrim($this->carpetaDestino, '/\\') . DIRECTORY_SEPARATOR . $nuevoNombre;

                if (!copy($rutaOrigen, $rutaDestino)) {
                    throw new \RuntimeException('No se pudo copiar el archivo a la carpeta de importación');
                }

                $origen = new EstadoDiarioOrigen();
                $origen->setNombreArchivo($nombreSinExtension);
                $origen->setUrl($rutaDestino);
                $origen->setRut($datosNombre['rut']);
                $origen->setFecha($datosNombre['fecha']);
                $origen->setGuid($datosNombre['guid']);
                $origen->setUsuarioCarga(null);
                $origen->setFechaCarga(new \DateTime());

                $this->em->persist($origen);
                $this->em->flush();

                $filas = $this->importService->importar($rutaDestino, $origen);

                $io->text(sprintf('Importado: %s -> %d filas', $nombreArchivo, $filas));
                $importados++;
            } catch (\Throwable $e) {
                $io->error(sprintf('Error importando %s: %s', $nombreArchivo, $e->getMessage()));
                $errores++;
            }
        }

        $io->table(
            ['Encontrados', 'Importados', 'Ya existentes', 'No reconocidos', 'Con error'],
            [[count($archivos), $importados, $omitidos, $noReconocidos, $errores]]
        );

        $io->success('Importación de Estado Diario finalizada.');

        return 0;
    }
}
