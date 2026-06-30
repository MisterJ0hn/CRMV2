<?php

namespace App\Command;

use App\Entity\Agenda;
use App\Repository\ConfiguracionRepository;
use App\Service\Adereso;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AderesoReasignarConversacionCommand extends Command
{
    protected static $defaultName = 'adereso:reasignar-conversacion';
    protected static $defaultDescription = 'Add a short description for your command';
    private $container;
    private $configuracionRepository;

    public function __construct(ContainerInterface $container,ConfiguracionRepository $configuracionRepository){
        $this->container=$container;   
        $this->configuracionRepository = $configuracionRepository;
        parent::__construct();
    }
    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
              ->addOption('agendaId', null, InputOption::VALUE_OPTIONAL, 'id de la agenda para reasignar conversacion con cliente');
              
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $agendaId = $input->getOption('agendaId');
        $em=$this->container->get('doctrine');
        $entityManager = $em->getManager();        
        $adereso = new Adereso($this->container,$this->configuracionRepository);
        $agenda=$em->getRepository(Agenda::class)->find($agendaId);


        $io->note(sprintf('Enviando a Adereso'));        
        $respuesta  = $adereso->iniciar_conversacion($agenda->getId());

        $ticket = json_decode($respuesta,true);
        if($ticket['status_code']!=null){
         
            $agenda->setAderesoStatusCodeReasignacion($ticket['status_code']);

            $entityManager->persist($agenda);
            $entityManager->flush();
        }


        $io->note(sprintf('Enviando a movatec: %s', $respuesta));
    

        return 0;
    }
}
