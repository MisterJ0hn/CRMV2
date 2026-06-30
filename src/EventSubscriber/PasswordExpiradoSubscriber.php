<?php

namespace App\EventSubscriber;

use App\Entity\Usuario;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class PasswordExpiradoSubscriber implements EventSubscriberInterface
{
    private const RUTAS_EXCLUIDAS = [
        'app_login',
        'app_logout',
        'password_caducado',
        'app_forgot_password_request',
        'app_check_email',
        'app_reset_password',
    ];

    private $security;
    private $urlGenerator;

    public function __construct(Security $security, UrlGeneratorInterface $urlGenerator)
    {
        $this->security     = $security;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 5],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $route = $event->getRequest()->attributes->get('_route');

        if (in_array($route, self::RUTAS_EXCLUIDAS, true)) {
            return;
        }

        $usuario = $this->security->getUser();

        if (!$usuario instanceof Usuario) {
            return;
        }

        if ($usuario->isPasswordExpirado()) {
            $event->setResponse(new RedirectResponse(
                $this->urlGenerator->generate('password_caducado')
            ));
        }
    }
}
