<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProjectController extends AbstractController
{
    #[Route('/projects/', name: 'app_project_index')]
    public function index(ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findBy(['published' => true], ['startDate' => 'DESC']);

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/projects/{id<\d+>}', name: 'app_project_show_by_id')]
    public function showById(Project $project): Response
    {
        if (!$project->isPublished() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createNotFoundException('This project is not published.');
        }

        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }

    // It could be useful to have a slug-based URL for SEO and readability
    #[Route('/projects/{slug}', name: 'app_project_show_by_slug')]
    public function showBySlug(string $slug, ProjectRepository $projectRepository): Response
    {
        // This assumes you will add a "slug" field to your Project entity
        // and a method to find by slug in your ProjectRepository.
        // For now, this is a placeholder. If you add a slug field,
        // you'll need to generate slugs (e.g. using StofDoctrineExtensionsBundle)
        // and modify the repository method.

        // $project = $projectRepository->findOneBy(['slug' => $slug, 'published' => true]);
        // if (!$project) {
        //     // If not found or not published, try finding by ID if slug is numeric and admin is viewing
        //     if (ctype_digit($slug) && $this->isGranted('ROLE_ADMIN')) {
        //         $project = $projectRepository->findOneBy(['id' => (int)$slug]);
        //     }
        //     if (!$project || (!$project->isPublished() && !$this->isGranted('ROLE_ADMIN'))) {
        //          throw $this->createNotFoundException('The project does not exist or is not published.');
        //     }
        // }
        // For now, we'll just use the ID based show method to avoid errors until slug is implemented
        // This is not ideal as it means two routes can point to the same content.
        // Consider removing this or fully implementing slugs.
        if (ctype_digit($slug)) {
            $project = $projectRepository->findOneBy(['id' => (int) $slug]);
            if ($project) {
                if (!$project->isPublished() && !$this->isGranted('ROLE_ADMIN')) {
                    throw $this->createNotFoundException('This project is not published.');
                }

                return $this->redirectToRoute('app_project_show_by_id', ['id' => $project->getId()], 301);
            }
        }
        throw $this->createNotFoundException('Project not found.');


        // return $this->render('project/show.html.twig', [
        //     'project' => $project,
        // ]);
    }
}
