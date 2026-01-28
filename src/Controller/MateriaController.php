<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\Materia;
use App\Entity\Cuenta;
use App\Entity\CuentaMateria;
use App\Entity\Empresa;
use App\Form\MateriaType;
use App\Repository\CorteRepository;
use App\Repository\CuentaMateriaRepository;
use App\Repository\MateriaCorteRepository;
use App\Repository\MateriaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/materia")
 */
class MateriaController extends AbstractController
{
    /**
     * @Route("/", name="materia_index", methods={"GET"})
     */
    public function index(MateriaRepository $materiaRepository): Response
    {
        return $this->render('materia/index.html.twig', [
            'materias' => $materiaRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="materia_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {

        $user=$this->getUser();
        $empresa=$this->getDoctrine()->getRepository(Empresa::class)->find($user->getEmpresaActual());
        $materium = new Materia();
        $materium->setEmpresa($empresa);
        $cuentas=$empresa->getCuentas();
        $form = $this->createForm(MateriaType::class, $materium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($materium);
            $entityManager->flush();
            
            $getcuentas=$_POST['cboEmpresa'];

            foreach($getcuentas as $getcuenta){
                $cuenta=$this->getDoctrine()->getRepository(Cuenta::class)->find($getcuenta);
                
                $cuentaMateria=new CuentaMateria();

                $cuentaMateria->setCuenta($cuenta);
                $cuentaMateria->setMateria($materium);
                
                $entityManager->persist($cuentaMateria);
                $entityManager->flush();
                
            }
            

            return $this->redirectToRoute('materia_index');
        }

        return $this->render('materia/new.html.twig', [
            'materium' => $materium,
            'form' => $form->createView(),
            'cuentas'=>$cuentas,
        ]);
    }

    /**
     * @Route("/{id}", name="materia_show", methods={"GET"})
     */
    public function show(Materia $materium): Response
    {
        return $this->render('materia/show.html.twig', [
            'materium' => $materium,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="materia_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Materia $materium): Response
    {
        $user=$this->getUser();
        $empresa=$this->getDoctrine()->getRepository(Empresa::class)->find($user->getEmpresaActual());
        $cuentas=$empresa->getCuentas();
        $form = $this->createForm(MateriaType::class, $materium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $getcuentas=$_POST['cboEmpresa'];

            foreach($getcuentas as $getcuenta){
                $cuenta=$this->getDoctrine()->getRepository(Cuenta::class)->find($getcuenta);
                
                $cuentaMateria=new CuentaMateria();

                $cuentaMateria->setCuenta($cuenta);
                $cuentaMateria->setMateria($materium);
                
                $entityManager->persist($cuentaMateria);
                $entityManager->flush();
                
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('materia_index');
        }

        return $this->render('materia/edit.html.twig', [
            'materium' => $materium,
            'form' => $form->createView(),
            'cuentas'=>$cuentas,
            'cuentas_sel'=>$materium->getCuentaMaterias(),
        ]);
    }
    /**
     * @Route("/{id}/combo", name="materia_combo", methods={"GET","POST"})
     */
    public function combo(Cuenta $cuenta, CuentaMateriaRepository $cuentaMateriaRepository): Response
    {


        return $this->render('materia/combo.html.twig', [
            'cuenta_materias' => $cuentaMateriaRepository->findBy(['cuenta'=>$cuenta,'estado'=>1]),
            
        ]);
    }
    /**
     * @Route("/{id}/corte_combo", name="materia_corte_combo", methods={"GET","POST"})
     */
    public function corteCombo(Cuenta $cuenta, 
                            CorteRepository $corteRepository, 
                            CuentaMateriaRepository $cuentaMateriaRepository, 
                            MateriaCorteRepository $materiaCorteRepository): Response
    {
        $cuentaMateria = $cuentaMateriaRepository->findOneBy(["cuenta"=>$cuenta->getId()]);
        $materiaCortes = $materiaCorteRepository->findBy(['materia'=>$cuentaMateria->getMateria()]);    
        return $this->render('materia/comboCorte.html.twig', [
            'cortes' => $materiaCortes
        ]);
    }
    /**
     * @Route("/{id}", name="materia_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Materia $materium): Response
    {
        if ($this->isCsrfTokenValid('delete'.$materium->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($materium);
            $entityManager->flush();
        }

        return $this->redirectToRoute('materia_index');
    }

    
}
