<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SoftSkill;
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

class SoftSkillCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SoftSkill::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Soft Skill')
            ->setEntityLabelInPlural('Soft Skills')
            ->setPageTitle('index', 'Soft Skills Management')
            ->setPageTitle('new', 'Add new Soft Skill')
            ->setPageTitle('edit', 'Edit Soft Skill')
            ->setPageTitle('detail', 'Soft Skill Details')
            ->setSearchFields(['name', 'icon'])
            ->setDefaultSort(['displayOrder' => 'ASC', 'name' => 'ASC'])
            ->setPaginatorPageSize(25)
            ->showEntityActionsInlined()
            ->setAutofocusSearch()
            ->setTimezone('Europe/Paris')
            ->setDateTimeFormat('short', 'short')
            ->setHelp(
                'index',
                'Manage the soft skills to be displayed on your portfolio.'
            );
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setIcon('fa fa-plus')
                    ->setLabel('Add Soft Skill')
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
            ->add(TextFilter::new('name', 'Soft Skill Name'))
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
            $this->createIconPreviewField(),
            $this->createDisplayOrderField(),
            $this->createDescriptionField(),
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
            $this->createDisplayOrderFormField(),
            $this->createDescriptionField(),
        ];
    }

    private function createNameField(): TextField
    {
        return TextField::new('name', 'Soft Skill Name')
            ->setRequired(true)
            ->setMaxLength(255)
            ->setHelp('Enter the name of the soft skill (e.g., "Teamwork", "Problem Solving")')
            ->setColumns(6);
    }

    private function createNameWithIconField(): TextField
    {
        return TextField::new('name', 'Soft Skill')
            ->setTemplatePath('admin/field/generic_name_with_icon.html.twig');
    }

    private function createDescriptionField(): TextField
    {
        return TextField::new('description', 'Description')
            ->setRequired(false)
            ->setHelp('Enter a short description of the soft skill ' .
                '(e.g., "Ability to work in a team", "Ability to solve problems")');
    }

    private function createIconField(): TextField
    {
        return TextField::new('icon', 'Icon')
            ->setRequired(false)
            ->setMaxLength(255)
            ->setHelp('Symfony UX icon name (e.g., "fa:users", "fa:lightbulb").')
            ->setColumns(6);
    }

    private function createIconPreviewField(): TextField
    {
        return TextField::new('icon', 'Icon Preview')
            ->setTemplatePath('admin/field/generic_icon_preview.html.twig');
    }

    private function createDisplayOrderField(): IntegerField
    {
        return IntegerField::new('displayOrder', 'Order');
    }

    private function createDisplayOrderFormField(): IntegerField
    {
        return IntegerField::new('displayOrder', 'Display Order')
            ->setHelp('Order of appearance (lower numbers first). Defaults to 0.')
            ->setRequired(true)
            ->setColumns(6);
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
