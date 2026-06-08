<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/projects/',
    name: 'app_project_index',
    methods: ['GET']
)]
final class ProjectIndexController extends AbstractController
{
    public function __invoke(ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findBy(
            ['published' => true],
            ['startDate' => 'DESC']
        );

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }
}
