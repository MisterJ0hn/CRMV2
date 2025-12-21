<?php

namespace App\Form;

use App\Entity\ClientePotencial;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientePotencialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('formId')
            ->add('leadgenId')
            ->add('pageId')
            ->add('createdTime')
            ->add('campos')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ClientePotencial::class,
        ]);
    }
}
