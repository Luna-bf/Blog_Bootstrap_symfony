<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse mail',

                'mapped' => false,

                // Pour ne pas re-publier l'image à chaque fois que l'on modifie un post
                'required' => false,

                // Attributs de la div générée par $builder
                'row_attr' => [
                    'class' => 'mb-4'
                ],

                // Attributs du label
                'label_attr' => [
                    'class' => 'form-label text-dark'
                ],

                // Attributs de l'input
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',

                'mapped' => false,

                // Pour ne pas re-publier l'image à chaque fois que l'on modifie un post
                'required' => false,

                // Attributs de la div générée par $builder
                'row_attr' => [
                    'class' => 'mb-4'
                ],

                // Attributs du label
                'label_attr' => [
                    'class' => 'form-label text-dark'
                ],

                // Attributs de l'input
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',

                'mapped' => false,

                // Pour ne pas re-publier l'image à chaque fois que l'on modifie un post
                'required' => false,

                // Attributs de la div générée par $builder
                'row_attr' => [
                    'class' => 'mb-4'
                ],

                // Attributs du label
                'label_attr' => [
                    'class' => 'form-label text-dark'
                ],

                // Attributs de l'input
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('profile_picture_name', FileType::class, [
                'label' => 'Photo de profil',

                'mapped' => false,

                // Pour ne pas re-publier l'image à chaque fois que l'on modifie un post
                'required' => false,

                // Attributs de la div générée par $builder
                'row_attr' => [
                    'class' => 'mb-4'
                ],

                // Attributs du label
                'label_attr' => [
                    'class' => 'form-label text-dark'
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
            ->add('banner_name', FileType::class, [
                'label' => 'Bannière de profil',

                'mapped' => false,

                // Pour ne pas re-publier l'image à chaque fois que l'on modifie un post
                'required' => false,

                // Attributs de la div générée par $builder
                'row_attr' => [
                    'class' => 'mb-4'
                ],

                // Attributs du label
                'label_attr' => [
                    'class' => 'form-label text-dark'
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
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer les modifications',

                // Attributs de la div générée par $builder
                'row_attr' => [
                    'class' => 'mb-4 d-flex justify-content-center'
                ],

                // Attributs de l'input
                'attr' => [
                    'class' => 'btn mt-3 py-2 w-50 bg-dark text-light ',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
