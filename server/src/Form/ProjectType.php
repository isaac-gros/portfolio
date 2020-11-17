<?php

namespace App\Form;

use App\Entity\Project;
use App\Form\BaseType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $inputParams = [
            'mapped' => false,
            'constraints' => [
                new File([
                    'maxSize' => '5000k',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                    ],
                    'mimeTypesMessage' => 'Le fichier doit être au format JPEG ou PNG.',
                ])
            ],
        ];

        $builder
            ->add('base', BaseType::class, [
                'data_class' => Project::class,
            ])
            ->add('name', TextType::class, [
                'attr' => [ 'class' => 'form-control' ]
            ])
            ->add('summary', TextareaType::class, [
                'attr' => [ 'class' => 'form-control' ]
            ])
            ->add('thumbnail', FileType::class, array_merge(
                ['attr' => [ 'class' => 'form-control-file' ]],
                $inputParams
            ))
            ->add('images', FileType::class, [
                'multiple' => true,
                'mapped' => false,
                'constraints' => [
                    new All([
                      'constraints' => $inputParams['constraints']
                    ]),
                ],
                'attr' => [ 'class' => 'form-control-file' ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
