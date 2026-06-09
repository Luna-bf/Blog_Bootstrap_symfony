<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\UserSettingsType;
use App\Repository\PostRepository;
use App\Service\BannerUploader;
use App\Service\ProfilePictureUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
        $posts = $repo->findBy(['myUser' => $user_id]); // Récupère tous les posts associés à l'utilisateur connecté
        $message = "";

        if ($posts === []) {
            $message = "Vous n'avez aucune publication.";
        }

        return $this->render('user/index.html.twig', [
            'posts' => $posts,
            'message' => $message,
        ]);
    
    }

    #[Route('/userProfile/{id}', name: 'show_profile')]
    public function showPostProfile(User $user, Security $security)
    {
        $page = "";
        $message = "";

        // Récupère les posts de l'utilisateur
        $posts = $user->getPosts();
        $authenticatedUser = $security->getUser();

        if($user === $authenticatedUser) {
            $page = "user/index.html.twig";
        } else {
            $page = "post/show/showProfile.html.twig";
        }

        return $this->render("$page", [
            'user' => $user,
            'posts' => $posts,
            'message' => $message
        ]);
    }

    /*
    La ligne "#[CurrentUser] User $user" va récupérer toutes les informations de l'utilisateur actuellement connecté :

    - User $user : injecte l'objet User (l'entité) dans la variable $user.
    - #[CurrentUser] : est un attribut PHP qui va me permettre de récupérer toutes les informations de l'utilisateur connecté
    */
    #[Route('/settings', name: 'settings')]
    public function settings(#[CurrentUser] User $user, Request $request, EntityManagerInterface $em, ProfilePictureUploader $profilePictureUploader, BannerUploader $bannerUploader): Response
    {
        /* J'ai ajouté un nouveau paramètre dans le fichier services.yaml nommé "user_images_directory", qui va contenir toutes
        les images associées à l'utilisateur */
        $oldProfilePicture = $profilePictureUploader->getTargetDirectory() . '/' . $user->getProfilePictureName(); // Récupère la photo de profil actuelle
        $newProfilePicture = "";

        $oldBanner = $bannerUploader->getTargetDirectory() . '/' . $user->getBannerName(); // Récupère la bannière de profil actuelle
        $newBanner = "";

        // Initialisation du formulaire
        $editUserForm = $this->createForm(UserSettingsType::class, $user);

        // Traitement du formulaire
        $editUserForm->handleRequest($request);

        // Si le formulaire est envoyé (isSubmitted) et que ses données sont valides (isValid())
        if ($editUserForm->isSubmitted() && $editUserForm->isValid()) {

            $newUsername = $editUserForm->get('username')->getData();
            $newProfilePicture = $editUserForm->get('profile_picture_name')->getData();
            $newBanner = $editUserForm->get('banner_name')->getData();

            if($newUsername) {
                $user->setUsername($newUsername);
            }

            if ($newProfilePicture) {

                if (is_dir($oldBanner)) {

                    // Utilise le service "ProfilePictureUploader" pour traiter l'envoi du fichier
                    $newFilename = $profilePictureUploader->upload($newProfilePicture);

                    // Stocke le nom du fichier dans la BDD
                    $user->setProfilePictureName($newFilename);

                    // Sinon, si je récupère un fichier :
                } else {
                    unlink($oldProfilePicture);

                    // Utilise le service "ProfilePictureUploader" pour traiter l'envoi du fichier
                    $newFilename = $profilePictureUploader->upload($newProfilePicture);

                    // Met à jour le nom du fichier dans la BDD
                    $user->setProfilePictureName($newFilename);
                }
            }

            if ($newBanner) {

                if (is_dir($oldBanner)) {

                    // Utilise le service "BannerUploader" pour traiter l'envoi du fichier
                    $newFilename = $bannerUploader->upload($newBanner);

                    // Stocke le nom du fichier dans la BDD
                    $user->setBannerName($newFilename);

                    // Sinon, si je récupère un fichier :
                } else {
                    unlink($oldBanner);

                    // Utilise le service "BannerUploader" pour traiter l'envoi du fichier
                    $newFilename = $bannerUploader->upload($newBanner);

                    // Met à jour le nom du fichier dans la BDD
                    $user->setBannerName($newFilename);
                }
            }

            $em->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/userSettings/settings.html.twig', [
            'editUserForm' => $editUserForm
        ]);
    }

    #[Route('/settings/emailReset', name: 'email_reset')]
    public function emailReset(#[CurrentUser] User $user, Request $request, EntityManagerInterface $em): Response
    {
        // Initialisation du formulaire
        $editUserForm = $this->createForm(UserSettingsType::class, $user);

        // Traitement du formulaire
        $editUserForm->handleRequest($request);
        
        if ($editUserForm->isSubmitted() && $editUserForm->isValid()) {

            $newEmail = $editUserForm->get('email')->getData();
            
            $user->setEmail($newEmail);

            $em->flush();

            $this->addFlash('success', 'Adresse mail modifiée avec succès.');

            return $this->redirectToRoute('user_settings');
        }

        return $this->render('user/userSettings/emailReset.html.twig', [
            'editUserForm' => $editUserForm
        ]);
    }

    #[Route('/settings/passwordReset', name: 'password_reset')]
    public function passwordReset(#[CurrentUser] User $user, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em): Response
    {
        // Initialisation du formulaire
        $resetPasswordForm = $this->createForm(ChangePasswordFormType::class, $user);

        // Traitement du formulaire
        $resetPasswordForm->handleRequest($request);

        if ($resetPasswordForm->isSubmitted() && $resetPasswordForm->isValid()) {

            $plainPassword = $resetPasswordForm->get('plainPassword')->getData();

            // Stocke une version hachée (sécurisée) du mot de passe dans la base de données
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $em->flush();

            $this->addFlash('success', 'Mot de passe modifié avec succès.');

            return $this->redirectToRoute('user_settings');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetPasswordForm' => $resetPasswordForm
        ]);
    }
}
