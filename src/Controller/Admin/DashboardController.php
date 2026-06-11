<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\AboutMe;
use App\Enum\Role;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted(Role::ADMIN->value)]
class DashboardController extends AbstractDashboardController
{
    public function __construct(private readonly ManagerRegistry $doctrine)
    {
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

        $url = $adminUrlGenerator->setController(AboutMeCrudController::class)
            ->setAction(Crud::PAGE_INDEX)
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
        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),
            ...$this->portfolioContentMenuItems(),
            ...$this->skillsAndExperienceMenuItems(),
            ...$this->aiMenuItems(),
            ...($this->isGranted('ROLE_SUPER_ADMIN') ? $this->administrationMenuItems() : []),
        ];
    }

    /** @return array<MenuItemInterface> */
    private function portfolioContentMenuItems(): array
    {
        $aboutMeEntryId  = $this->getAboutMeEntryId();
        $aboutMeMenuItem = MenuItem::linkTo(
            AboutMeCrudController::class,
            'À Propos',
            'fa fa-address-card'
        )->setAction(null !== $aboutMeEntryId ? Crud::PAGE_EDIT : Crud::PAGE_INDEX);

        if (null !== $aboutMeEntryId) {
            $aboutMeMenuItem->setEntityId($aboutMeEntryId);
        }

        return [
            MenuItem::section('Contenu du Portfolio'),
            $aboutMeMenuItem,
            MenuItem::linkTo(
                SocialCrudController::class,
                'Liens Sociaux',
                'fa fa-share-alt'
            ),
            MenuItem::subMenu('Projets', 'fa fa-project-diagram')
                ->setSubItems([
                    MenuItem::linkTo(
                        ProjectCrudController::class,
                        'Projets',
                        'fa fa-project-diagram'
                    ),
                    MenuItem::linkTo(
                        ProjectImageCrudController::class,
                        'Images des Projets',
                        'fa fa-images'
                    ),
                ]),
            MenuItem::subMenu('Articles', 'fa fa-newspaper')
                ->setSubItems([
                    MenuItem::linkTo(
                        ArticleCrudController::class,
                        'Articles',
                        'fa fa-newspaper'
                    ),
                    MenuItem::linkTo(
                        ArticleContentCrudController::class,
                        'Blocs de contenu',
                        'fa fa-cubes'
                    ),
                ]),
        ];
    }

    /** @return array<MenuItemInterface> */
    private function skillsAndExperienceMenuItems(): array
    {
        return [
            MenuItem::section('Compétences & Expérience'),
            MenuItem::subMenu('Compétences', 'fa fa-cogs')
                ->setSubItems([
                    MenuItem::linkTo(
                        SkillCategoryCrudController::class,
                        'Catégories de Compétences',
                        'fa fa-tags'
                    ),
                    MenuItem::linkTo(
                        TechnologyCrudController::class,
                        'Technologies',
                        'fa fa-microchip'
                    ),
                    MenuItem::linkTo(
                        SoftSkillCrudController::class,
                        'Soft Skills',
                        'fa fa-handshake'
                    ),
                ]),
            MenuItem::subMenu('Parcours', 'fa fa-book')
                ->setSubItems([
                    MenuItem::linkTo(
                        EducationCrudController::class,
                        'Éducation',
                        'fa fa-graduation-cap'
                    ),
                    MenuItem::linkTo(
                        ExperienceCrudController::class,
                        'Expérience',
                        'fa fa-briefcase'
                    ),
                ]),
            MenuItem::linkTo(
                HobbyCrudController::class,
                'Hobbies',
                'fa fa-gamepad'
            ),
        ];
    }

    /** @return array<MenuItemInterface> */
    private function aiMenuItems(): array
    {
        return [
            MenuItem::section('Intelligence Artificielle'),
            MenuItem::linkToRoute(
                'Générer un article',
                'fa fa-robot',
                'admin_article_generator'
            ),
            MenuItem::linkToRoute(
                'Humaniser un texte',
                'fa fa-wand-magic-sparkles',
                'admin_article_humanizer'
            ),
            MenuItem::linkToRoute(
                'Importer un JSON',
                'fa fa-file-import',
                'admin_article_json_import'
            ),
        ];
    }

    /** @return array<MenuItemInterface> */
    private function administrationMenuItems(): array
    {
        return [
            MenuItem::section('Administration'),
            MenuItem::subMenu('Utilisateurs', 'fa fa-users')
                ->setSubItems([
                    MenuItem::linkTo(
                        UserCrudController::class,
                        'Tous les Utilisateurs',
                        'fa fa-user'
                    ),
                ]),
        ];
    }

    private function getAboutMeEntryId(): ?int
    {
        return $this->doctrine->getRepository(AboutMe::class)->findOneBy([])?->getId();
    }
}
