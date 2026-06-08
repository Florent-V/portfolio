<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/articles/{slug}',
    name: 'app_article_show',
    methods: ['GET']
)]
final class ArticleShowController extends AbstractController
{
    public function __invoke(string $slug, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->findOnePublishedBySlug($slug);

        if (!$article) {
            throw $this->createNotFoundException('L\'article demandé n\'a pas été trouvé ou n\'est pas publié.');
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }
}
