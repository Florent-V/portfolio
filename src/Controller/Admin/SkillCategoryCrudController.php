<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SkillCategory;
use App\Service\Admin\SkillCategoryFieldsConfigurationService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/**
 * @extends AbstractCrudController<SkillCategory>
 */
class SkillCategoryCrudController extends AbstractCrudController
{
    use AdminCrudControllerTrait;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly SkillCategoryFieldsConfigurationService $fieldsService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return SkillCategory::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $this->configureCommonCrud($crud, 'Skill Category', 'Skill Categories')
            ->setSearchFields(['name', 'icon'])
            ->setDefaultSort(['displayOrder' => 'ASC', 'name' => 'ASC'])
            ->setPaginatorPageSize(20);
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $adminUrlGenerator = $this->adminUrlGenerator;

        $viewTechnologies = Action::new('viewTechnologies', 'View Technologies', 'fa fa-list')
            ->linkToUrl(static function (SkillCategory $entity) use ($adminUrlGenerator): string {
                return $adminUrlGenerator
                    ->setController(TechnologyCrudController::class)
                    ->setAction('index')
                    ->set('filters[category][comparison]', '=')
                    ->set('filters[category][value]', (string) $entity->getId())
                    ->generateUrl();
            })
            ->setHtmlAttributes(['title' => 'View all technologies in this category']);

        $baseActions = $this->configureSoftDeleteActions(
            $this->configureCommonActions($actions)
        );

        return $baseActions
            ->add(Crud::PAGE_INDEX, $viewTechnologies)
            ->add(Crud::PAGE_INDEX, $this->buildDuplicateAction())
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $a) => $a
                ->displayIf(static fn (SkillCategory $e) => $e->getTechnologies()->isEmpty()));
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', 'Nom'))
            ->add(TextFilter::new('icon', 'Icône'));
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return $this->fieldsService->getFieldsForPage($pageName, $this->getContext());
    }

    protected function getAdminUrlGenerator(): AdminUrlGenerator
    {
        return $this->adminUrlGenerator;
    }
}
