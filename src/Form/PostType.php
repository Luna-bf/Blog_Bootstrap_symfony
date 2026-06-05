<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Post;
use DateTimeImmutable;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la publication',

                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez définir un titre.',
                    )
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',

                // Attributs de l'input
                'attr' => [
                    'rows' => '5',
                    'cols' => '33',
                ],

                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez rédiger une description.',
                    )
                ],
            ])
            /*
            La date (DateTimeImmutable) sera envoyée par le contrôleur grâce au setter "setCreatedAt". Effectuer l'envoi de cette
            donnée côté serveur est plus sécurisé car l'utilisateur n'y aura jamais accès (envoyer cette donnée via un formulaire
            lui donnerais la possibilité de modifier une donnée sensible, ce qui n'est pas désirable).
            */
            ->add('image_name', FileType::class, [
                'label' => 'Image',

                'mapped' => false,

                // Pour ne pas re-publier l'image à chaque fois que l'on modifie un post
                'required' => $options['is_file_required'],

                'constraints' => [
                    new Assert\File(
                        extensions: ['jpeg', 'png', 'jpg'],
                        extensionsMessage: 'Veuillez exporter une image.',
                    ),
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',

                'label' => 'Catégorie',

                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez sélectionner une catégorie.',
                    )
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Publier',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
        /*
        Définition d'une nouvelle option nommée "is_file_required" grâce à la méthode setRequired() que je vais utiliser dans le
        contrôleur "PostController" pour définir une valeur différente (true ou false) pour le formulaire de création et le
        formulaire de modification de post
        */
        $resolver->setRequired('is_file_required');
    }
}
