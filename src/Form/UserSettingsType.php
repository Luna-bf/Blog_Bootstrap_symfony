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
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',

                'mapped' => false,
            ])
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',

                'mapped' => false,

                'required' => false,
            ])
            ->add('profile_picture_name', FileType::class, [
                'label' => 'Photo de profil',

                'mapped' => false,

                'required' => false,
                
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

                'required' => false,
                
                'constraints' => [
                    new Assert\File(
                        extensions: ['jpeg', 'png', 'jpg'],
                        extensionsMessage: 'Veuillez exporter une image.',
                    )
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer les modifications',
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
