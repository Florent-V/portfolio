<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Enum\Role;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    path: '/admin/articles/{slug}/preview',
    name: 'admin_article_preview',
    methods: ['GET']
)]
#[IsGranted(Role::ADMIN->value)]
final class ArticlePreviewController extends AbstractController
{
    public function __invoke(string $slug, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->findOneBySlug($slug);

        if (!$article) {
            throw $this->createNotFoundException('Article introuvable.');
        }

        return $this->render('article/show.html.twig', [
            'article'   => $article,
            'isPreview' => true,
        ]);
    }
}
