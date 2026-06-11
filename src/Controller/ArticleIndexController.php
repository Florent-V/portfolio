<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/articles/',
    name: 'app_article_index',
    methods: ['GET']
)]
final class ArticleIndexController extends AbstractController
{
    private const int PAGE_SIZE = 9;

    public function __invoke(ArticleRepository $articleRepository, Request $request): Response
    {
        $page     = max(1, $request->query->getInt('page', 1));
        $total    = $articleRepository->countPublished();
        $lastPage = max(1, (int) ceil($total / self::PAGE_SIZE));
        $page     = min($page, $lastPage);

        $articles = $articleRepository->findPublished(self::PAGE_SIZE, ($page - 1) * self::PAGE_SIZE);

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'page'     => $page,
            'lastPage' => $lastPage,
            'total'    => $total,
        ]);
    }
}
