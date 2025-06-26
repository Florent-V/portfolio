<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Experience;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class ExperienceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Experience::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Experience')
            ->setEntityLabelInPlural('Experiences')
            ->setPageTitle(Crud::PAGE_INDEX, 'All Experiences')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Experience')
            ->setPageTitle(Crud::PAGE_NEW, 'Add Experience')
            ->setPageTitle(Crud::PAGE_DETAIL, 'View Experience')
            ->setSearchFields(['jobTitle', 'company', 'description'])
            ->setDefaultSort(['startDate' => 'DESC'])
            ->setPaginatorPageSize(25)
            ->showEntityActionsInlined()
            ->setAutofocusSearch()
            ->setTimezone('Europe/Paris')
            ->setHelp(Crud::PAGE_INDEX, 'List of all professional experiences.')
            ->setHelp(Crud::PAGE_EDIT, 'Edit the details of the experience.')
            ->setHelp(Crud::PAGE_NEW, 'Create a new experience record.');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, fn (Action $action) => $action
                ->setIcon('fa fa-briefcase')
                ->setLabel('Add Experience')
                ->setCssClass('btn btn-success'))
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn (Action $action) => $action
                ->setIcon('fa fa-edit'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn (Action $action) => $action
                ->setIcon('fa fa-trash'))
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, fn (Action $action) => $action
                ->setIcon('fa fa-eye'));
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('jobTitle', 'Job Title'))
            ->add(TextFilter::new('company', 'Company'))
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
            $this->createJobTitleField(),
            $this->createCompanyField(),
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
            $this->createJobTitleField(),
            $this->createCompanyField(),
            $this->createStartDateDisplayField(),
            $this->createEndDateDisplayField(),
            $this->createDescriptionDetailField(),
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
            $this->createJobTitleField(),
            $this->createCompanyField(),
            $this->createStartDateField(),
            $this->createEndDateField(),
            $this->createIsCurrentField(),
            $this->createDescriptionField(),
        ];
    }

    private function createJobTitleField(): TextField
    {
        return TextField::new('jobTitle', 'Job Title');
    }

    private function createCompanyField(): TextField
    {
        return TextField::new('company', 'Company');
    }

    private function createStartDateField(): DateField
    {
        return DateField::new('startDate', 'Start Date');
    }

    private function createEndDateField(): DateField
    {
        return DateField::new('endDate', 'End Date')
            ->setHelp('Leave empty for current position.');
    }

    private function createIsCurrentField(): BooleanField
    {
        return BooleanField::new('isCurrent', 'Current Position')
            ->setHelp('Check if this is your current ongoing position.');
    }

    private function createDescriptionField(): TextEditorField
    {
        return TextEditorField::new('description', 'Description');
    }

    private function createDescriptionDetailField(): TextareaField
    {
        return TextareaField::new('description', 'Description')->renderAsHtml();
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
