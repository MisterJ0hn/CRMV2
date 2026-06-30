<?php

namespace App\Form;

use App\Entity\Vencimiento;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VencimientoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('valMin')
            ->add('valMax')
            ->add('color', ChoiceType::class, [
                'choices' => [
                    'text-warning' => 'text-warning',
                    'text-danger'  => 'text-danger',
                    'text-info'    => 'text-info',
                    'text-success'    => 'text-success',
                    'text-ligth'    => 'text-ligth',
                ],
            ])
  
            ->add('icono')          
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Vencimiento::class,
        ]);
    }
}
