<?php

namespace App\Command;

use App\Entity\Agenda;
use App\Service\Movatec;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MovatecCreaLeadCommand extends Command
{
    protected static $defaultName = 'app:movatec-crea-lead';
    protected static $defaultDescription = 'Add a short description for your command';
    private $container;

    public function __construct(ContainerInterface $container){
        $this->container=$container;   
        parent::__construct();
    }
    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
              ->addOption('agendaId', null, InputOption::VALUE_OPTIONAL, 'id de la agenda para enviar a movatec');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $agendaId = $input->getOption('agendaId');
        $em=$this->container->get('doctrine');

        $movatec=new Movatec($this->container);
        $agenda=$em->getRepository(Agenda::class)->find($agendaId);


        $io->note(sprintf('Enviando a movatec'));
        $token=$movatec->login();
        $respuesta=$movatec->create_leads($token,$agenda->getId(),"",$agenda->getFechaCarga()->format('Y-m-d'),$agenda->getTelefonoCliente(),$agenda->getId());                  
        $io->note(sprintf('Enviando a movatec: %s', $respuesta));
        

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return 0;
    }
}
