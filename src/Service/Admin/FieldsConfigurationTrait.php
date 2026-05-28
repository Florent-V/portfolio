<?php

declare(strict_types=1);

namespace App\Service\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Vich\UploaderBundle\Form\Type\VichImageType;

trait FieldsConfigurationTrait
{
    protected function createIdField(): IdField
    {
        return IdField::new('id')
            ->onlyOnIndex()
            ->hideOnForm();
    }

    protected function createTextField(string $property, string $label): TextField
    {
        return TextField::new($property, $label);
    }

    protected function createTextAreaField(string $property, string $label): TextareaField
    {
        return TextareaField::new($property, $label);
    }

    protected function createTextEditorField(string $property, string $label): TextEditorField
    {
        return TextEditorField::new($property, $label);
    }

    protected function createAssociationField(string $property, string $label): AssociationField
    {
        return AssociationField::new($property, $label);
    }

    protected function createBooleanField(string $property, string $label): BooleanField
    {
        return BooleanField::new($property, $label);
    }

    protected function createIntegerField(string $property, string $label): IntegerField
    {
        return IntegerField::new($property, $label);
    }

    protected function createNumberField(string $property, string $label): NumberField
    {
        return NumberField::new($property, $label);
    }

    protected function createDateField(string $property, string $label): DateField
    {
        return DateField::new($property, $label);
    }

    protected function createDateTimeField(string $property, string $label): DateTimeField
    {
        return DateTimeField::new($property, $label)
            ->setFormat('dd/MM/yyyy HH:mm');
    }

    protected function createUrlField(string $property, string $label): UrlField
    {
        return UrlField::new($property, $label);
    }

    protected function createSlugField(string $targetField = 'name'): SlugField
    {
        return SlugField::new('slug')->setTargetFieldName($targetField);
    }

    protected function createImageField(string $property, string $label, string $basePath): ImageField
    {
        return ImageField::new($property, $label)
            ->setBasePath($basePath)
            ->setTemplatePath('admin/field/generic_image.html.twig');
    }

    protected function createVichImageUploadField(string $property, string $label): TextField
    {
        return TextField::new($property, $label)
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions([
                'allow_delete'       => false,
                'delete_label'       => 'Supprimer',
                'download_label'     => 'Télécharger',
                'download_uri'       => false,
                'image_uri'          => true,
                'translation_domain' => 'VichUploaderBundle',
            ])
            ->setRequired(false);
    }

    protected function createIsDeletedField(): BooleanField
    {
        return BooleanField::new('isDeleted', 'Statut')
            ->setTemplatePath('admin/fields/is_deleted.html.twig')
            ->hideOnForm();
    }

    protected function createdAtField(): DateTimeField
    {
        return DateTimeField::new('createdAt', 'Créé le')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm();
    }

    protected function updatedAtField(): DateTimeField
    {
        return DateTimeField::new('updatedAt', 'Mis à jour le')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm();
    }

    protected function deletedAtField(): DateTimeField
    {
        return DateTimeField::new('deletedAt', 'Supprimé le')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->setCssClass('text-danger')
            ->hideOnForm();
    }

    protected function createdByField(): AssociationField
    {
        return AssociationField::new('createdBy', 'Créé par')
            ->hideOnForm()
            ->setRequired(false);
    }

    protected function updatedByField(): AssociationField
    {
        return AssociationField::new('updatedBy', 'Mis à jour par')
            ->hideOnForm()
            ->setRequired(false);
    }
}
