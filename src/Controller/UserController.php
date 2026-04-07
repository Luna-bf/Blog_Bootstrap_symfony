<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user', name: 'user_')]
final class UserController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(PostRepository $repo): Response
    {
        return $this->render('user/index.html.twig', [
            'posts' => $repo->findAll(),
        ]);
    }
}
