<?php

namespace App\EventListener;

use App\Entity\UserActivityLog;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserActivityListener implements EventSubscriberInterface
{
    private EntityManagerInterface $em;
    private TokenStorageInterface $tokenStorage;

    // Datos capturados en kernel.response para usarlos en kernel.terminate
    private ?array $pendingLog = null;

    private const RUTAS_EXCLUIDAS = [
        '/_wdt',
        '/_profiler',
        '/api/',
        '/favicon.ico',
        '/build/',
    ];

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        $this->em           = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE  => ['onKernelResponse',  0],
            KernelEvents::TERMINATE => ['onKernelTerminate', 0],
        ];
    }

    /**
     * Se ejecuta ANTES de enviar la respuesta → sesión y token aún disponibles.
     * Solo captura los datos, no escribe en BD.
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $ruta    = $request->getPathInfo();

        foreach (self::RUTAS_EXCLUIDAS as $excluida) {
            if (str_starts_with($ruta, $excluida)) {
                return;
            }
        }

        $metodo = $request->getMethod();
        if (!in_array($metodo, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
            return;
        }

        // Aquí la sesión está activa → sin errores de headers
        $token   = $this->tokenStorage->getToken();
        $usuario = $token ? $token->getUser() : null;

        if (!$usuario instanceof Usuario) {
            return;
        }

        $controlador = $request->attributes->get('_controller');
        if (is_array($controlador)) {
            $controlador = implode('::', $controlador);
        }

        // Guardar datos para escribir en BD después de enviar la respuesta
        $this->pendingLog = [
            'usuario'     => $usuario,
            'metodo'      => $metodo,
            'ruta'        => $ruta,
            'controlador' => $controlador ? substr($controlador, 0, 255) : null,
            'ip'          => $request->getClientIp(),
            'statusCode'  => $event->getResponse()->getStatusCode(),
        ];
    }

    /**
     * Se ejecuta DESPUÉS de enviar la respuesta → escribe el log en BD
     * sin afectar el tiempo de respuesta del usuario.
     */
    public function onKernelTerminate(TerminateEvent $event): void
    {
        if ($this->pendingLog === null) {
            return;
        }

        $log = new UserActivityLog();
        $log->setUsuario($this->pendingLog['usuario']);
        $log->setMetodo($this->pendingLog['metodo']);
        $log->setRuta($this->pendingLog['ruta']);
        $log->setControlador($this->pendingLog['controlador']);
        $log->setIp($this->pendingLog['ip']);
        $log->setStatusCode($this->pendingLog['statusCode']);
        $log->setFechaRegistro(new \DateTime());

        $this->em->persist($log);
        $this->em->flush();

        $this->pendingLog = null;
    }
}
