<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Post;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la publication',

                // Attributs de la div générée par $builder
                'row_attr' => [
                    'class' => 'mb-4'
                ],

                // Attributs du label
                'label_attr' => [
                    'class' => 'form-label text-white'
                ],

                // Attributs de l'input
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',

                // Attributs de la div générée par $builder
                'row_attr' => [
                    'class' => 'mb-4'
                ],

                // Attributs du label
                'label_attr' => [
                    'class' => 'form-label text-white'
                ],

                // Attributs de l'input
                'attr' => [
                    'class' => 'form-control',
                    'rows' => '5',
                    'cols' => '33',
                ]
            ])
            ->add('created_at', HiddenType::class, [
                // 'widget' => 'single_text',

                'label' => 'Date de la publication',

                // Attributs de la div générée par $builder
                'row_attr' => [
                    'class' => 'mb-4'
                ],

                // Attributs du label
                'label_attr' => [
                    'class' => 'form-label text-white'
                ],

                // Attributs de l'input
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('image_name', FileType::class, [
                'label' => 'Image',

                'mapped' => false,

                // Pour ne pas re-publier l'image à chaque fois que l'on modifie un post
                'required' => false,

                // Attributs de la div générée par $builder
                'row_attr' => [
                    'class' => 'mb-4'
                ],

                // Attributs du label
                'label_attr' => [
                    'class' => 'form-label text-white'
                ],

                // Attributs de l'input
                'attr' => [
                    'class' => 'form-control',
                ],
                
                'constraints' => [
                    new Assert\File(
                        extensions: ['jpeg', 'png', 'jpg'],
                        extensionsMessage: 'Veuillez exporter une image.',
                    )
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',

                'label' => 'Catégorie',

                // Attributs de la div générée par $builder
                'row_attr' => [
                    'class' => 'mb-4'
                ],

                // Attributs du label
                'label_attr' => [
                    'class' => 'form-label text-white'
                ],

                // Attributs de l'input
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Publier',

                // Attributs de la div générée par $builder
                'row_attr' => [
                    'class' => 'mb-4'
                ],

                // Attributs de l'input
                'attr' => [
                    'class' => 'btn align-self-start mt-3 w-50 m-auto text-white sign-in',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
