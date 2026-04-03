<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user', name: 'user_')]
final class UserController extends AbstractController
{
    #[Route('/user', name: 'index')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'user' => 'Hello World !',
        ]);
    }
}
