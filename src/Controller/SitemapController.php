<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/sitemap.xml',
    name: 'app_sitemap',
    methods: ['GET']
)]
final class SitemapController extends AbstractController
{
    public function __invoke(
        ProjectRepository $projectRepository,
        ArticleRepository $articleRepository,
    ): Response {
        $projects = $projectRepository->findBy(['published' => true], ['updatedAt' => 'DESC']);
        $articles = $articleRepository->findPublished(1000);

        $response = new Response(
            $this->renderView('seo/sitemap.xml.twig', [
                'projects' => $projects,
                'articles' => $articles,
            ]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/xml; charset=UTF-8'],
        );

        $response->setPublic();
        $response->setMaxAge(86400);

        return $response;
    }
}
