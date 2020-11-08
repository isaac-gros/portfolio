<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'attr' => [ 'class' => 'form-control' ]
            ])
            ->add('description', TextareaType::class, [
                'attr' => [ 
                    'class' => 'form-control',
                    'id' => 'description',
                    'maxlength' => 120,
                ]
            ])
            ->add('content', TextareaType::class, [
                'attr' => ['id' => 'tiny']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'inherit_data' => true
        ]);
    }
}
