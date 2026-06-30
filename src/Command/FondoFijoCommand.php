<?php

namespace App\Command;
use App\Entity\Usuario;
use App\Entity\Configuracion;
use App\Entity\Rendicion;
use App\Entity\RendicionTipo;
use App\Entity\RendicionEstado;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FondoFijoCommand extends Command
{
    protected static $defaultName = 'fondo-fijo';
    private $mailer;
    private $twig;
    private $container;

    public function __construct(\Swift_Mailer $mailer,Environment $twig,ContainerInterface $container){
        $this->mailer=$mailer;
        $this->twig=$twig;
        $this->container=$container;   
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Genera fondos fijos para los trabajadores configurados')
        
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $entityManager = $this->container->get('doctrine')->getManager();
        $em=$this->container->get('doctrine');

        $config=$em->getRepository(Configuracion::class)->find(1);


        if($config->getDiaFondoFijo()==date("d")){
            $trabajadores=$em->getRepository(Usuario::class)->findBy(['habilitar_rxp'=>1]);
            foreach ($trabajadores as $trabajador) {
                $rendicion=new Rendicion();
                $rendicion->setUsuario($trabajador);
                $rendicion->setFechaRegistro(new \DateTime(date('Y-m-d H:i:s')));
                $rendicion->setFechaInicio(new \DateTime(date('Y-m-d')));
                $rendicion->setFechaFin(new \DateTime(date('Y-m-d')));
                $rendicion->setArea($trabajador->getArea());
                $rendicion->setTipo($em->getRepository(RendicionTipo::class)->find(2));
                $rendicion->setEstado($em->getRepository(RendicionEstado::class)->find(1));
                $rendicion->setMonto($trabajador->getMontoRxp());

                $dql="SELECT max(r.numero) as numero 
                FROM App:Rendicion r 
                where r.tipo=2";

                $query= $entityManager->createQuery($dql);
                $rendicion_numero=$query->getResult();

                if($rendicion_numero){
                    $numero=$rendicion_numero[0]['numero'];
                }
                $numero++;
                
                $rendicion->setNumero($numero);

                $trabajador->setPendientes($trabajador->getPendientes()+1);


                $entityManager->persist($rendicion);
                $entityManager->flush();
                $entityManager->persist($trabajador);
                $entityManager->flush();


                $render=$this->twig->load('email/apruebaar.html.twig');
                $message = (new \Swift_Message('Fondo Fijo habilitado'))
                    ->setFrom('noreply@ulmaconstruction.cl')
                    ->setTo($trabajador->getCorreo())
                    ->setCc('jromero@ulmaconstruction.cl')
                    ->setBody(
                        $render->render(
                            [
                                'rendicion' =>$rendicion,
                                'host'=>$config->getHost(),
                                'Titulo'=>'Fondo Fijo'
                            ]
                        ),
                        'text/html'
                    )
                ;
                $this->mailer->send($message);

                $io->note(sprintf('Trabajador : %s Procesado', $trabajador->getNombre()));
            }

        }


        $io->success('Proceso finalizado! --help para ver ayuda.');

        return 0;
    }
}
