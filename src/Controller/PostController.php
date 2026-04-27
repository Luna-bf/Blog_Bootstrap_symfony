<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/', name: 'post_')]
final class PostController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(PostRepository $postRepo, CategoryRepository $categoryRepo): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepo->findAll(),
            'categories' => $categoryRepo->findAll(),
        ]);
    }

    #[Route('/post/{id}', name: 'show')]
    public function showPost(Post $post)
    {

        return $this->render('post/showPost.html.twig', [
            'post' => $post
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/post/forms/create', name: 'create')]
    public function createNewPost(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/uploads/images')] string $imagesDirectory): Response
    {
        $message = ""; // Initialisation de la variable contenant le message d'erreur pour les fichiers (FileException)

        // Nouvelle instance de la classe (entité) Post
        $post = new Post;

        // Initialisation du formulaire
        $createPostForm = $this->createForm(PostType::class, $post, [
            // Ici, l'input "image_name" est requis pour envoyer le formulaire, contrairement à la méthode updatePost()
            'is_file_required' => true
        ]);

        // Traitement du formulaire
        $createPostForm->handleRequest($request);

        // Si le formulaire est envoyé (isSubmitted) et que ces données sont valides (isValid())
        if ($createPostForm->isSubmitted() && $createPostForm->isValid()) {

            // Récupère la valeur de l'input "image_name" et la stocke dans la variable $image
            $image = $createPostForm->get('image_name')->getData();

            // Je récupère toutes les données du formulaire (rempli) et les injectes dans l'objet $post
            $completePost = $createPostForm->getData();

            // Si la valeur du champ de saisie "image_name" (stockée dans la variable $image) n'est pas vide...
            if ($image) {
                // Je récupère le nom original de l'image
                $originalImageName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);

                // Puis j'utilise un slug pour créer un nom sécurisé
                $safeFilename = $slugger->slug($originalImageName);

                /* Enfin, je crée le nom définitif de l'image en utilisant la valeur de la variable $safeFileName, j'inclus
                un identifiant unique grâce à la fonction "uniqid()", puis je précise l'extension de l'image grâce à la fonction
                "guessExtension()" appliquée sur la variable $image.
                */
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                try {
                    // Envoie l'image dans le dossier adéquat
                    $image->move($imagesDirectory, $newFilename);
                } catch (FileException $e) {
                    $message = $e;
                }

                // Stocke le nom de l'image dans la BDD
                $post->setImageName($newFilename);
            }

            /*
            La date (DateTimeImmutable) sera envoyée par le contrôleur grâce au setter "setCreatedAt". Effectuer l'envoi de cette
            donnée côté serveur est plus sécurisé car l'utilisateur n'y aura jamais accès (envoyer cette donnée via un formulaire
            lui donnerais la possibilité de modifier une donnée sensible, ce qui n'est pas désirable).
            */
            $completePost->setCreatedAt(new \DateTimeImmutable());

            $user = $this->getUser(); // Je récupère les données l'utilisateur connecté
            $completePost->setMyUser($user); // Puis j'associe les données de l'utilisateur connecté à la publication (setter)

            $em->persist($completePost); // Prépare la requête (ici, la création d'un nouveau post)
            $em->flush(); // Exécute la requête préparée

            $this->addFlash('success', 'La publication a été créée avec succès.');
            return $this->redirectToRoute('user_index');
        }

        return $this->render('post/forms/createPost.html.twig', [
            'createPostForm' => $createPostForm,
            'message' => $message
        ]);
    }

    #[IsGranted('ROLE_USER')]
    // {id} est un paramètre dynamique : il va récupérer l'identifiant associé au post à modifier pour afficher le formulaire adéquat
    #[Route('/post/forms/update/{id}', name: 'update')]
    public function updatePost(Post $post, Request $request, EntityManagerInterface $em, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/uploads/images')] string $imagesDirectory): Response
    {
        $oldImage = $this->getParameter("images_directory") . '/' . $post->getImageName(); // Récupère l'ancienne image du post (l'image actuelle)
        $newImage = ""; // Initialisation de la variable qui va récupérer la nouvelle image (si il y en a une)
        $message = ""; // Initialisation de la variable contenant le message d'erreur pour les fichiers (FileException)

        // Initialisation du formulaire
        $updatePostForm = $this->createForm(PostType::class, $post, [ // $post représente une ligne récupérée dans la BDD
            // Ici, l'input "image_name" n'est pas requis pour envoyer le formulaire, contrairement à la méthode createPost()
            'is_file_required' => false
        ]);

        // Traitement du formulaire (injecte les nouvelles valeurs à la variable $post si il y en a)
        $updatePostForm->handleRequest($request);

        // Si le formulaire est envoyé (isSubmitted) et que ces données sont valides (isValid())
        if ($updatePostForm->isSubmitted() && $updatePostForm->isValid()) {

            $newImage = $updatePostForm->get('image_name')->getData(); // Je récupère la nouvelle image (si il y en a une)

            /*
            J'ajoute ensuite une condition : si l'utilisateur envoie une nouvelle image dans le formulaire, je supprime
            l'ancienne ($oldImage) et la remplace par la nouvelle ($newImage)
            */
            if ($newImage) {
                unlink($oldImage);

                // Je récupère le nom original de la nouvelle image
                $originalImageName = pathinfo($newImage->getClientOriginalName(), PATHINFO_FILENAME);

                // Puis j'utilise un slug pour créer un nom sécurisé
                $safeFilename = $slugger->slug($originalImageName);

                /* Enfin, je crée le nom définitif de l'image en utilisant la valeur de la variable $safeFileName, j'inclus
                un identifiant unique grâce à la fonction "uniqid()", puis je précise l'extension de l'image grâce à la fonction
                "guessExtension()" appliquée sur la variable $image.
                */
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $newImage->guessExtension();

                try {
                    // Envoie l'image dans le dossier adéquat
                    $newImage->move($imagesDirectory, $newFilename);
                } catch (FileException $error) {
                    $message = $error;
                }

                // Stocke le nom de la nouvelle image dans la BDD
                $post->setImageName($newFilename);
            }

            $em->persist($post);
            $em->flush(); // Exécute la requête préparée

            $this->addFlash('success', 'La publication a été modifiée avec succès.');
            return $this->redirectToRoute('user_index');
        }

        return $this->render('post/forms/updatePost.html.twig', [
            'updatePostForm' => $updatePostForm,
            'message' => $message
        ]);
    }

    #[IsGranted('ROLE_USER')]
    // {id} est un paramètre dynamique : il va récupérer l'identifiant associé au post à modifier pour afficher le formulaire adéquat
    #[Route('/post/{id}/delete', name: 'delete')]
    public function deletePost(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        // Récupère la valeur de l'input nommé "token" (le jeton CSRF)
        $submittedToken = $request->getPayload()->get('token');

        // Récupère l'image du post
        $postImage = $post->getImageName();

        // Vérifie si le jeton CSRF nommé "delete-post" correspond à la valeur récupérée par la variable $submittedToken
        if ($this->isCsrfTokenValid('delete-post', $submittedToken)) {

            // Si le post contient un nom d'image
            if ($postImage) {
                // Je récupère son chemin d'accès (nom du dossier et de l'image associée au post) et le stocke dans la variable $image
                $image = $this->getParameter("images_directory") . '/' . $post->getImageName();

                // Si le chemin de l'image récupérée correspond à l'une des images du dossier
                if (file_exists($image)) {
                    unlink($image); // Alors je la supprime
                }
            }

            // Une fois cela fait, je supprime entièrement le post
            $em->remove($post);
            $em->flush();

            $this->addFlash('success', 'La publication a été supprimée avec succès.');
            return $this->redirectToRoute('user_index');
        } else {
            throw new Exception("Le jeton CSRF est invalide.");
        }
    }
}
