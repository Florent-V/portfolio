<?php

declare(strict_types=1);

namespace App\Service\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ExperienceFieldsConfigurationService extends AbstractFieldsConfigurationService
{
    /**
     * @return FieldInterface[]
     */
    protected function buildIndexFields(): array
    {
        return [
            $this->createTextField('jobTitle', 'Job Title'),
            $this->createTextField('company', 'Company'),
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
            $this->createTextField('jobTitle', 'Job Title'),
            $this->createTextField('company', 'Company'),
            $this->createStartDateDisplayField(),
            $this->createEndDateDisplayField(),
            $this->createTextAreaField('description', 'Description')->renderAsHtml(),
            $this->createIsDeletedField(),
            $this->createdAtField(),
            $this->updatedAtField(),
            $this->createdByField(),
            $this->updatedByField(),
            $this->deletedAtField(),
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
            $this->createTextField('jobTitle', 'Job Title'),
            $this->createTextField('company', 'Company'),
            $this->createDateField('startDate', 'Start Date'),
            $this->createEndDateField(),
            $this->createBooleanField('isCurrent', 'Current Position'),
            $this->createTextEditorField('description', 'Description'),
        ];
    }

    private function createStartDateDisplayField(): TextField
    {
        return $this->createTextField('startDateDisplay', 'Start Date');
    }

    private function createEndDateDisplayField(): TextField
    {
        return $this->createTextField('endDateDisplay', 'End Date')
            ->formatValue(static function (mixed $value): string {
                if ('Present' === $value) {
                    return '<span class="badge bg-success">Present</span>';
                }

                return (string) $value;
            })
            ->renderAsHtml();
    }

    private function createEndDateField(): DateField
    {
        return $this->createDateField('endDate', 'End Date')->setRequired(false);
    }
}
