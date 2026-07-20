<?php

namespace App\Command;

use App\Repository\EstadoDiarioAgendaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twilio\Rest\Client;

class EnviarMensajesEstadoDiarioAgendaCommand extends Command
{
    protected static $defaultName        = 'app:enviar-mensajes-estado-diario-agenda';
    protected static $defaultDescription = 'Envía por Twilio los recordatorios de EstadoDiarioAgenda pendientes (fecha_hora ya cumplida) y los marca como enviados para no reenviarlos';

    private EntityManagerInterface $em;
    private EstadoDiarioAgendaRepository $agendaRepository;
    private string $twilioAccountSid;
    private string $twilioAuthToken;
    private string $twilioFromNumber;

    public function __construct(
        EntityManagerInterface $em,
        EstadoDiarioAgendaRepository $agendaRepository,
        string $twilioAccountSid,
        string $twilioAuthToken,
        string $twilioFromNumber
    ) {
        $this->em               = $em;
        $this->agendaRepository = $agendaRepository;
        $this->twilioAccountSid = $twilioAccountSid;
        $this->twilioAuthToken  = $twilioAuthToken;
        $this->twilioFromNumber = $twilioFromNumber;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $pendientes = $this->agendaRepository->findPendientesEnvio();

        if (empty($pendientes)) {
            $io->text('No hay recordatorios pendientes de envío.');

            return 0;
        }

        $client = new Client($this->twilioAccountSid, $this->twilioAuthToken);

        $enviados = 0;
        $errores  = 0;

        foreach ($pendientes as $agenda) {
            $usuario  = $agenda->getUsuarioRegistro();
            $telefono = $this->formatearTelefono($usuario->getTelefono());

            if (!$telefono) {
                $agenda->setMensajeError('Usuario sin teléfono válido');
                $this->em->flush();

                $io->warning(sprintf('Agenda #%d: usuario sin teléfono válido, se omite.', $agenda->getId()));
                $errores++;
                continue;
            }

            try {
               
                $message = $client->messages
                ->create("whatsapp:$telefono", // to
                    array(
                    "from" => "whatsapp:".$this->twilioFromNumber,
                    //"contentSid" => "HX5f91b49fd936e355e8ca63c98b17d6e7",
                    "contentSid" => "HXb9ce70f637853bfb9cfc21c7dc546034",
                    "contentVariables" => 
                    '{"first_name":"'.$agenda->getUsuarioRegistro()->getNombre().' Rol: '.$agenda->getEstadoDiario()->getRol().' Caratulado: '.$agenda->getEstadoDiario()->getCaratulado().'" }',
                    "body" => $agenda->getDetalle()
                    )
                );
                // Se marca enviado=true inmediatamente y se hace flush individual
                // para que, aunque el proceso se corte a mitad de camino, los
                // registros ya enviados no vuelvan a procesarse en la próxima corrida.
                $agenda->setEnviado(true);
                $agenda->setFechaEnvio(new \DateTime());
                $agenda->setMensajeError(null);
                $this->em->flush();

                $io->text(sprintf('Agenda #%d: enviado a %s', $agenda->getId(), $telefono));
                $enviados++;
            } catch (\Throwable $e) {
                $agenda->setMensajeError($e->getMessage());
                $this->em->flush();

                $io->error(sprintf('Agenda #%d: error al enviar - %s', $agenda->getId(), $e->getMessage()));
                $errores++;
            }
        }

        $io->table(['Pendientes', 'Enviados', 'Con error'], [[count($pendientes), $enviados, $errores]]);
        $io->success('Proceso de envío finalizado.');

        return 0;
    }

    private function formatearTelefono(?string $telefono): ?string
    {
        if (empty($telefono)) {
            return null;
        }

        $telefono = trim($telefono);

        if (strpos($telefono, '+') === 0) {
            return $telefono;
        }

        if (strlen($telefono) >= 9) {
            return '+56' . $telefono;
        }

        return '+569' . $telefono;
    }
}
