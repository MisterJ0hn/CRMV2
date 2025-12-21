<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlatformController extends AbstractController
{
    /**
     * @Route("/platform", name="app_platform")
     */
    public function index(): Response
    {
        session_start();

        $appId = "210261737605756";
        $appSecret = "210261737605756|KF7oIvoQ5PtsNlMpl8ZGhzJJHPg";
        $redirectUri = "https://crm.ejam.cl/platform";

        // 1. Si no hay "code", redirige al login de Facebook
        if (!isset($_GET['code'])) {
            $loginUrl = "https://www.facebook.com/v18.0/dialog/oauth?" . http_build_query([
                'client_id' => $appId,
                'redirect_uri' => $redirectUri,
                'scope' => 'pages_show_list,leads_retrieval,pages_read_engagement,pages_manage_metadata,pages_read_user_content,pages_manage_ads',
                'response_type' => 'code',
            ]);
            header("Location: $loginUrl");
            exit;
        }

        // 2. Intercambia el code por un access_token
        $code = $_GET['code'];
        $tokenUrl = "https://graph.facebook.com/v18.0/oauth/access_token?" . http_build_query([
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'client_secret' => $appSecret,
            'code' => $code,
        ]);

        $response = file_get_contents($tokenUrl);
        $tokenData = json_decode($response, true);
        $accessToken = $tokenData['access_token'] ?? null;

        if (!$accessToken) {
            die("No se pudo obtener el token.");
        }

        // 3. Obtiene las páginas que administra el usuario
        $pagesUrl = "https://graph.facebook.com/v18.0/me/accounts?access_token=$accessToken";
        $pagesResponse = file_get_contents($pagesUrl);
        $pagesData = json_decode($pagesResponse, true);

        echo "<h2>Páginas disponibles:</h2>";
        foreach ($pagesData['data'] as $page) {
            $pageId = $page['id'];
            $pageName = $page['name'];
            $pageAccessToken = $page['access_token'];
            echo "<p><strong>$pageName</strong> (ID: $pageId)</p>";

            // 4. Suscribe la página al webhook (leadgen)
            $subscribeUrl = "https://graph.facebook.com/v18.0/$pageId/subscribed_apps";
            $postFields = http_build_query([
                'subscribed_fields' => 'leadgen',
                'access_token' => $pageAccessToken
            ]);

            $opts = [
                "http" => [
                    "method" => "POST",
                    "header" => "Content-Type: application/x-www-form-urlencoded",
                    "content" => $postFields
                ]
            ];

            $context = stream_context_create($opts);
            $subscribeResult = file_get_contents($subscribeUrl, false, $context);
            $subscribeResponse = json_decode($subscribeResult, true);

            echo "<pre>Respuesta: " . print_r($subscribeResponse, true) . "</pre>";
                }
        return $this->render('platform/index.html.twig', [
            'controller_name' => 'PlatformController',
        ]);
    }
}
