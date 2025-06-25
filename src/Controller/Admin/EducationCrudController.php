<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Education;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class EducationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Education::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Education')
            ->setEntityLabelInPlural('Education')
            ->setPageTitle('index', 'Education Management')
            ->setPageTitle('new', 'Add new Education Entry')
            ->setPageTitle('edit', 'Edit Education Entry')
            ->setPageTitle('detail', 'Education Details')
            ->setSearchFields(['degree', 'institution', 'description'])
            ->setDefaultSort(['startDate' => 'DESC'])
            ->setPaginatorPageSize(25)
            ->showEntityActionsInlined()
            ->setAutofocusSearch()
            ->setTimezone('Europe/Paris')
            ->setDateTimeFormat('long', 'none') // For DateField
            ->setHelp(
                'index',
                'Manage your education history. Entries are sorted by most recent start date.'
            );
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setIcon('fa fa-graduation-cap')
                    ->setLabel('Add Education')
                    ->setCssClass('btn btn-success');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn (Action $action) => $action
                ->setIcon('fa fa-edit')
                ->setLabel('Edit'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn (Action $action) => $action
                ->setIcon('fa fa-trash')
                ->setLabel('Delete'))
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, fn (Action $action) => $action
                ->setIcon('fa fa-eye')
                ->setLabel('View'))
            ->update(Crud::PAGE_DETAIL, Action::EDIT, fn (Action $action) => $action
                ->setIcon('fa fa-edit'))
            ->update(Crud::PAGE_DETAIL, Action::DELETE, fn (Action $action) => $action
                ->setIcon('fa fa-trash'))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, fn (Action $action) => $action
                ->setIcon('fa fa-save'))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, fn (Action $action) => $action
                ->setIcon('fa fa-save'));
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('degree', 'Degree'))
            ->add(TextFilter::new('institution', 'Institution'))
            ->add(DateTimeFilter::new('startDate', 'Start Date'))
            ->add(DateTimeFilter::new('endDate', 'End Date'));
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
            $this->createDegreeField(),
            $this->createInstitutionField(),
            $this->createStartDateDisplayField(),
            $this->createEndDateDisplayField(),
        ];
    }

    /**
     * @return FieldInterface[]
     */
    private function getDetailFields(): array
    {
        return [
            $this->createDegreeField(),
            $this->createInstitutionField(),
            $this->createStartDateDisplayField(),
            $this->createEndDateDisplayField(),
            $this->createDescriptionDetailField(),
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
            $this->createDegreeField()->setColumns(6),
            $this->createInstitutionField()->setColumns(6),
            $this->createStartDateFormField()->setColumns(6),
            $this->createEndDateFormField()->setColumns(6),
            $this->createDescriptionFormField()->setColumns(12),
        ];
    }

    private function createDegreeField(): TextField
    {
        return TextField::new('degree', 'Degree');
    }

    private function createInstitutionField(): TextField
    {
        return TextField::new('institution', 'Institution');
    }

    private function createStartDateDisplayField(): TextField
    {
        return TextField::new('startDateDisplay', 'Start Date');
    }

    private function createEndDateDisplayField(): TextField
    {
        return TextField::new('endDateDisplay', 'End Date')
            ->formatValue(function ($value) {
                if ('Present' === $value) {
                    return '<span class="badge bg-success">Present</span>';
                }

                return $value;
            })
            ->renderAsHtml();
    }

    private function createStartDateFormField(): DateField
    {
        return DateField::new('startDate', 'Start Date');
    }

    private function createEndDateFormField(): DateField
    {
        return DateField::new('endDate', 'End Date')
            ->setHelp('Leave empty if ongoing.');
    }

    private function createDescriptionDetailField(): TextareaField
    {
        return TextareaField::new('description')->renderAsHtml();
    }

    private function createDescriptionFormField(): TextEditorField
    {
        return TextEditorField::new('description')
            ->setHelp('Provide details about the course, achievements, etc.');
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
            ->formatValue(fn ($value) => null !== $value)
            ->hideOnForm()
            ->hideOnIndex();
    }
}
