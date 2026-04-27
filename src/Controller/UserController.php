<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserSettingsType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

// #[IsGranted('ROLE_USER')] : Ces routes ne sont accessibles que si l'utilisateur est connecté
#[IsGranted('ROLE_USER')]
#[Route('/user', name: 'user_')]
final class UserController extends AbstractController
{
    /*
    La ligne "#[CurrentUser] User $user" va récupérer toutes les informations de l'utilisateur actuellement connecté :

    - User $user : injecte l'objet (l'entité) User dans la variable $user.
    - #[CurrentUser] : est un attribut PHP qui va me permettre de récupérer toutes les informations de l'utilisateur connecté
    */
    #[Route('', name: 'index')]
    public function index(PostRepository $repo, #[CurrentUser] User $user): Response
    {
        $user_id = $user->getId(); // Récupère l'identifiant de l'utilisateur actuellement connecté
        $posts = $repo->findBy(['my_user' => $user_id]); // Récupère tous les posts associés à l'utilisateur connecté
        $message = "";

        if ($posts === []) {
            $message = "Vous n'avez aucune publication.";
        }

        return $this->render('user/index.html.twig', [
            'posts' => $posts,
            'message' => $message,
        ]);
    }


    /*
    La ligne "#[CurrentUser] User $user" va récupérer toutes les informations de l'utilisateur actuellement connecté :

    - User $user : injecte l'objet User (l'entité) dans la variable $user.
    - #[CurrentUser] : est un attribut PHP qui va me permettre de récupérer toutes les informations de l'utilisateur connecté
    */
    #[Route('/settings', name: 'settings')]
    public function settings(#[CurrentUser] User $user): Response {
        
        $editUserForm = $this->createForm(UserSettingsType::class, $user);

        return $this->render('user/userSettings/settings.html.twig', [
            'editUserForm' => $editUserForm
        ]);
    }

    #[Route('/settings/profilePicture', name: 'profile_picture_settings')]
    public function profilePictureSettings(#[CurrentUser] User $user, Request $request, EntityManagerInterface $em, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/uploads/user_images/profile_pictures')] string $profilePicturesDirectory): Response
    {
        /* J'ai ajouté un nouveau paramètre dans le fichier services.yaml nommé "user_images_directory", qui va contenir toutes
        les images associées à l'utilisateur */
        $oldProfilePicture = $this->getParameter("user_images_directory") . '/profile_pictures/' . $user->getProfilePictureName(); // Récupère la photo de profil actuelle
        $newProfilePicture = "";

        // Initialisation du formulaire
        $editUserForm = $this->createForm(UserSettingsType::class, $user);

        // Traitement du formulaire
        $editUserForm->handleRequest($request);

        // Si le formulaire est envoyé (isSubmitted) et que ses données sont valides (isValid())
        if ($editUserForm->isSubmitted() && $editUserForm->isValid()) {
            
            // Récupère la valeur de l'input "profile_picture_name" et la stocke dans la variable $newProfilePicture
            $newProfilePicture = $editUserForm->get('profile_picture_name')->getData();

            if ($newProfilePicture) {
                unlink($oldProfilePicture);

                // Je récupère le nom original de l'image
                $originalImageName = pathinfo($newProfilePicture->getClientOriginalName(), PATHINFO_FILENAME);

                // Puis j'utilise un slug pour créer un nom sécurisé
                $safeFilename = $slugger->slug($originalImageName);

                /* Enfin, je crée le nom définitif de l'image en utilisant la valeur de la variable $safeFileName, j'inclus
                un identifiant unique grâce à la fonction "uniqid()", puis je précise l'extension de l'image grâce à la fonction
                "guessExtension()" appliquée sur la variable $image.
                */
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $newProfilePicture->guessExtension();

                try {
                    // Envoie l'image dans le dossier adéquat
                    $newProfilePicture->move($profilePicturesDirectory, $newFilename);
                } catch (FileException $e) {
                    $message = $e;
                }

                // Stocke le nom de l'image dans la BDD
                $user->setProfilePictureName($newFilename);
            }

            $em->persist($user); // Prépare la requête
            $em->flush(); // Exécute la requête préparée

            return $this->redirectToRoute('user_index');
        }
        
        return $this->render('user/userSettings/pictures/profilePictureSettings.html.twig', [
            'editUserForm' => $editUserForm
        ]);
    }

    #[Route('/settings/banner', name: 'banner_settings')]
    public function bannerSettings(#[CurrentUser] User $user, Request $request, EntityManagerInterface $em, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/uploads/user_images/banners')] string $bannersDirectory): Response {
        
        /* J'ai ajouté un nouveau paramètre dans le fichier services.yaml nommé "user_images_directory", qui va contenir toutes
        les images associées à l'utilisateur */
        $oldBanner = $this->getParameter("user_images_directory") . '/banners/' . $user->getBannerName(); // Récupère la photo de profil actuelle
        $newBanner = "";

        // Initialisation du formulaire
        $editUserForm = $this->createForm(UserSettingsType::class, $user);

        // Traitement du formulaire
        $editUserForm->handleRequest($request);

        // Si le formulaire est envoyé (isSubmitted) et que ses données sont valides (isValid())
        if ($editUserForm->isSubmitted() && $editUserForm->isValid()) {
            
            // Récupère la valeur de l'input "banner_name" et la stocke dans la variable $newBanner
            $newBanner = $editUserForm->get('banner_name')->getData();

            if ($newBanner) {
                unlink($oldBanner);

                // Je récupère le nom original de l'image
                $originalImageName = pathinfo($newBanner->getClientOriginalName(), PATHINFO_FILENAME);

                // Puis j'utilise un slug pour créer un nom sécurisé
                $safeFilename = $slugger->slug($originalImageName);

                /* Enfin, je crée le nom définitif de l'image en utilisant la valeur de la variable $safeFileName, j'inclus
                un identifiant unique grâce à la fonction "uniqid()", puis je précise l'extension de l'image grâce à la fonction
                "guessExtension()" appliquée sur la variable $image.
                */
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $newBanner->guessExtension();

                try {
                    // Envoie l'image dans le dossier adéquat
                    $newBanner->move($bannersDirectory, $newFilename);
                } catch (FileException $e) {
                    $message = $e;
                }

                // Stocke le nom de l'image dans la BDD
                $user->setBannerName($newFilename);
            }

            $em->persist($user); // Prépare la requête
            $em->flush(); // Exécute la requête préparée

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/userSettings/pictures/bannerSettings.html.twig', [
            'editUserForm' => $editUserForm
        ]);
    }
}
