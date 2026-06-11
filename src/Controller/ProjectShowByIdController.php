<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/projects/{id<\d+>}',
    name: 'app_project_show_by_id',
    methods: ['GET']
)]
final class ProjectShowByIdController extends AbstractController
{
    public function __invoke(Project $project): Response
    {
        if (!$project->isPublished() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createNotFoundException('This project is not published.');
        }

        if ($project->getSlug()) {
            return $this->redirectToRoute(
                'app_project_show_by_slug',
                ['slug' => $project->getSlug()],
                301
            );
        }

        return $this->render(
            'project/show.html.twig',
            [
                'project' => $project,
            ]
        );
    }
}
