<?php

declare(strict_types=1);

namespace App\Service\Admin;

use App\Controller\Admin\TechnologyCrudController;
use App\Form\ProjectImageFormType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

class ProjectFieldsConfigurationService extends AbstractFieldsConfigurationService
{
    /**
     * @return FieldInterface[]
     */
    protected function buildIndexFields(): array
    {
        return [
            $this->createImageField('mainImageName', 'Image', '/uploads/images/projects'),
            $this->createTextField('title', 'Titre'),
            $this->createTechnologiesField(),
            $this->createDateField('startDate', 'Début'),
            $this->createDateField('endDate', 'Fin'),
            $this->createBooleanField('published', 'Publié'),
            $this->createIsDeletedField(),
        ];
    }

    /**
     * @return FieldInterface[]
     */
    protected function buildDetailFields(): array
    {
        return [
            $this->createImageField(
                'mainImageName',
                'Image Principale',
                '/uploads/images/projects'
            ),
            $this->createTextField('title', 'Titre'),
            $this->createTextAreaField('description', 'Description')
                ->renderAsHtml(),
            $this->createTechnologiesField(),
            $this->createUrlField('url', 'URL du projet')
                ->setHelp('URL publique du projet'),
            $this->createUrlField('repositoryUrl', 'URL du dépôt')
                ->setHelp('GitHub, GitLab, etc.'),
            $this->createDateField('startDate', 'Date de début'),
            $this->createDateField('endDate', 'Date de fin'),
            $this->createBooleanField('published', 'Publié'),
            Field::new('projectImages', 'Galerie d\'images')
                ->setTemplatePath('admin/field/project_gallery.html.twig')
                ->onlyOnDetail(),
            $this->createIsDeletedField(),
            $this->createdAtField(),
            $this->updatedAtField(),
            $this->createdByField(),
            $this->updatedByField(),
        ];
    }

    /**
     * @param AdminContext<object>|null $context
     *
     * @return FieldInterface[]
     */
    protected function buildFormFields(?AdminContext $context = null): array
    {
        return [
            $this->createTextField('title', 'Titre'),
            $this->createTextEditorField('description', 'Description')
                ->setNumOfRows(15),
            $this->createTechnologiesField(),
            $this->createUrlField('url', 'URL du projet')
                ->setHelp('URL publique du projet'),
            $this->createUrlField('repositoryUrl', 'URL du dépôt')
                ->setHelp('GitHub, GitLab, etc.'),
            $this->createVichImageUploadField('mainImageFile', 'Image Principale')
                ->setHelp('Image principale du projet (JPEG, PNG, WEBP). Max 5MB.')
                ->setRequired(null !== $context && Crud::PAGE_NEW === $context->getCrud()->getCurrentPage())
                ->setColumns(6),
            $this->createDateField('startDate', 'Date de début')
                ->renderAsNativeWidget(),
            $this->createDateField('endDate', 'Date de fin')
                ->renderAsNativeWidget()
                ->setRequired(false),
            $this->createBooleanField('published', 'Publié'),
            $this->createProjectImagesField(),
        ];
    }

    private function createTechnologiesField(): AssociationField
    {
        return $this->createAssociationField('technologies', 'Technologies')
            ->setCrudController(TechnologyCrudController::class)
            ->setTemplatePath('admin/fields/project_technologies.html.twig')
            ->setFormTypeOptions(['by_reference' => false]);
    }

    private function createProjectImagesField(): CollectionField
    {
        return CollectionField::new('projectImages', 'Galerie d\'images')
            ->setEntryType(ProjectImageFormType::class)
            ->setFormTypeOptions(['by_reference' => false])
            ->setHelp('Images supplémentaires pour la galerie du projet.');
    }
}
