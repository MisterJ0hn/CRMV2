<?php

namespace App\Service;

use App\Entity\EstadoDiario;
use App\Entity\EstadoDiarioOrigen;
use App\Repository\JurisdiccionRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class EstadoDiarioImportService
{
    private const CAMPO_POR_ENCABEZADO = [
        'rol' => 'rol',
        'rit' => 'rol',
        'rol interno' => 'rol',
        'n ingreso' => 'rol',
        'rol unico' => 'rolUnico',
        'ruc' => 'rolUnico',
        'fecha ingreso' => 'fechaIngreso',
        'caratulado' => 'caratulado',
        'tribunal' => 'tribunal',
        'estado' => 'estado',
        'tipo recurso' => 'tipoCausa',
        'tipo causa' => 'tipoCausa',
        'ubicacion' => 'ubicacion',
        'fecha ubicacion' => 'fechaUbicacion',
        'corte' => 'corte',
    ];

    private $entityManager;
    private $jurisdiccionRepository;

    public function __construct(EntityManagerInterface $entityManager, JurisdiccionRepository $jurisdiccionRepository)
    {
        $this->entityManager = $entityManager;
        $this->jurisdiccionRepository = $jurisdiccionRepository;
    }

    /**
     * Extrae rut, fecha y guid desde el nombre del archivo.
     * Formato esperado: EstadoDiario{RUT}-{DV}_{DD}_{MM}_{AAAA}-{guid}.xlsx
     * Tolera el sufijo " (1)", " (2)", etc. que agrega el navegador cuando el
     * archivo se descarga duplicado, ej: EstadoDiario16952077-1_23_07_2024 (1)-66a1134a5ff38.xlsx
     *
     * @return array{rut:?string,fecha:?\DateTimeInterface,guid:?string}
     */
    public function parseNombreArchivo(string $nombreSinExtension): array
    {
        // Quita el sufijo "(1)", "(2)", etc. que agregan los navegadores en descargas
        // duplicadas, tolerando cualquier tipo de espacio (normal, non-breaking, etc.)
        // o ninguno, antes del paréntesis.
        $nombreLimpio = preg_replace('/[\s\x{00A0}]*\(\d+\)(?=-)/u', '', $nombreSinExtension);

        $patron = '/^EstadoDiario(?<rut>\d{1,9}-[\dkK])_(?<dd>\d{2})_(?<mm>\d{2})_(?<yyyy>\d{4})-(?<guid>[a-zA-Z0-9]+)$/';

        if (!preg_match($patron, $nombreLimpio, $m)) {
            return ['rut' => null, 'fecha' => null, 'guid' => null];
        }

        $fecha = \DateTime::createFromFormat('d-m-Y H:i:s', $m['dd'] . '-' . $m['mm'] . '-' . $m['yyyy'] . ' 00:00:00');

        return [
            'rut' => $m['rut'],
            'fecha' => $fecha ?: null,
            'guid' => $m['guid'],
        ];
    }

    /**
     * Recorre todas las hojas del Excel y crea los EstadoDiario asociados al origen dado.
     *
     * @return int cantidad de filas importadas
     */
    public function importar(string $rutaArchivo, EstadoDiarioOrigen $origen): int
    {
        $spreadsheet = IOFactory::load($rutaArchivo);
        $total = 0;

        foreach ($spreadsheet->getSheetNames() as $nombreHoja) {
            $hoja = $spreadsheet->getSheetByName($nombreHoja);
            $highestRow = $hoja->getHighestRow();
            $highestColumn = $hoja->getHighestColumn();

            if ($highestRow < 2) {
                continue;
            }

            $mapaColumnas = $this->mapearEncabezados($hoja, $highestColumn);

            if (empty($mapaColumnas)) {
                continue;
            }

            $jurisdiccion = $this->jurisdiccionRepository->findOrCreateByNombre($nombreHoja);

            for ($fila = 2; $fila <= $highestRow; $fila++) {
                $datos = [];
                foreach ($mapaColumnas as $columna => $campo) {
                    $celda = $hoja->getCell($columna . $fila);
                    $datos[$campo] = $this->valorCelda($celda);
                }

                if (empty($datos['rol'])) {
                    continue;
                }

                $estadoDiario = new EstadoDiario();
                $estadoDiario->setEstadoDiarioOrigen($origen);
                $estadoDiario->setJurisdiccion($jurisdiccion);
                $estadoDiario->setRol($datos['rol'] ?? null);
                $estadoDiario->setRolUnico($datos['rolUnico'] ?? null);
                $estadoDiario->setFechaIngreso($this->parseFecha($datos['fechaIngreso'] ?? null));
                $estadoDiario->setCaratulado($datos['caratulado'] ?? null);
                $estadoDiario->setTribunal($datos['tribunal'] ?? null);
                $estadoDiario->setEstado($datos['estado'] ?? null);
                $estadoDiario->setTipoCausa($datos['tipoCausa'] ?? null);
                $estadoDiario->setUbicacion($datos['ubicacion'] ?? null);
                $estadoDiario->setFechaUbicacion($this->parseFecha($datos['fechaUbicacion'] ?? null));
                $estadoDiario->setCorte($datos['corte'] ?? null);

                $this->entityManager->persist($estadoDiario);
                $total++;
            }
        }

        $this->entityManager->flush();

        return $total;
    }

    /**
     * @return array<string,string> columna Excel => nombre de campo de la entidad
     */
    private function mapearEncabezados($hoja, string $highestColumn): array
    {
        $mapa = [];
        foreach ($this->rangoColumnas('A', $highestColumn) as $columna) {
            $texto = $this->normalizarEncabezado((string) $hoja->getCell($columna . '1')->getValue());
            if (isset(self::CAMPO_POR_ENCABEZADO[$texto])) {
                $mapa[$columna] = self::CAMPO_POR_ENCABEZADO[$texto];
            }
        }

        return $mapa;
    }

    private function rangoColumnas(string $desde, string $hasta): array
    {
        $columnas = [];
        for ($col = $desde; $col <= $hasta; $col++) {
            $columnas[] = $col;
        }

        return $columnas;
    }

    private function normalizarEncabezado(string $texto): string
    {
        $texto = mb_strtolower(trim($texto));
        $texto = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', '°' => ' ',
        ]);
        $texto = preg_replace('/[^a-z0-9 ]/', ' ', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);

        return trim($texto);
    }

    private function valorCelda(Cell $celda)
    {
        $valor = $celda->getValue();

        if ($valor instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
            $valor = $valor->getPlainText();
        }

        return is_string($valor) ? trim($valor) : $valor;
    }

    private function parseFecha($valor): ?\DateTimeInterface
    {
        if (empty($valor)) {
            return null;
        }

        if (is_numeric($valor)) {
            return ExcelDate::excelToDateTimeObject($valor);
        }

        $fecha = \DateTime::createFromFormat('d/m/Y', trim((string) $valor));

        return $fecha ?: null;
    }
}
