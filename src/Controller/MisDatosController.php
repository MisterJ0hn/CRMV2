<?php

namespace App\Controller;

use App\Repository\UsuarioRepository;
use App\Repository\ModuloPerRepository;
use App\Service\PasswordService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/mis_datos")
 */

class MisDatosController extends AbstractController
{
    /**
     * @Route("/", name="mis_datos_index", methods={"GET"})
     */
    public function index(
    ModuloPerRepository $moduloPerRepository)
    {
        $this->denyAccessUnlessGranted('view','mis_datos');
        $u=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('mis_datos',$u->getEmpresaActual());
        return $this->render('mis_datos/index.html.twig', [
            'misdatos' => $u,
            'pagina'=>$pagina->getNombre(),
            ]);
    }
    /**
     * @Route("/modificar", name="mis_datos_modificar", methods={"GET","POST"})
     */
    public function modificar(UsuarioRepository $usuarioRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit','mis_datos');

        $u=$this->getUser();
        $usuario=$usuarioRepository->find($u->getId());

        $usuario->setNombre($request->request->get('nombre'));
        $usuario->setCorreo($request->request->get('correo'));

        
        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($usuario);
        $entityManager->flush();

        //return $this->redirectToRoute('mis_datos_index');
        $error="<div class='alert alert-success alert-dismissible fade show' role='alert'>
        <strong>Exito</strong> Datos modificados!!!
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
        <span aria-hidden='true'>&times;</span></button></div>";
        return $this->render('mis_datos/index.html.twig', ['misdatos' => $usuario,'mensaje'=>$error]);



    }
     /**
     * @Route("/password", name="mis_datos_password", methods={"GET","POST"})
     */
    public function password(UsuarioRepository $usuarioRepository, Request $request, UserPasswordEncoderInterface $encoder, PasswordService $passwordService): Response
    {
        $this->denyAccessUnlessGranted('edit','mis_datos');
        $u=$this->getUser();
        $usuario=$usuarioRepository->find($u->getId());

        $password=$request->request->get('password');

        if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,}$/', $password)) {
            $error="<div class='alert alert-danger alert-dismissible fade show' role='alert'>
            <strong>Error</strong> La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial.
            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span></button></div>";
            return $this->render('mis_datos/index.html.twig', ['misdatos' => $usuario, 'error' => $error]);
        }

        if ($passwordService->yaFueUsada($usuario, $password)) {
            $limite = $passwordService->getHistorialLimite();
            $error="<div class='alert alert-danger alert-dismissible fade show' role='alert'>
            <strong>Error</strong> No puedes reutilizar alguna de tus últimas {$limite} contraseñas.
            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span></button></div>";
            return $this->render('mis_datos/index.html.twig', ['misdatos' => $usuario, 'error' => $error]);
        }

        $passwordService->aplicarNuevoPassword($usuario, $password);

        $error="<div class='alert alert-success alert-dismissible fade show' role='alert'>
        <strong>Éxito</strong> Contraseña modificada. Caduca en {$passwordService->getDiasExpiracion()} días.
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
        <span aria-hidden='true'>&times;</span></button></div>";
        return $this->render('mis_datos/index.html.twig', ['misdatos' => $usuario, 'error' => $error]);
    }
}
