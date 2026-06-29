<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/projects/{slug}',
    name: 'app_project_show_by_slug',
    methods: ['GET']
)]
final class ProjectShowBySlugController extends AbstractController
{
    public function __invoke(
        string $slug,
        ProjectRepository $projectRepository,
        ArticleRepository $articleRepository,
    ): Response {
        $project = $projectRepository->findOneBy(['slug' => $slug]);

        if (!$project || (!$project->isPublished() && !$this->isGranted('ROLE_ADMIN'))) {
            throw $this->createNotFoundException('Project not found.');
        }

        $relatedArticles = $articleRepository->findPublishedByTags($project->getTags(), limit: 5);

        return $this->render('project/show.html.twig', [
            'project'         => $project,
            'relatedArticles' => $relatedArticles,
        ]);
    }
}
