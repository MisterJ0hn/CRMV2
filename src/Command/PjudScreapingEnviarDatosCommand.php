<?php

namespace App\Command;

use App\Entity\Causa;
use App\Service\PjudScraping;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PjudScreapingEnviarDatosCommand extends Command
{
    protected static $defaultName = 'pjudScreaping:enviar-datos';
    protected static $defaultDescription = 'Add a short description for your command';

    private $scraping;
   

    public function __construct(PjudScraping $scraping)
    {
        parent::__construct();
        $this->scraping = $scraping;
       
    }

    protected function configure(): void
    {
            $this
            ->setDescription('Procesa scraping judicial en background')
            ->addArgument('numero', InputArgument::REQUIRED)
            ->addArgument('letra', InputArgument::REQUIRED)
            ->addArgument('anio', InputArgument::REQUIRED)
            ->addArgument('competencia', InputArgument::REQUIRED)
            ->addArgument('corte', InputArgument::REQUIRED)
            ->addArgument('tribunal', InputArgument::REQUIRED)
            ->addArgument('causaId', InputArgument::REQUIRED)
            ->addArgument('estado', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
      
        $numero = $input->getArgument('numero');
        $letra = $input->getArgument('letra');
        $anio = (int)$input->getArgument('anio');
        $competencia = $input->getArgument('competencia');
        $corte = $input->getArgument('corte');
        $tribunal = $input->getArgument('tribunal');
        $causaId = (int)$input->getArgument('causaId');
        $estado = (int)$input->getArgument('estado');

        $output->writeln("Iniciando scraping...");

        $token = $this->scraping->login();

        if (!$token) {
            $output->writeln("Error al obtener token");
            return 0;
        }

            $this->scraping->enviarDatos(
                $token,
                $numero,
                $letra,
                $anio,
                $competencia,
                $corte,
                $tribunal,
                $causaId,
                $estado
            );
           
        

        $output->writeln("Scraping finalizado.");

        return 1;
    }
}
