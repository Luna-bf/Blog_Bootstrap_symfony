<?php

namespace App\Controller;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/category', name: 'category')]
final class CategoryController extends AbstractController
{
    #[Route('/{id}', name: '_show')]
    public function index(Category $category): Response
    {
        $posts = $category->getPosts();

        return $this->render('category/index.html.twig', [
            'category' => $category,
            'posts' => $posts
        ]);
    }
}
