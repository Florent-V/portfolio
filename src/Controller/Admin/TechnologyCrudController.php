<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Technology;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Vich\UploaderBundle\Form\Type\VichImageType;

class TechnologyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Technology::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // Labels et titre
            ->setEntityLabelInSingular('Technology')
            ->setEntityLabelInPlural('Technologies')
            ->setPageTitle('index', '%entity_label_plural% Management')
            ->setPageTitle('new', 'Add new %entity_label_singular%')
            ->setPageTitle('edit', 'Edit %entity_label_singular%')
            ->setPageTitle('detail', '%entity_label_singular% Details')

            // Recherche
            ->setSearchFields(['name', 'category.name', 'icon'])

            // Tri par défaut
            ->setDefaultSort(['category.name' => 'ASC', 'name' => 'ASC'])

            // Pagination
            ->setPaginatorPageSize(25)
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
                'Manage your technology stack.' .
                ' Each technology belongs to a skill category and can have a proficiency level.'
            )
            ->setHelp(
                'new',
                'Add a new technology to your portfolio. Don\'t forget to set the category and level.'
            )
            ->setHelp(
                'edit',
                'Modify technology information. You can update the image, level, or category as needed.'
            );
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // Personnaliser les actions sur la page index
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setIcon('fa fa-plus')
                    ->setLabel('Add Technology')
                    ->setCssClass('btn btn-success');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit')->setLabel('Edit');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa fa-trash')->setLabel('Delete');
            })

            // Ajouter l'action DETAIL sur la page INDEX
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setIcon('fa fa-eye')
                    ->setLabel('View Details');
            })

            // Actions sur les autres pages
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit');
            })
            ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa fa-trash');
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
            ->add(TextFilter::new('name', 'Technology Name'))
            ->add(EntityFilter::new('category', 'Category'))
            ->add(ChoiceFilter::new('level', 'Proficiency Level')->setChoices([
                'Beginner (1)'     => 1,
                'Novice (2)'       => 2,
                'Intermediate (3)' => 3,
                'Advanced (4)'     => 4,
                'Expert (5)'       => 5,
            ]))
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
            $this->createTechnologyNameWithIconField(),
            $this->createCategoryField(),
            $this->createLevelDisplayField(),
            $this->createImageDisplayField(),
            $this->createCreatedAtField(),
        ];
    }

    /**
     * @return FieldInterface[]
     */
    private function getDetailFields(): array
    {
        return [
            $this->createTechnologyNameWithIconField(),
            $this->createCategoryField(),
            $this->createLevelDisplayField(),
            $this->createIconDisplayField(),
            $this->createImageDisplayField(),
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
            $this->createCategoryFormField(),
            $this->createLevelFormField(),
            $this->createIconField(),
            $this->createImageUploadField(),
        ];
    }

    private function createNameField(): TextField
    {
        return TextField::new('name', 'Technology Name')
            ->setRequired(true)
            ->setMaxLength(255)
            ->setHelp('Enter the technology name (e.g., "React", "Laravel", "Docker")')
            ->setColumns(6);
    }

    private function createTechnologyNameWithIconField(): TextField
    {
        return TextField::new('name', 'Technology')
            ->setTemplatePath('admin/field/generic_name_with_icon.html.twig');
    }

    private function createCategoryField(): AssociationField
    {
        return AssociationField::new('category', 'Category')
            ->setRequired(false);
    }

    private function createCategoryFormField(): AssociationField
    {
        return AssociationField::new('category', 'Skill Category')
            ->setRequired(true)
            ->setCrudController(SkillCategoryCrudController::class)
            ->setHelp('Select the skill category this technology belongs to')
            ->setColumns(6);
    }

    private function createLevelFormField(): IntegerField
    {
        return IntegerField::new('level', 'Proficiency Level')
            ->setRequired(false)
            ->setHelp('Your skill level from 1 (Beginner) to 5 (Expert). Leave empty if not applicable.')
            ->setColumns(6);
    }

    private function createLevelDisplayField(): IntegerField
    {
        return IntegerField::new('level', 'Level')
            ->setTemplatePath('admin/field/technology_level_display.html.twig');
    }

    private function createIconField(): TextField
    {
        return TextField::new('icon', 'Icon')
            ->setRequired(false)
            ->setMaxLength(255)
            ->setHelp('Symfony UX icon name (e.g., "logos:react", "logos:laravel"). Leave empty for default.')
            ->setColumns(6);
    }

    private function createIconDisplayField(): TextField
    {
        return TextField::new('icon', 'Icon Preview')
            ->setTemplatePath('admin/field/generic_icon_preview.html.twig');
    }

    private function createImageUploadField(): TextField
    {
        return TextField::new('imageFile', 'Technology Image')
            ->setFormType(VichImageType::class)
            ->setHelp('Upload an image (JPEG, PNG, WEBP, SVG). Max 1MB. This will override the icon if both are set.')
            ->setRequired(false)
            ->setColumns(12);
    }

    private function createImageDisplayField(): ImageField
    {
        return ImageField::new('imageName', 'Image')
            ->setBasePath('/uploads/images/technologies')
            ->setUploadDir('public/uploads/images/technologies')
            ->hideOnForm();
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

    private function createCreatedByField(): AssociationField
    {
        return AssociationField::new('createdBy', 'Created By')
            ->hideOnForm()
            ->setRequired(false);
    }

    private function createUpdatedByField(): AssociationField
    {
        return AssociationField::new('updatedBy', 'Updated By')
            ->hideOnForm()
            ->setRequired(false);
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
