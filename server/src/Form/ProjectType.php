<?php

namespace App\Form;

use App\Entity\Project;
use App\Form\BaseType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('base', BaseType::class, [
                'data_class' => Project::class,
            ])
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control']
            ])
            ->add('summary', TextareaType::class, [
                'attr' => ['class' => 'form-control']
            ])
            ->add('thumbnail', HiddenType::class, [
                'required'   => false,
                'empty_data' => ''
            ])
            ->add('images', HiddenType::class, [
                'required'   => false,
                'empty_data' => ''
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // 'data_class' => Project::class,
        ]);
    }
}
