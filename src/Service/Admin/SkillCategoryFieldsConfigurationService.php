<?php

declare(strict_types=1);

namespace App\Service\Admin;

use App\Controller\Admin\TechnologyCrudController;
use App\Entity\SkillCategory;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SkillCategoryFieldsConfigurationService extends AbstractFieldsConfigurationService
{
    /**
     * @return FieldInterface[]
     */
    protected function buildIndexFields(): array
    {
        return [
            $this->createNameWithIconField(),
            $this->createIconPreviewField(),
            $this->createTechnologiesField(),
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
            $this->createTechnologiesField(),
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
            $this->createTextField('icon', 'Icône')->setHelp('Nom d\'icône Symfony UX (ex: "logos:php")'),
            $this->createIntegerField('displayOrder', 'Ordre d\'affichage'),
        ];
    }

    private function createNameWithIconField(): TextField
    {
        return $this->createTextField('name', 'Category')
            ->setTemplatePath('admin/field/generic_name_with_icon.html.twig');
    }

    private function createTechnologiesField(): AssociationField
    {
        return $this->createAssociationField('technologies', 'Technologies')
            ->setRequired(false)
            ->setCrudController(TechnologyCrudController::class)
            ->setHelp('Technologies associées à cette catégorie')
            ->formatValue(static function (mixed $value, object $entity): string {
                if ($entity instanceof SkillCategory) {
                    $count = $entity->getTechnologies()->count();

                    return $count > 0
                        ? sprintf('%d technolog%s', $count, $count > 1 ? 'ies' : 'y')
                        : 'No technologies';
                }

                return (string) $value;
            });
    }
}
