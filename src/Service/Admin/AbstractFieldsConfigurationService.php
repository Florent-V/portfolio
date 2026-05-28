<?php

declare(strict_types=1);

namespace App\Service\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;

abstract class AbstractFieldsConfigurationService
{
    use FieldsConfigurationTrait;

    /**
     * Returns configured fields for a given EasyAdmin page.
     *
     * @return FieldInterface[]
     */
    public function getFieldsForPage(string $pageName, ?AdminContext $context = null): iterable
    {
        return match ($pageName) {
            Crud::PAGE_INDEX                => $this->buildIndexFields(),
            Crud::PAGE_DETAIL               => $this->buildDetailFields(),
            Crud::PAGE_NEW, Crud::PAGE_EDIT => $this->buildFormFields($context),
            default                         => [],
        };
    }

    /**
     * @return FieldInterface[]
     */
    abstract protected function buildIndexFields(): array;

    /**
     * @return FieldInterface[]
     */
    abstract protected function buildDetailFields(): array;

    /**
     * @param AdminContext<object>|null $context
     *
     * @return FieldInterface[]
     */
    abstract protected function buildFormFields(?AdminContext $context = null): array;
}
