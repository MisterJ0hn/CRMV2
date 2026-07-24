<?php

namespace App\Service;

use App\Repository\UsuarioFcmTokenRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

/**
 * Envía notificaciones push vía Firebase Cloud Messaging (HTTP v1 API) usando
 * las credenciales de una cuenta de servicio (service account JSON).
 */
class FirebaseMessagingService
{
    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';
    private const OAUTH_URL = 'https://oauth2.googleapis.com/token';

    private $credentialsPath;
    private $projectDir;
    private $tokenRepository;
    private $logger;
    private $client;

    private $accessToken;
    private $accessTokenExpiraEn;

    public function __construct(
        string $credentialsPath,
        string $projectDir,
        UsuarioFcmTokenRepository $tokenRepository,
        LoggerInterface $logger
    ) {
        $this->credentialsPath = $credentialsPath;
        $this->projectDir = $projectDir;
        $this->tokenRepository = $tokenRepository;
        $this->logger = $logger;
        $this->client = new Client();
    }

    /**
     * Envía la notificación a todos los tokens de dispositivo activos.
     *
     * @param array<string,string> $data
     *
     * @return array{enviados:int,fallidos:int}
     */
    public function enviarATodosLosTokens(string $titulo, string $cuerpo, array $data = []): array
    {
        $tokens = $this->tokenRepository->findTokensActivos();

        $resultado = ['enviados' => 0, 'fallidos' => 0];

        if (empty($tokens)) {
            return $resultado;
        }

        try {
            $accessToken = $this->obtenerAccessToken();
        } catch (\Throwable $e) {
            $this->logger->error('FirebaseMessagingService: no se pudo obtener access token: ' . $e->getMessage());
            $resultado['fallidos'] = count($tokens);

            return $resultado;
        }

        $credenciales = $this->leerCredenciales();
        $url = sprintf(
            'https://fcm.googleapis.com/v1/projects/%s/messages:send',
            $credenciales['project_id']
        );

        foreach ($tokens as $token) {
            $enviado = $this->enviarAToken($url, $accessToken, $token, $titulo, $cuerpo, $data);

            if ($enviado) {
                $resultado['enviados']++;
            } else {
                $resultado['fallidos']++;
            }
        }

        return $resultado;
    }

    private function enviarAToken(string $url, string $accessToken, string $token, string $titulo, string $cuerpo, array $data): bool
    {
        try {
            $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $titulo,
                            'body' => $cuerpo,
                        ],
                        'data' => $data,
                    ],
                ],
            ]);

            return true;
        } catch (GuzzleException $e) {
            $respuesta = method_exists($e, 'getResponse') ? $e->getResponse() : null;
            $cuerpoRespuesta = $respuesta ? (string) $respuesta->getBody() : '';

            if ($respuesta && in_array($respuesta->getStatusCode(), [400, 404], true)
                && (strpos($cuerpoRespuesta, 'UNREGISTERED') !== false || strpos($cuerpoRespuesta, 'INVALID_ARGUMENT') !== false)
            ) {
                $this->tokenRepository->desactivarToken($token);
            }

            $this->logger->error('FirebaseMessagingService: error enviando a token: ' . $e->getMessage());

            return false;
        }
    }

    private function obtenerAccessToken(): string
    {
        if ($this->accessToken && $this->accessTokenExpiraEn > time() + 30) {
            return $this->accessToken;
        }

        $credenciales = $this->leerCredenciales();
        $jwt = $this->firmarJwt($credenciales);

        $response = $this->client->post(self::OAUTH_URL, [
            'form_params' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ],
        ]);

        $data = json_decode((string) $response->getBody(), true);

        if (empty($data['access_token'])) {
            throw new \RuntimeException('Respuesta de OAuth2 sin access_token');
        }

        $this->accessToken = $data['access_token'];
        $this->accessTokenExpiraEn = time() + (int) ($data['expires_in'] ?? 3600);

        return $this->accessToken;
    }

    /**
     * Construye y firma (RS256) el JWT del flujo de cuenta de servicio de Google,
     * usando únicamente la extensión openssl (sin dependencias adicionales).
     */
    private function firmarJwt(array $credenciales): string
    {
        $ahora = time();

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims = [
            'iss' => $credenciales['client_email'],
            'scope' => self::SCOPE,
            'aud' => self::OAUTH_URL,
            'iat' => $ahora,
            'exp' => $ahora + 3600,
        ];

        $segmentos = [
            $this->base64UrlEncode(json_encode($header)),
            $this->base64UrlEncode(json_encode($claims)),
        ];

        $entrada = implode('.', $segmentos);

        $firma = '';
        $exito = openssl_sign($entrada, $firma, $credenciales['private_key'], OPENSSL_ALGO_SHA256);

        if (!$exito) {
            throw new \RuntimeException('No se pudo firmar el JWT con la clave privada de Firebase');
        }

        $segmentos[] = $this->base64UrlEncode($firma);

        return implode('.', $segmentos);
    }

    private function base64UrlEncode(string $valor): string
    {
        return rtrim(strtr(base64_encode($valor), '+/', '-_'), '=');
    }

    private function leerCredenciales(): array
    {
        $ruta = $this->credentialsPath;

        if ($ruta === '') {
            throw new \RuntimeException('FIREBASE_CREDENTIALS_PATH no está configurado');
        }

        if (!$this->esRutaAbsoluta($ruta)) {
            $ruta = rtrim($this->projectDir, '/\\') . DIRECTORY_SEPARATOR . $ruta;
        }

        if (!is_file($ruta)) {
            throw new \RuntimeException('No se encontró el archivo de credenciales de Firebase: ' . $ruta);
        }

        $credenciales = json_decode(file_get_contents($ruta), true);

        if (empty($credenciales['project_id']) || empty($credenciales['client_email']) || empty($credenciales['private_key'])) {
            throw new \RuntimeException('El archivo de credenciales de Firebase es inválido o está incompleto');
        }

        return $credenciales;
    }

    private function esRutaAbsoluta(string $ruta): bool
    {
        return isset($ruta[0]) && ($ruta[0] === '/' || $ruta[0] === '\\' || preg_match('#^[A-Za-z]:[\\\\/]#', $ruta));
    }
}
