<?php

namespace App\Controller;

use App\Entity\Usuario;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

/**
 * @Route("/password-caducado")
 */
class PasswordCaducadoController extends AbstractController
{
    use ResetPasswordControllerTrait;

    private ResetPasswordHelperInterface $resetPasswordHelper;

    public function __construct(ResetPasswordHelperInterface $resetPasswordHelper)
    {
        $this->resetPasswordHelper = $resetPasswordHelper;
    }

    /**
     * Muestra la página de contraseña caducada y permite solicitar el enlace de cambio.
     *
     * @Route("", name="password_caducado", methods={"GET","POST"})
     */
    public function index(Request $request, MailerInterface $mailer): Response
    {
        /** @var Usuario|null $usuario */
        $usuario = $this->getUser();

        // Si accede sin estar logueado (raro pero posible), redirigir al login
        if (!$usuario instanceof Usuario) {
            return $this->redirectToRoute('app_login');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            try {
                $resetToken = $this->resetPasswordHelper->generateResetToken($usuario);
            } catch (ResetPasswordExceptionInterface $e) {
                $error = 'No se pudo generar el enlace de cambio de contraseña. Intente nuevamente.';
                return $this->render('password_caducado/index.html.twig', [
                    'correo' => $usuario->getCorreo(),
                    'error'  => $error,
                ]);
            }

            $email = (new TemplatedEmail())
                ->from(new Address('info@proyectosphp.cl', 'Administrador'))
                ->to($usuario->getCorreo())
                ->subject('Cambio de contraseña requerido')
                ->htmlTemplate('reset_password/email.html.twig')
                ->context([
                    'resetToken'    => $resetToken,
                    'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
                ]);

            $mailer->send($email);

            $this->setCanCheckEmailInSession();

            return $this->redirectToRoute('app_check_email');
        }

        return $this->render('password_caducado/index.html.twig', [
            'correo' => $usuario->getCorreo(),
            'error'  => $error,
        ]);
    }
}
