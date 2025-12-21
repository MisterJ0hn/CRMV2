<?php

namespace App\Controller;
use App\Entity\Empresa;
use App\Repository\EmpresaRepository;
use App\Entity\Cuenta;
use App\Entity\Usuario;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @Route("/changecomp")
 */
class ChangecompController extends AbstractController
{
    /**
     * @Route("/", name="changecomp_index")
     */
    public function index(String $route_name, EmpresaRepository $empresaRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $user=$this->getUser();
        $empresas=$empresaRepository->findAll();

        return $this->render('changecomp/index.html.twig', [
            'controller_name' => 'ChangecompController',
            'empresas'=>$empresas,
            'route'=>$route_name,
            'id_empresa'=>$user->getEmpresaActual(),
        ]);
    }
    /**
     * @Route("/new", name="changecomp_new", methods={"GET","POST"})
     */
    public function new(EmpresaRepository $empresaRepository, Request $request,UrlGeneratorInterface $urlGenerator): Response
    {
        $empresa=$request->request->get('company');
        $route=$request->request->get('route');

        $user=$this->getUser();
        $user->setEmpresaActual($empresa);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return new RedirectResponse($urlGenerator->generate($route));        
    }
}
