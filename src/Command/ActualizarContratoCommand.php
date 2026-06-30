<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ActualizarContratoCommand extends Command
{
    protected static $defaultName = 'actualizar-contrato';
    protected static $defaultDescription = 'Ejecuta los procedimientos almacenados de actualización para un contrato';

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('contrato_id', InputArgument::REQUIRED, 'ID del contrato a actualizar');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $contratoId = (int) $input->getArgument('contrato_id');

        $conn = $this->container->get('doctrine')->getConnection();
        $conn->executeQuery("CALL sp_actualizar_vip({$contratoId})");
        $conn->executeQuery("CALL sp_actualizar_dias_morosidad({$contratoId})");

        return true;
    }
}
