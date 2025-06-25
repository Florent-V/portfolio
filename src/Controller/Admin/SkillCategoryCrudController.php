<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SkillCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class SkillCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SkillCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // Labels et titre
            ->setEntityLabelInSingular('Skill Category')
            ->setEntityLabelInPlural('Skill Categories')
            ->setPageTitle('index', '%entity_label_plural% Management')
            ->setPageTitle('new', 'Create new %entity_label_singular%')
            ->setPageTitle('edit', 'Edit %entity_label_singular%')
            ->setPageTitle('detail', '%entity_label_singular% Details')

            // Recherche
            ->setSearchFields(['name', 'icon'])

            // Tri par défaut
            ->setDefaultSort(['displayOrder' => 'ASC', 'name' => 'ASC'])

            // Pagination
            ->setPaginatorPageSize(20)
            ->setPaginatorRangeSize(4)

            // Options d'affichage
            ->showEntityActionsInlined()
            ->setAutofocusSearch()

            // Timestamps
            ->setTimezone('Europe/Paris')
            ->setDateTimeFormat('short', 'short')

            // Help
            ->setHelp(
                'index',
                'Manage skill categories that group your technologies. Use display order to control the sequence.'
            )
            ->setHelp(
                'new',
                'Create a new skill category to organize your technologies.'
            )
            ->setHelp(
                'edit',
                'Modify the skill category information. Changes will affect all associated technologies.'
            );
    }

    public function configureActions(Actions $actions): Actions
    {
        // Action personnalisée pour voir les technologies liées
        $viewTechnologies = Action::new('viewTechnologies', 'View Technologies', 'fa fa-list')
            ->linkToRoute('admin', [
                'crudAction'                    => 'index',
                'crudControllerFqcn'            => 'App\\Controller\\Admin\\TechnologyCrudController',
                'filters[category][comparison]' => '=',
                'filters[category][value]'      => '',
            ])
            ->setHtmlAttributes(['title' => 'View all technologies in this category']);

        return $actions
            // Personnaliser les actions sur la page index
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setIcon('fa fa-plus')
                    ->setLabel('New Category')
                    ->setCssClass('btn btn-primary');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit')->setLabel('Edit');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->setLabel('Delete')
                    ->displayIf(static function (SkillCategory $entity) {
                        // Ne permettre la suppression que si aucune technologie n'est liée
                        return $entity->getTechnologies()->isEmpty();
                    });
            })

            // Ajouter l'action personnalisée
            ->add(Crud::PAGE_INDEX, $viewTechnologies)

            // Actions sur les autres pages
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit');
            })
            ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa fa-trash')
                    ->displayIf(static function (SkillCategory $entity) {
                        return $entity->getTechnologies()->isEmpty();
                    });
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fa fa-save');
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fa fa-save');
            });
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', 'Category Name'))
            ->add(TextFilter::new('icon', 'Icon'))
            ->add(DateTimeFilter::new('createdAt', 'Created'))
            ->add(DateTimeFilter::new('updatedAt', 'Last Modified'));
    }

    public function configureFields(string $pageName): iterable
    {
        return match ($pageName) {
            Crud::PAGE_INDEX  => $this->getIndexFields(),
            Crud::PAGE_DETAIL => $this->getDetailFields(),
            Crud::PAGE_NEW, Crud::PAGE_EDIT => $this->getFormFields(),
            default => [],
        };
    }

    /**
     * @return FieldInterface[]
     */
    private function getIndexFields(): array
    {
        return [
            $this->createNameWithIconField(),
            $this->createDisplayOrderField(),
            $this->createTechnologiesField(),
            $this->createIconDisplayField(),
            $this->createCreatedAtField(),
        ];
    }

    /**
     * @return FieldInterface[]
     */
    private function getDetailFields(): array
    {
        return [
            $this->createNameWithIconField(),
            $this->createIconDisplayField(),
            $this->createDisplayOrderField(),
            $this->createTechnologiesField(),
            $this->createCreatedAtField(),
            $this->createUpdatedAtField(),
            $this->createCreatedByField(),
            $this->createUpdatedByField(),
            $this->createIsDeletedField(),
        ];
    }

    /**
     * @return FieldInterface[]
     */
    private function getFormFields(): array
    {
        return [
            $this->createNameField(),
            $this->createIconField(),
            $this->createDisplayOrderField(),
        ];
    }

    private function createNameField(): TextField
    {
        return TextField::new('name', 'Category Name')
            ->setRequired(true)
            ->setMaxLength(255)
            ->setHelp('Enter a descriptive name for this skill category (e.g., "Frontend", "Backend", "DevOps")')
            ->setColumns(6);
    }

    private function createNameWithIconField(): TextField
    {
        return TextField::new('name', 'Category')
            ->setTemplatePath('admin/field/skill_category_name_with_icon.html.twig');
    }

    private function createIconField(): TextField
    {
        return TextField::new('icon', 'Icon Class')
            ->setRequired(false)
            ->setMaxLength(255)
            ->setHelp('Name of Symfony UC icon. Ex : "hugeicons:developer". Leave empty if no icon.')
            ->setColumns(6);
    }

    private function createIconDisplayField(): TextField
    {
        return TextField::new('icon', 'Icon Preview')
            ->setTemplatePath('admin/field/skill_category_icon_preview.html.twig');
    }

    private function createDisplayOrderField(): IntegerField
    {
        return IntegerField::new('displayOrder', 'Display Order')
            ->setRequired(true)
            ->setHelp('Order in which categories appear (lower numbers first, e.g., 1, 2, 3...)')
            ->setColumns(6);
    }

    private function createTechnologiesField(): AssociationField
    {
        return AssociationField::new('technologies', 'Technologies')
            ->setRequired(false)
            ->setCrudController(TechnologyCrudController::class)
            ->setHelp('Technologies associated with this category')
            ->formatValue(function ($value, $entity) {
                if ($entity instanceof SkillCategory) {
                    $count = $entity->getTechnologies()->count();

                    return $count > 0
                        ? sprintf('%d technolog%s', $count, $count > 1 ? 'ies' : 'y')
                        : 'No technologies';
                }

                return $value;
            });
    }

    private function createCreatedAtField(): DateTimeField
    {
        return DateTimeField::new('createdAt', 'Created')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm();
    }

    private function createUpdatedAtField(): DateTimeField
    {
        return DateTimeField::new('updatedAt', 'Last Modified')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm();
    }

    private function createCreatedByField(): TextField
    {
        return TextField::new('createdBy', 'Created By')
            ->hideOnForm();
    }

    private function createUpdatedByField(): TextField
    {
        return TextField::new('updatedBy', 'Updated By')
            ->hideOnForm();
    }

    private function createIsDeletedField(): BooleanField
    {
        return BooleanField::new('deletedAt', 'Is Deleted')
            ->formatValue(function ($value) {
                return null !== $value;
            })
            ->hideOnForm()
            ->hideOnIndex();
    }
}
