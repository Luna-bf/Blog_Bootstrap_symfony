<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'post_')]
final class PostController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(PostRepository $repo): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $repo->findAll(),
        ]);
    }

    #[Route('/post/forms/create', name: 'create')]
    public function createNewPost(Request $request, EntityManagerInterface $em): Response
    {
        // Nouvelle instance de la classe (entité) Post
        $post = new Post;

        // Initialisation du formulaire
        $createPostForm = $this->createForm(PostType::class, $post);

        // Traitement du formulaire
        $createPostForm->handleRequest($request);

        // Si le formulaire est envoyé (isSubmitted) et que ces données sont valides (isValid())
        if ($createPostForm->isSubmitted() && $createPostForm->isValid()) {

            // Je récupère toutes les données du formulaire (rempli) et les injectes dans l'objet $post
            $completePost = $createPostForm->getData();

            // J'ajoute la date de création (setter) au champ createdAt du formulaire (caché)
            $completePost->setCreatedAt(new \DateTimeImmutable());

            $user = $this->getUser(); // Je récupère les données l'utilisateur connecté
            $completePost->setMyUser($user); // Puis j'associe les données de l'utilisateur connecté à la publication (setter)

            $em->persist($completePost); // Prépare la requête (ici, la création d'un nouveau post)
            $em->flush(); // Exécute la requête préparée

            return $this->redirectToRoute('user_index');
        }

        return $this->render('post/forms/createPost.html.twig', [
            'createPostForm' => $createPostForm,
        ]);
    }
}
