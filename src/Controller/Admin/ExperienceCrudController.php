<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Experience;
use App\Service\Admin\ExperienceFieldsConfigurationService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/**
 * @extends AbstractCrudController<Experience>
 */
class ExperienceCrudController extends AbstractCrudController
{
    use AdminCrudControllerTrait;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly ExperienceFieldsConfigurationService $fieldsService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Experience::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $this->configureCommonCrud($crud, 'Expérience', 'Expériences')
            ->setSearchFields(['jobTitle', 'company', 'description'])
            ->setDefaultSort(['startDate' => 'DESC']);
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        return $this->configureSoftDeleteActions(
            $this->configureCommonActions($actions)
        )
            ->add(Crud::PAGE_INDEX, $this->buildDuplicateAction());
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('jobTitle', 'Poste'))
            ->add(TextFilter::new('company', 'Entreprise'))
            ->add(DateTimeFilter::new('startDate', 'Date de début'))
            ->add(DateTimeFilter::new('endDate', 'Date de fin'));
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
