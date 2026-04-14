<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                // Label personnalisé
                'label' => 'Nom d\'utilisateur',

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
            ->add('email', EmailType::class, [
                // Label personnalisé
                'label' => 'Adresse mail',

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
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label_attr' => [
                    'class' => 'me-2 text-center text-white'
                ],
                'constraints' => [
                    new IsTrue(
                        message: 'You should agree to our terms.',
                    ),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],

                // Label personnalisé
                'label' => 'Mot de passe',

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
                    new NotBlank(
                        message: 'Veuillez saisir un mot de passe.',
                    ),
                    new Length(
                        min: 6,
                        minMessage: 'Votre mot de passe doit contenir au moins {{ limit }} caractères.',
                        // max length allowed by Symfony for security reasons
                        max: 4096,
                    ),
                ],
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
