<?php

namespace App\Controller;

use App\Form\UserFormType;
use App\Form\UserSettingsType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/user', name: 'user_')]
final class UserController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(PostRepository $repo): Response
    {
        // Refactor cette méthode pour trouver uniquement le contenu de l'utilisateur connecté
        return $this->render('user/index.html.twig', [
            'posts' => $repo->findAll(),
        ]);
    }

    #[Route('/settings', name: 'settings')]
    public function settings(UserRepository $repo, Request $request, EntityManagerInterface $em, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/uploads/profile_pictures')] string $profilePicturesDirectory, #[Autowire('%kernel.project_dir%/public/uploads/banners')] string $bannersDirectory): Response
    {
        // Je récupère les informations de l'utilisateur connecté
        $userForm = $this->getUser();

        // Initialisation du formulaire
        $editUserForm = $this->createForm(UserSettingsType::class, $userForm);

        // Traitement du formulaire
        $editUserForm->handleRequest($request);

        // Si le formulaire est envoyé (isSubmitted) et que ces données sont valides (isValid())
        if ($editUserForm->isSubmitted() && $editUserForm->isValid()) {

            // Récupère la valeur de l'input "profile_picture_name" et la stocke dans la variable $profilePicture
            $profilePicture = $editUserForm->get('profilePictureName')->getData();
            $banner = $editUserForm->get('bannerName')->getData();

            // Je récupère toutes les données du formulaire (rempli) et les injectes dans l'objet $post
            $completePost = $editUserForm->getData();

            // Si la valeur du champ de saisie "image_name" (stockée dans la variable $profilePicture) n'est pas vide...
            if ($profilePicture) {
                // Je récupère le nom original de l'image
                $originalImageName = pathinfo($profilePicture->getClientOriginalName(), PATHINFO_FILENAME);

                // Puis j'utilise un slug pour créer un nom sécurisé
                $safeFilename = $slugger->slug($originalImageName);

                /* Enfin, je crée le nom définitif de l'image en utilisant la valeur de la variable $safeFileName, j'inclus
                un identifiant unique grâce à la fonction "uniqid()", puis je précise l'extension de l'image grâce à la fonction
                "guessExtension()" appliquée sur la variable $image.
                */
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $profilePicture->guessExtension();
                
                try {
                    // Envoie l'image dans le dossier adéquat
                    $profilePicture->move($profilePicturesDirectory, $newFilename);
                } catch (FileException $e) {
                    $message = $e;
                }

                // Stocke le nom de l'image dans la BDD
                $editUserForm->setProfilePictureName($newFilename);
            }

            if ($banner) {
                // Je récupère le nom original de l'image
                $originalImageName = pathinfo($banner->getClientOriginalName(), PATHINFO_FILENAME);

                // Puis j'utilise un slug pour créer un nom sécurisé
                $safeFilename = $slugger->slug($originalImageName);

                /* Enfin, je crée le nom définitif de l'image en utilisant la valeur de la variable $safeFileName, j'inclus
                un identifiant unique grâce à la fonction "uniqid()", puis je précise l'extension de l'image grâce à la fonction
                "guessExtension()" appliquée sur la variable $image.
                */
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $banner->guessExtension();
                
                try {
                    // Envoie l'image dans le dossier adéquat
                    $banner->move($bannersDirectory, $newFilename);
                } catch (FileException $e) {
                    $message = $e;
                }

                // Stocke le nom de l'image dans la BDD
                $editUserForm->setBannerName($newFilename);
            }

            // J'ajoute la date de création (setter) au champ createdAt du formulaire (caché)
            $completePost->setCreatedAt(new \DateTimeImmutable());

            $user = $this->getUser(); // Je récupère les données l'utilisateur connecté
            $completePost->setMyUser($user); // Puis j'associe les données de l'utilisateur connecté à la publication (setter)

            $em->persist($completePost); // Prépare la requête (ici, la création d'un nouveau post)
            $em->flush(); // Exécute la requête préparée
        }

        // Refactor cette méthode pour récupérer les informations de l'utilisateur connecté
        return $this->render('user/userSettings/settings.html.twig', [
            'editUserForm' => $editUserForm
        ]);
    }
}
