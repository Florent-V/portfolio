<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Hobby;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class HobbyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Hobby::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Hobby')
            ->setEntityLabelInPlural('Hobbies')
            ->setPageTitle(Crud::PAGE_INDEX, 'All Hobbies')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Hobby')
            ->setPageTitle(Crud::PAGE_NEW, 'Add Hobby')
            ->setPageTitle(Crud::PAGE_DETAIL, 'View Hobby')
            ->setSearchFields(['name', 'icon'])
            ->setDefaultSort(['name' => 'ASC'])
            ->setPaginatorPageSize(25)
            ->showEntityActionsInlined()
            ->setAutofocusSearch()
            ->setTimezone('Europe/Paris')
            ->setHelp(Crud::PAGE_INDEX, 'List of all hobbies.')
            ->setHelp(Crud::PAGE_EDIT, 'Edit the details of the hobby.')
            ->setHelp(Crud::PAGE_NEW, 'Create a new hobby.');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, fn (Action $action) => $action
                ->setIcon('fa fa-puzzle-piece')
                ->setLabel('Add Hobby')
                ->setCssClass('btn btn-success'))
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn (Action $action) => $action
                ->setIcon('fa fa-edit')
                ->setLabel('Edit'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn (Action $action) => $action
                ->setIcon('fa fa-trash')
                ->setLabel('Delete'))
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, fn (Action $action) => $action
                ->setIcon('fa fa-eye')
                ->setLabel('View'));
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', 'Name'))
            ->add(TextFilter::new('icon', 'Icon'))
            ->add(DateTimeFilter::new('createdAt', 'Created At'))
            ->add(DateTimeFilter::new('updatedAt', 'Updated At'));
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
            $this->createCreatedAtField(),
        ];
    }

    /**
     * @return FieldInterface[]
     */
    private function getDetailFields(): array
    {
        return [
            $this->createNameWithIconField()->setColumns(6),
            $this->createIconPreviewField()->setColumns(6),
            $this->createCreatedAtField(),
            $this->createUpdatedAtField(),
            $this->createCreatedByField(),
            $this->createUpdatedByField(),
            $this->createDeletedAtField(),
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
        ];
    }

    private function createNameField(): TextField
    {
        return TextField::new('name', 'Name')
            ->setHelp('The name of the hobby.');
    }

    private function createIconField(): TextField
    {
        return TextField::new('icon', 'Icon')
            ->setHelp('Enter a Symfony UX icon name (e.g., "fa:code", "mdi:database").');
    }

    private function createNameWithIconField(): TextareaField
    {
        return TextareaField::new('name', 'Name')
            ->setTemplatePath('admin/field/generic_name_with_icon.html.twig');
    }

    private function createIconPreviewField(): Field
    {
        return Field::new('icon', 'Icon Preview')
            ->setTemplatePath('admin/field/generic_icon_preview.html.twig');
    }

    private function createCreatedAtField(): DateTimeField
    {
        return DateTimeField::new('createdAt', 'Created')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm();
    }

    private function createUpdatedAtField(): DateTimeField
    {
        return DateTimeField::new('updatedAt', 'Updated')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm();
    }

    private function createCreatedByField(): TextField
    {
        return TextField::new('createdBy', 'Created By')->hideOnForm();
    }

    private function createUpdatedByField(): TextField
    {
        return TextField::new('updatedBy', 'Updated By')->hideOnForm();
    }

    private function createDeletedAtField(): DateTimeField
    {
        return DateTimeField::new('deletedAt', 'Deleted')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm();
    }
}
