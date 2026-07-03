<?php

namespace App\Service;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class S3Service
{
    private S3Client $client;
    private string $bucket;

    public function __construct(string $key, string $secret, string $region, string $bucket)
    {
        $this->bucket = $bucket;
        $this->client = new S3Client([
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => $key,
                'secret' => $secret,
            ],
        ]);
    }

    /**
     * Sube un archivo UploadedFile al bucket S3.
     * Devuelve la URL pública del archivo o false si falla.
     *
     * @param UploadedFile $file
     * @param string       $carpeta  Prefijo/carpeta dentro del bucket (ej: "contratos/2024")
     * @return string|false
     */
    public function subirArchivo(UploadedFile $file, string $carpeta = ''): string|false
    {
        $nombreOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension      = $file->guessExtension() ?? $file->getClientOriginalExtension();
        $clave          = ($carpeta ? rtrim($carpeta, '/') . '/' : '')
                        . $nombreOriginal . '_' . uniqid() . '.' . $extension;

        try {
            $resultado = $this->client->putObject([
                'Bucket'      => $this->bucket,
                'Key'         => $clave,
                'SourceFile'  => $file->getPathname(),
                'ContentType' => $file->getMimeType(),
                'ACL'         => 'private',
            ]);

            return (string) $resultado->get('ObjectURL');
        } catch (AwsException $e) {
            error_log('S3Service::subirArchivo error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sube un archivo a partir de su ruta en disco.
     *
     * @param string $rutaLocal   Ruta absoluta al archivo en el servidor
     * @param string $claveS3     Key destino dentro del bucket (ej: "docs/contrato.pdf")
     * @param string $contentType MIME type del archivo
     * @return string|false
     */
    public function subirDesdeRuta(string $rutaLocal, string $claveS3, string $contentType = 'application/octet-stream'): string|false
    {
        try {
            $resultado = $this->client->putObject([
                'Bucket'      => $this->bucket,
                'Key'         => $claveS3,
                'SourceFile'  => $rutaLocal,
                'ContentType' => $contentType,
                'ACL'         => 'private',
            ]);

            return (string) $resultado->get('ObjectURL');
        } catch (AwsException $e) {
            error_log('S3Service::subirDesdeRuta error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Genera una URL prefirmada para acceso temporal a un objeto privado.
     *
     * @param string $claveS3   Key del objeto en S3
     * @param int    $expiraEn  Segundos hasta que expira la URL (default: 1 hora)
     * @return string
     */
    public function urlPrefirmada(string $claveS3, int $expiraEn = 3600): string
    {
        $comando = $this->client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key'    => $claveS3,
        ]);

        $request = $this->client->createPresignedRequest($comando, '+' . $expiraEn . ' seconds');

        return (string) $request->getUri();
    }

    /**
     * Elimina un objeto del bucket.
     *
     * @param string $claveS3
     * @return bool
     */
    public function eliminar(string $claveS3): bool
    {
        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $claveS3,
            ]);
            return true;
        } catch (AwsException $e) {
            error_log('S3Service::eliminar error: ' . $e->getMessage());
            return false;
        }
    }
}
