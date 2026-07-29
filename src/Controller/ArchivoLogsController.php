<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Listado y descarga de los CSV generados por los comandos de archivado
 * (app:archivar-activity-log y app:archivar-movatec-log).
 *
 * @Route("/archivo_logs")
 */
class ArchivoLogsController extends AbstractController
{
    /**
     * Orígenes permitidos. La clave es lo que viaja por URL; nunca se concatena
     * el parámetro recibido a una ruta de disco.
     */
    private const ORIGENES = [
        'actividad' => [
            'dir'    => 'var/activity_logs',
            'titulo' => 'Actividad de Usuarios',
            'badge'  => 'badge-primary',
            'icono'  => 'fas fa-history',
        ],
        'movatec'   => [
            'dir'    => 'var/movatec_logs',
            'titulo' => 'Movatec',
            'badge'  => 'badge-success',
            'icono'  => 'fas fa-exchange-alt',
        ],
    ];

    /**
     * @Route("/", name="archivo_logs_index", methods={"GET"})
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('view', 'Actividad_Usuarios');

        $archivos = [];

        foreach (self::ORIGENES as $origen => $config) {
            $dir = $this->rutaOrigen($origen);

            if (!is_dir($dir)) {
                continue;
            }

            foreach (glob($dir . '/*.csv') as $ruta) {
                $nombre = basename($ruta);

                // Sólo los generados por los comandos: YYYY-MM.csv
                if (!preg_match('/^\d{4}-\d{2}\.csv$/', $nombre)) {
                    continue;
                }

                $archivos[] = [
                    'origen'      => $origen,
                    'titulo'      => $config['titulo'],
                    'badge'       => $config['badge'],
                    'icono'       => $config['icono'],
                    'archivo'     => $nombre,
                    'mes'         => substr($nombre, 0, 7),
                    'tamano'      => $this->formatearTamano(filesize($ruta)),
                    'modificado'  => (new \DateTime())->setTimestamp(filemtime($ruta)),
                ];
            }
        }

        // Más recientes primero; a igual mes, agrupados por origen.
        usort($archivos, function ($a, $b) {
            return [$b['mes'], $a['origen']] <=> [$a['mes'], $b['origen']];
        });

        return $this->render('archivo_logs/index.html.twig', [
            'archivos' => $archivos,
            'origenes' => self::ORIGENES,
        ]);
    }

    /**
     * @Route(
     *     "/descargar/{origen}/{archivo}",
     *     name="archivo_logs_descargar",
     *     methods={"GET"},
     *     requirements={"origen"="actividad|movatec", "archivo"="\d{4}-\d{2}\.csv"}
     * )
     */
    public function descargar(string $origen, string $archivo): Response
    {
        $this->denyAccessUnlessGranted('view', 'Actividad_Usuarios');

        $dir  = realpath($this->rutaOrigen($origen));
        $ruta = $dir ? realpath($dir . '/' . $archivo) : false;

        // El archivo resuelto debe existir y estar dentro del directorio del origen.
        if (!$ruta || strpos($ruta, $dir . DIRECTORY_SEPARATOR) !== 0 || !is_file($ruta)) {
            throw $this->createNotFoundException('Archivo no encontrado.');
        }

        $nombreDescarga = sprintf('%s_%s', $origen, $archivo);

        return $this->file($ruta, $nombreDescarga, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

    private function rutaOrigen(string $origen): string
    {
        return $this->getParameter('kernel.project_dir') . '/' . self::ORIGENES[$origen]['dir'];
    }

    private function formatearTamano(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }
}
