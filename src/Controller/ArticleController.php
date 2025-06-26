<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ArticleController extends AbstractController
{
    #[Route('/articles/', name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        // Paginer les résultats serait une bonne amélioration ici
        $articles = $articleRepository->findPublished(10); // Get 10 latest published articles

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/articles/{slug}', name: 'app_article_show', methods: ['GET'])]
    public function show(string $slug, ArticleRepository $articleRepository): Response
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
