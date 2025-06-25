<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\AboutMe;
use App\Entity\Article;
use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Hobby;
use App\Entity\Project;
use App\Entity\SkillCategory;
use App\Entity\SoftSkill; // Added
use App\Entity\Technology;
use App\Entity\User;
use App\Enum\Role;
use Doctrine\Persistence\ManagerRegistry; // Added missing import
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response; // Added missing import for getDoctrine
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted(Role::ADMIN->value)]
class DashboardController extends AbstractDashboardController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        $aboutMeEntry = $this->doctrine->getRepository(AboutMe::class)->findOneBy([]);
        if ($aboutMeEntry) {
            $url = $adminUrlGenerator->setController(AboutMeCrudController::class)
                ->setAction(Crud::PAGE_EDIT)
                ->setEntityId($aboutMeEntry->getId())
                ->generateUrl();

            return $this->redirect($url);
        }
        // If no AboutMe entry, redirect to the index/creation page of AboutMeCrudController
        $url = $adminUrlGenerator->setController(AboutMeCrudController::class)
            ->setAction(Crud::PAGE_INDEX) // This will lead to 'new' if no entry, or 'edit' if one is created.
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Portfolio Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Portfolio Content');

        $aboutMeEntryId  = $this->getAboutMeEntryId();
        $aboutMeMenuItem = MenuItem::linkToCrud('About Me', 'fa fa-address-card', AboutMe::class)
            ->setController(AboutMeCrudController::class);

        $aboutMeMenuItem->setAction(null !== $aboutMeEntryId ? Crud::PAGE_EDIT : Crud::PAGE_INDEX)
            ->setEntityId($aboutMeEntryId);
        yield $aboutMeMenuItem;

        yield MenuItem::linkToCrud('Projects', 'fa fa-project-diagram', Project::class);
        yield MenuItem::linkToCrud('Articles', 'fa fa-newspaper', Article::class);
        yield MenuItem::subMenu('Skills & Technologies', 'fa fa-cogs')->setSubItems([
            MenuItem::linkToCrud('Skill Categories', 'fa fa-tags', SkillCategory::class), // For Hard Skills
            MenuItem::linkToCrud('Technologies', 'fa fa-microchip', Technology::class),   // Hard Skills
            MenuItem::linkToCrud('Soft Skills', 'fa fa-handshake', SoftSkill::class),    // Soft Skills
        ]);
        yield MenuItem::linkToCrud('Education', 'fa fa-graduation-cap', Education::class);
        yield MenuItem::linkToCrud('Experience', 'fa fa-briefcase', Experience::class);
        yield MenuItem::linkToCrud('Hobbies', 'fa fa-gamepad', Hobby::class);

        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            yield MenuItem::subMenu('Admin Settings', 'fa fa-users')->setSubItems([
                MenuItem::linkToCrud('All Users', 'fa fa-user', User::class),
            ]);
        }
    }

    private function getAboutMeEntryId(): ?int
    {
        $aboutMeRepository = $this->doctrine->getRepository(AboutMe::class);
        $aboutMe           = $aboutMeRepository->findOneBy([]);

        return $aboutMe ? $aboutMe->getId() : null;
    }
}
