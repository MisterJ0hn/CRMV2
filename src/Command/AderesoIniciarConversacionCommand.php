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

class AderesoIniciarConversacionCommand extends Command
{
    protected static $defaultName = 'adereso:iniciar-conversacion';
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
              ->addOption('agendaId', null, InputOption::VALUE_OPTIONAL, 'id de la agenda para iniciar conversacion con cliente');
              
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
            if($ticket['status_code']==200 || $ticket['status_code']==207){
                $agenda->setAderesoTicketId($ticket['first_hsm_ticket_id']);
            }
            $agenda->setAderesoStatusCode($ticket['status_code']);

            $entityManager->persist($agenda);
            $entityManager->flush();
        }


        $io->note(sprintf('Enviando a movatec: %s', $respuesta));
    

        return 0;
    }
}
