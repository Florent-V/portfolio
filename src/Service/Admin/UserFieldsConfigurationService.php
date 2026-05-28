<?php

declare(strict_types=1);

namespace App\Service\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserFieldsConfigurationService extends AbstractFieldsConfigurationService
{
    /**
     * @return FieldInterface[]
     */
    protected function buildIndexFields(): array
    {
        return [
            EmailField::new('email'),
            TextField::new('userName'),
            TextField::new('firstName'),
            TextField::new('lastName'),
        ];
    }

    /**
     * @return FieldInterface[]
     */
    protected function buildDetailFields(): array
    {
        return [
            EmailField::new('email'),
            TextField::new('userName'),
            TextField::new('firstName'),
            TextField::new('lastName'),
            $this->createdAtField(),
            $this->updatedAtField(),
        ];
    }

    /**
     * Roles field with context-based logic must be handled in the controller.
     * This returns base fields only; call getFieldsForPage() from the controller
     * and append the roles field there.
     *
     * @return FieldInterface[]
     */
    /**
     * @param AdminContext<object>|null $context
     */
    protected function buildFormFields(?AdminContext $context = null): array
    {
        return [
            EmailField::new('email'),
            TextField::new('userName'),
            TextField::new('firstName'),
            TextField::new('lastName'),
            TextField::new('password')->hideOnIndex(),
        ];
    }
}
