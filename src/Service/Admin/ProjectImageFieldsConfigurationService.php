<?php

declare(strict_types=1);

namespace App\Service\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;

class ProjectImageFieldsConfigurationService extends AbstractFieldsConfigurationService
{
    /**
     * @return FieldInterface[]
     */
    protected function buildIndexFields(): array
    {
        return [
            $this->createImageField('imageName', 'Image', '/uploads/images/projects/gallery'),
            $this->createAssociationField('project', 'Projet'),
            $this->createNumberField('displayOrder', 'Ordre'),
            $this->createTextField('altText', 'Texte alternatif'),
            $this->createIsDeletedField(),
            $this->createdAtField(),
        ];
    }

    /**
     * @return FieldInterface[]
     */
    protected function buildDetailFields(): array
    {
        return [
            $this->createImageField('imageName', 'Image', '/uploads/images/projects/gallery'),
            $this->createAssociationField('project', 'Projet'),
            $this->createNumberField('displayOrder', 'Ordre'),
            $this->createTextField('altText', 'Texte alternatif'),
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
            $this->createAssociationField('project', 'Projet'),
            $this->createVichImageUploadField('imageFile', 'Image')
                ->setHelp('Image du projet (JPEG, PNG, WEBP). Max 5MB.'),
            $this->createNumberField('displayOrder', 'Ordre d\'affichage'),
            $this->createTextField('altText', 'Texte alternatif')
                ->setHelp('Description de l\'image pour l\'accessibilité.'),
        ];
    }
}
