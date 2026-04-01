<?php

namespace App\EventListener;

use App\Repository\ApiTokenRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;


class ApiTokenListener
{
    private $tokens;

    public function __construct(ApiTokenRepository $tokens)
    {
        $this->tokens = $tokens;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        if ($request->getPathInfo() === '/api/auth') {
            return;
        }

        //$header = $request->headers->get('Authorization');
        $header= $request->headers->get('Authorization')
                    ?? $_SERVER['HTTP_AUTHORIZATION']
                    ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
                    ?? null;

        if (!$header || !preg_match('/Bearer\s(\S+)/', $header, $m)) {
           $event->setResponse(new JsonResponse([
                'exito' => false,
                'mensaje' => 'Token requerido'
            ], 401));
            return;
        }

        $token = $this->tokens->findOneBy(['token' => $m[1]]);

        if (!$token || $token->getExpiresAt() < new \DateTime()) {
        
            $event->setResponse(new JsonResponse([
                'exito' => false,
                'mensaje' => 'Token expirado o inválido'
            ], 401));
            return;
        }
    }
}