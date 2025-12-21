<?php

namespace App\Form;

use App\Entity\Contrato;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class ContratoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre')
            ->add('email')
            ->add('telefono')
            ->add('rut')
            ->add('direccion')
            ->add('montoNivelDeuda')
            ->add('MontoContrato')
            ->add('primeraCuota')
            ->add('isAbono')
            ->add('isTotal')
            ->add('cuotas')
            ->add('valorCuota')
            ->add('estadoCivil')
            ->add('situacionLaboral')
            ->add('claveUnica')
            ->add('pais')
            ->add('telefonoRecado')
            ->add('vehiculo')
            ->add('vivienda')
            ->add('reunion')
            ->add('observacion')

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contrato::class,
        ]);
    }
}
