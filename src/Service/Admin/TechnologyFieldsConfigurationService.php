<?php

declare(strict_types=1);

namespace App\Service\Admin;

use App\Controller\Admin\SkillCategoryCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TechnologyFieldsConfigurationService extends AbstractFieldsConfigurationService
{
    /**
     * @return FieldInterface[]
     */
    protected function buildIndexFields(): array
    {
        return [
            $this->createTechnologyNameWithIconField(),
            $this->createAssociationField('category', 'Catégorie'),
            $this->createIsDeletedField(),
            $this->createIntegerField('level', 'Niveau'),
            $this->createImageField('imageName', 'Image', '/uploads/images/technologies'),
            $this->createdAtField(),
        ];
    }

    /**
     * @return FieldInterface[]
     */
    protected function buildDetailFields(): array
    {
        return [
            $this->createTechnologyNameWithIconField(),
            $this->createAssociationField('category', 'Catégorie'),
            $this->createIntegerField('level', 'Niveau'),
            $this->createTextField('icon', 'Icône'),
            $this->createImageField('imageName', 'Image', '/uploads/images/technologies'),
            $this->createdAtField(),
            $this->updatedAtField(),
            $this->createdByField(),
            $this->updatedByField(),
            $this->createIsDeletedField(),
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
            $this->createTextField('name', 'Nom de la technologie')
                ->setRequired(true)
                ->setMaxLength(255)
                ->setHelp('Ex: "React", "Laravel", "Docker"')
                ->setColumns(6),
            $this->createCategoryFormField(),
            $this->createIntegerField('level', 'Niveau')
                ->setHelp('Niveau de maîtrise (0-100)')
                ->setColumns(6),
            $this->createTextField('icon', 'Icône')
                ->setRequired(false)
                ->setMaxLength(255)
                ->setHelp('Icône Symfony UX (ex: "logos:react"). Laissez vide pour défaut.')
                ->setColumns(6),
            $this->createVichImageUploadField('imageFile', 'Image de la technologie')
                ->setHelp('Upload (JPEG, PNG, WEBP, SVG). Max 1MB. Prioritaire sur l\'icône.')
                ->setColumns(12),
        ];
    }

    private function createTechnologyNameWithIconField(): TextField
    {
        return $this->createTextField('name', 'Technology')
            ->setTemplatePath('admin/field/generic_name_with_icon.html.twig');
    }

    private function createCategoryFormField(): AssociationField
    {
        return $this->createAssociationField('category', 'Catégorie')
            ->setCrudController(SkillCategoryCrudController::class)
            ->setRequired(true)
            ->setColumns(6);
    }
}
