<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\AboutMe;
use App\Entity\Article;
use App\Entity\ArticleContent;
use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Hobby;
use App\Entity\Project;
use App\Entity\ProjectImage;
use App\Entity\SkillCategory;
use App\Entity\SoftSkill;
use App\Entity\Technology;
use App\Entity\User;
use App\Enum\Role;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
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
            ->setTitle('Administration du Portfolio')
            ->setFaviconPath('/favicon.ico')
            ->setTranslationDomain('messages')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        // Portfolio Content
        yield MenuItem::section('Contenu du Portfolio');

        $aboutMeEntryId  = $this->getAboutMeEntryId();
        $aboutMeMenuItem = MenuItem::linkToCrud('À Propos', 'fa fa-address-card', AboutMe::class)
            ->setController(AboutMeCrudController::class);

        $aboutMeMenuItem->setAction(null !== $aboutMeEntryId ? Crud::PAGE_EDIT : Crud::PAGE_INDEX)
            ->setEntityId($aboutMeEntryId);
        yield $aboutMeMenuItem;

        yield MenuItem::subMenu('Projets', 'fa fa-project-diagram')->setSubItems([
            MenuItem::linkToCrud('Projets', 'fa fa-project-diagram', Project::class),
            MenuItem::linkToCrud('Images des Projets', 'fa fa-images', ProjectImage::class),
        ]);

        yield MenuItem::subMenu('Articles', 'fa fa-newspaper')->setSubItems([
            MenuItem::linkToCrud('Articles', 'fa fa-newspaper', Article::class),
            MenuItem::linkToCrud('Blocs de contenu', 'fa fa-cubes', ArticleContent::class)
                ->setController(ArticleContentCrudController::class),
        ]);

        // Skills & Experience
        yield MenuItem::section('Compétences & Expérience');

        yield MenuItem::subMenu('Compétences', 'fa fa-cogs')->setSubItems([
            MenuItem::linkToCrud('Catégories de Compétences', 'fa fa-tags', SkillCategory::class),
            MenuItem::linkToCrud('Technologies', 'fa fa-microchip', Technology::class),
            MenuItem::linkToCrud('Soft Skills', 'fa fa-handshake', SoftSkill::class),
        ]);

        yield MenuItem::subMenu('Parcours', 'fa fa-book')->setSubItems([
            MenuItem::linkToCrud('Éducation', 'fa fa-graduation-cap', Education::class),
            MenuItem::linkToCrud('Expérience', 'fa fa-briefcase', Experience::class),
        ]);

        yield MenuItem::linkToCrud('Hobbies', 'fa fa-gamepad', Hobby::class);

        // Administration
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            yield MenuItem::section('Administration');
            yield MenuItem::subMenu('Utilisateurs', 'fa fa-users')->setSubItems([
                MenuItem::linkToCrud('Tous les Utilisateurs', 'fa fa-user', User::class),
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
