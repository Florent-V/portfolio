<?php

declare(strict_types=1);

namespace App\Service\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class HobbyFieldsConfigurationService extends AbstractFieldsConfigurationService
{
    /**
     * @return FieldInterface[]
     */
    protected function buildIndexFields(): array
    {
        return [
            $this->createNameWithIconField(),
            $this->createIconPreviewField(),
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
            $this->createNameWithIconField()
                ->setColumns(6),
            $this->createIconPreviewField()
                ->setColumns(6),
            $this->createdAtField(),
            $this->updatedAtField(),
            $this->createdByField(),
            $this->updatedByField(),
            $this->deletedAtField(),
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
            $this->createTextField('icon', 'Icône'),
        ];
    }

    private function createNameWithIconField(): TextareaField
    {
        return $this->createTextAreaField('name', 'Hobby')
            ->setTemplatePath('admin/field/generic_name_with_icon.html.twig');
    }
}
