<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ProjectImage;
use App\Service\Admin\ProjectImageFieldsConfigurationService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/**
 * @extends AbstractCrudController<ProjectImage>
 */
class ProjectImageCrudController extends AbstractCrudController
{
    use AdminCrudControllerTrait;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly ProjectImageFieldsConfigurationService $fieldsService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return ProjectImage::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $this->configureCommonCrud($crud, 'Image de projet', 'Images de projet')
            ->setDefaultSort(['project' => 'ASC', 'displayOrder' => 'ASC'])
            ->addFormTheme('@VichUploader/Form/fields.html.twig');
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        return $this->configureSoftDeleteActions(
            $this->configureCommonActions($actions)
        );
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('project', 'Projet'));
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
