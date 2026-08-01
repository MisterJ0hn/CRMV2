<?php

namespace App\Command;

use App\Repository\ConfiguracionRepository;
use App\Repository\CuotaRepository;
use App\Service\VirtualPos;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Reporte (solo lectura) del descuadre entre VirtualPos y el CRM: cuotas de pago
 * automático que están atrasadas y sin pago —o con pago parcial— en el CRM, pero
 * que en VirtualPos figuran como "pagado". El resultado queda en un Excel.
 *
 * No escribe nada en la base: para registrar esos pagos en el CRM está
 * app:actualizar-pago-virtualpos.
 */
class CuotasPagadasVirtualposCommand extends Command
{
    protected static $defaultName = 'app:cuotas-pagadas-virtualpos';
    protected static $defaultDescription = 'Genera un Excel con las cuotas atrasadas que en VirtualPos figuran pagadas pero en el CRM siguen sin pago';

    private const ENCABEZADOS = ['Contrato', 'Folio', 'Cliente', 'Suscripción', 'Cuota', 'Fecha pago', 'Días atraso',
                                'Monto CRM', 'Pagado CRM', 'Invoice ID', 'Monto VirtualPos', 'Fecha pago VirtualPos',
                                'Comprobante VirtualPos'];

    private CuotaRepository $cuotaRepository;
    private ConfiguracionRepository $configuracionRepository;
    private ParameterBagInterface $params;

    public function __construct(CuotaRepository $cuotaRepository,
                                ConfiguracionRepository $configuracionRepository,
                                ParameterBagInterface $params)
    {
        $this->cuotaRepository = $cuotaRepository;
        $this->configuracionRepository = $configuracionRepository;
        $this->params = $params;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('contratoId', null, InputOption::VALUE_OPTIONAL, 'Revisar solo las cuotas de este contrato')
            ->addOption('todas', null, InputOption::VALUE_NONE, 'Incluir contratos cuya suscripción no está ACTIVA')
            ->addOption('archivo', null, InputOption::VALUE_OPTIONAL, 'Ruta del Excel a generar (por defecto var/reportes/cuotas-pagadas-virtualpos-<fecha>.xlsx)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $cuotas = $this->cuotaRepository->findCuotasAtrasadasPagoAutomatico(
            $input->getOption('contratoId'),
            !$input->getOption('todas')
        );

        $io->text(sprintf('%d cuotas atrasadas sin pago (o con pago parcial) para revisar en VirtualPos.', count($cuotas)));

        if (!$cuotas) {
            $io->success('No hay cuotas para revisar.');

            return 0;
        }

        $configuracion = $this->configuracionRepository->find(1);
        $virtualPos = new VirtualPos($configuracion->getVirtualPosApiKey(),
                                    $configuracion->getVirtualPosSecretKey(),
                                    $configuracion->getVirtualPosPlan(),
                                    $configuracion->getVirtualPosUrl());

        $hoy = new \DateTime(date('Y-m-d'));
        $filas = [];
        $errores = [];
        $progreso = $io->createProgressBar(count($cuotas));

        foreach ($cuotas as $cuota) {
            try {
                $respuesta = $virtualPos->recuperarCuota($cuota->getInvoiceId());
                $charge = $respuesta['response']['charge'] ?? [];

                if (($charge['status'] ?? '') === 'pagado') {
                    $orden = $charge['payment']['order'] ?? [];
                    $contrato = $cuota->getContrato();
                    $cliente = $contrato->getCliente();

                    $filas[] = [
                        $contrato->getId(),
                        $contrato->getFolio(),
                        $cliente ? $cliente->getNombre() : '',
                        $contrato->getEstadoSuscripcion(),
                        $cuota->getNumero(),
                        $cuota->getFechaPago()->format('Y-m-d'),
                        $hoy->diff($cuota->getFechaPago())->days,
                        $cuota->getMonto(),
                        $cuota->getPagado() ?? 0,
                        $cuota->getInvoiceId(),
                        $orden['amount'] ?? ($charge['amount'] ?? null),
                        isset($orden['authorized_at']) ? substr((string) $orden['authorized_at'], 0, 19) : '',
                        $orden['uuid'] ?? '',
                    ];
                }
            } catch (\Exception $e) {
                $errores[] = sprintf('cuota id %d (invoice %s): %s', $cuota->getId(), $cuota->getInvoiceId(), $e->getMessage());
            }

            $progreso->advance();
        }

        $progreso->finish();
        $io->newLine(2);

        if ($errores) {
            $io->warning(sprintf('%d cuotas no se pudieron consultar en VirtualPos:', count($errores)));
            $io->listing(array_slice($errores, 0, 20));
        }

        if (!$filas) {
            $io->success('Ninguna de las cuotas atrasadas figura pagada en VirtualPos: no se genera Excel.');

            return 0;
        }

        $archivo = $this->generarExcel($filas, $input->getOption('archivo'));

        $io->table(self::ENCABEZADOS, $filas);
        $io->text(sprintf('Monto total pagado en VirtualPos y no registrado en el CRM: $%s',
            number_format(array_sum(array_column($filas, 10)), 0, ',', '.')));
        $io->success(sprintf('%d cuotas descuadradas. Excel generado en %s', count($filas), $archivo));

        return 0;
    }

    private function generarExcel(array $filas, ?string $archivo): string
    {
        if (!$archivo) {
            $directorio = $this->params->get('kernel.project_dir').'/var/reportes';

            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }

            $archivo = $directorio.'/cuotas-pagadas-virtualpos-'.date('Ymd-His').'.xlsx';
        }

        $spreadSheet = new Spreadsheet();
        $sheet = $spreadSheet->getActiveSheet();
        $sheet->setTitle('Pagadas VP sin pago CRM');
        $sheet->fromArray(self::ENCABEZADOS, null, 'A1');

        $fila = 2;
        foreach ($filas as $datos) {
            $sheet->fromArray($datos, null, 'A'.$fila);
            $fila++;
        }

        $ultimaColumna = chr(ord('A') + count(self::ENCABEZADOS) - 1);
        $sheet->getStyle('A1:'.$ultimaColumna.'1')->getFont()->setBold(true);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:'.$ultimaColumna.($fila - 1));

        foreach (range('A', $ultimaColumna) as $columna) {
            $sheet->getColumnDimension($columna)->setAutoSize(true);
        }

        (new Xlsx($spreadSheet))->save($archivo);

        return $archivo;
    }
}
