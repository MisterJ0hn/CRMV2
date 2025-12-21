<?php

namespace App\Controller;
use App\Repository\ConfiguracionRepository;
use App\Entity\ClientePotencial;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\WebhookRepository;
/**
 * @Route("/webhook")
 */
class WebhookController extends AbstractController
{
    /**
     * @Route("/", name="webhook_index", methods={"GET","POST","PUT"})
     */
    public function index(ConfiguracionRepository $configuracionRepository)
    {
         error_log(print_r("PAOS",true),3,"/home/webhook_error_log");
    	$challenge = $_REQUEST['hub_challenge'];
		$verify_token = $_REQUEST['hub_verify_token'];
        $configuracion=$configuracionRepository->find(1);

		if ($verify_token === $configuracion->getVerifyToken()) {
         echo $challenge;
            
		}

		
        $access_token=$configuracion->getAccessToken();

        $input = json_decode(file_get_contents('php://input'), true);
        error_log(print_r($input,true));



        $lead=$input['entry'][0]['changes'][0]['value'];
        
        $clientePotencial=new ClientePotencial();
        $data = json_decode( file_get_contents('https://graph.facebook.com/'.$lead['leadgen_id'].'?access_token='.$access_token), true );

        $clientePotencial->setFormId($lead['form_id']);
        $clientePotencial->setLeadgenId($lead['leadgen_id']);
        $clientePotencial->setPageId($lead['page_id']);
        $clientePotencial->setCreateTime($lead['created_time']);
        $clientePotencial->setCampos($data);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($clientePotencial);
        $entityManager->flush();

        return $this->render('webhook/index.html.twig', [
                'controller_name' => 'WebhookController',
                'challenge'=>$challenge,
            ]);
    }
    /**
     * @Route("/funnelup", name="webhook_funnelup", methods={"GET","POST","PUT"})
     */
    public function funnelUp(ConfiguracionRepository $configuracionRepository)
    {
        
        # Recibimos los datos leídos de php://input
       // $datosRecibidos = $_POST;
        $datosRecibidos = file_get_contents("php://input");
        # No los hemos decodificado, así que lo hacemos de una vez:
       /* $persona = json_decode($datosRecibidos);
        $respuesta=[
            "nombre"=>$persona->name,
            "email"=>$persona->email
        ];*/
            
        error_log("Respuesta: ".var_dump($datosRecibidos),3,$this->getParameter('url_raiz')."/var/log/webhook_funnelup_log");
        return $this->render('webhook/index.html.twig', [
            'controller_name' => 'WebhookController'
        ]);
    }
}
