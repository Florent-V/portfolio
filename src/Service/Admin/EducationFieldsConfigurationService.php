<?php

declare(strict_types=1);

namespace App\Service\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EducationFieldsConfigurationService extends AbstractFieldsConfigurationService
{
    /**
     * @return FieldInterface[]
     */
    protected function buildIndexFields(): array
    {
        return [
            $this->createTextField('degree', 'Diplôme'),
            $this->createTextField('institution', 'Établissement'),
            $this->createStartDateDisplayField(),
            $this->createEndDateDisplayField(),
            $this->createIsDeletedField(),
        ];
    }

    /**
     * @return FieldInterface[]
     */
    protected function buildDetailFields(): array
    {
        return [
            $this->createTextField('degree', 'Diplôme'),
            $this->createTextField('institution', 'Établissement'),
            $this->createStartDateDisplayField(),
            $this->createEndDateDisplayField(),
            $this->createTextAreaField('description', 'Description')->renderAsHtml(),
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
            $this->createTextField('degree', 'Diplôme')->setColumns(6),
            $this->createTextField('institution', 'Établissement')->setColumns(6),
            $this->createDateField('startDate', 'Date de début')->setColumns(6),
            $this->createEndDateField()->setColumns(6),
            $this->createTextEditorField('description', 'Description')->setColumns(12),
        ];
    }

    private function createStartDateDisplayField(): TextField
    {
        return $this->createTextField('startDateDisplay', 'Date de début');
    }

    private function createEndDateDisplayField(): TextField
    {
        return $this->createTextField('endDateDisplay', 'Date de fin')
            ->formatValue(static function (mixed $value): string {
                if ('Present' === $value || null === $value) {
                    return '<span class="badge bg-success">En cours</span>';
                }

                return (string) $value;
            })
            ->renderAsHtml();
    }

    private function createEndDateField(): DateField
    {
        return $this->createDateField('endDate', 'Date de fin')->setRequired(false);
    }
}
