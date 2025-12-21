<?php


namespace App\Command;

use App\Entity\Usuario;
use App\Entity\Agenda;
use App\Entity\Cuenta;
use App\Entity\MovatecLog;
use App\Entity\ResponseEnvio;
use App\Entity\ResponseLogin;
use App\Entity\UsuarioUsuariocategoria;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Service\Movatec;


class AsignarLeadsCommand extends Command
{
    protected static $defaultName = 'app:asignar-leads';
    private $container;

    public function __construct(ContainerInterface $container){
        $this->container=$container;   
        parent::__construct();
    }
    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
        
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
       
        $io = new SymfonyStyle($input, $output);
        $movatec=new Movatec($this->container);
        $token=$movatec->login();
        
        $entityManager = $this->container->get('doctrine')->getManager();
        $em=$this->container->get('doctrine');

        //buscamos leads sin asignar::::

        $agendas=$em->getRepository(Agenda::class)->findBy(['agendador'=>null]);
        foreach($agendas as $agenda){
            
            $io->note(sprintf(" agenda: %s",$agenda->getId()));
            //seleccionamos la Cuenta de esta agenda y buscamos los agendadores disponibles
            if($agenda->getCampania() == 'FDTC120225 NOTTFO2'){ //JRM:27-02-2025 - Si la compania es NOTTFO2 asignamos a los agendadores 23873 y 23860 (Ana y Gloria)
                $agendadores=$em->getRepository(Usuario::class)->findBy(['id'=>[23873,23845]]);
            }else{
                $agendadores=$em->getRepository(Usuario::class)->findByCuenta($agenda->getCuenta()->getId(),['usuarioTipo'=>5,'status'=>1,'estado'=>1]);
            }
            
            $eliminar_asignaciones=true;
            foreach($agendadores as $agendador){
                $leads=$agendador->getCategoria()->getNLeads();
                
                //consultamos los leads asignados
                $dql="SELECT count(u.usuario) as leads_asignados 
                FROM App:UsuarioUsuariocategoria u 
                where u.usuario= ".$agendador->getId()."
                and u.cuenta=".$agenda->getCuenta()->getId();

                $query= $entityManager->createQuery($dql);
                $leads_asignados=$query->getResult();
                
                
                $io->note(sprintf('asignados %s configurados %s',$leads_asignados[0]['leads_asignados'],$leads)); 
                $io->note(sprintf('agenda %s cuenta %s agendador %s',$agenda->getId(),$agenda->getCuenta()->getId(), $agendador->getNombre()));
                if(intval($leads_asignados[0]['leads_asignados'])<$leads){
                    $usuarioUsuariocategoria=new UsuarioUsuariocategoria();
                    $usuarioUsuariocategoria->setUsuario($agendador);
                    $usuarioUsuariocategoria->setCuenta($agenda->getCuenta());
                    $usuarioUsuariocategoria->setAgenda($agenda);
                    $entityManager->persist($usuarioUsuariocategoria);
                    $entityManager->flush();
                    $agenda->setAgendador($agendador);
                    $entityManager->persist($agendador);
                    $entityManager->flush();
                    $io->note(sprintf('cliente : %s agendador %s', $agenda->getNombreCliente(),$agendador->getNombre()));
                    $io->note(sprintf('leads Asignados : %s ', $leads_asignados[0]['leads_asignados']));

                    //Enviamos el lead a la api movatec
                    $io->note(sprintf('Enviando a movatec'));
                        
                    $response=$movatec->create_leads($token,$agenda->getId(),"",$agenda->getFechaCarga()->format('Y-m-d'),$agenda->getTelefonoCliente(),$agenda->getId());
                    
                    $io->note(sprintf('Enviando a movatec: %s', $response));
                    $eliminar_asignaciones=false;
                    break;
                }
                
                    
            }
            if($eliminar_asignaciones){
                //si llegamos a este punto es que se nos acabo el espacio de asignacion, polo que debemos limpiar las asignacionesPrep
                $io->note(sprintf('Preparando para eliminar'));
            
                $usuarioUsuariocategorias=$em->getRepository(UsuarioUsuariocategoria::class)->findBy(['cuenta'=>$agenda->getCuenta()->getId()]);
                foreach($usuarioUsuariocategorias as $usuarioUsuariocategoria){
                    $io->note(sprintf('Borrando usuariousuariocategoria %s',$usuarioUsuariocategoria->getId()));
                    $entityManager->remove($usuarioUsuariocategoria);
                    $entityManager->flush();
                
                }
                //seleccionamos la Cuenta de esta agenda y buscamos los agendadores disponibles
                //seleccionamos la Cuenta de esta agenda y buscamos los agendadores disponibles
                if($agenda->getCampania() == 'FDTC120225 NOTTFO2'){ //JRM:27-02-2025 - Si la compania es NOTTFO2 asignamos a los agendadores 23873 y 23860 (Ana y Gloria)
                    $agendadores=$em->getRepository(Usuario::class)->findBy(['id'=>[23873,23845]]);
                }else{
                    $agendadores=$em->getRepository(Usuario::class)->findByCuenta($agenda->getCuenta()->getId(),['usuarioTipo'=>5,'status'=>1,'estado'=>1]);
                }
                //$agendadores=$em->getRepository(Usuario::class)->findByCuenta($agenda->getCuenta()->getId(),['usuarioTipo'=>5,'status'=>1,'estado'=>1]);
                $eliminar_asignaciones=true;
                foreach($agendadores as $agendador){
                    $usuarioUsuariocategoria=new UsuarioUsuariocategoria();
                    $usuarioUsuariocategoria->setUsuario($agendador);
                    $usuarioUsuariocategoria->setCuenta($agenda->getCuenta());
                    $usuarioUsuariocategoria->setAgenda($agenda);
                    $entityManager->persist($usuarioUsuariocategoria);
                    $entityManager->flush();
                    $agenda->setAgendador($agendador);
                    $entityManager->persist($agendador);
                    $entityManager->flush();
                    $io->note(sprintf('cliente : %s agendador %s', $agenda->getNombreCliente(),$agendador->getNombre()));
                    
                    //Enviamos el lead a la api movatec
                    $io->note(sprintf('Enviando a movatec'));
                      
                    $response=$movatec->create_leads($token,$agenda->getId(),"",$agenda->getFechaCarga()->format('Y-m-d'),$agenda->getTelefonoCliente(),$agenda->getId());
                    
                    
                    $io->note(sprintf('Enviando a movatec: %s', $response));
                    break;
                }
            }
        }

        $io->success('Ha finalizado proceso de asignacion');

        return 0;
    }
    
}
