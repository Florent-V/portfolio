<?php

declare(strict_types=1);

namespace App\Service\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SoftSkillFieldsConfigurationService extends AbstractFieldsConfigurationService
{
    /**
     * @return FieldInterface[]
     */
    protected function buildIndexFields(): array
    {
        return [
            $this->createNameWithIconField(),
            $this->createIsDeletedField(),
            $this->createIntegerField('displayOrder', 'Ordre'),
            $this->createdAtField(),
        ];
    }

    /**
     * @return FieldInterface[]
     */
    protected function buildDetailFields(): array
    {
        return [
            $this->createNameWithIconField(),
            $this->createIconPreviewField(),
            $this->createIntegerField('displayOrder', 'Ordre'),
            $this->createTextField('description', 'Description'),
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
            $this->createTextField('name', 'Nom'),
            $this->createTextField('icon', 'Icône')
                ->setHelp('Nom d\'icône Symfony UX (ex: "bi:star")'),
            $this->createDisplayOrderFormField(),
            $this->createTextField('description', 'Description'),
        ];
    }

    private function createNameWithIconField(): TextField
    {
        return $this->createTextField('name', 'Soft Skill')
            ->setTemplatePath('admin/field/generic_name_with_icon.html.twig');
    }

    private function createIconPreviewField(): TextField
    {
        return $this->createTextField('icon', 'Aperçu icône')
            ->setTemplatePath('admin/field/generic_icon_preview.html.twig');
    }

    private function createDisplayOrderFormField(): IntegerField
    {
        return $this->createIntegerField('displayOrder', 'Ordre d\'affichage')
            ->setHelp('Ordre d\'affichage (0 = premier)')
            ->setRequired(false);
    }
}
