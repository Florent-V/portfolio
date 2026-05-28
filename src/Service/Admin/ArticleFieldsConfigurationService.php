<?php

declare(strict_types=1);

namespace App\Service\Admin;

use App\Controller\Admin\ArticleContentCrudController;
use App\Entity\Article;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

class ArticleFieldsConfigurationService extends AbstractFieldsConfigurationService
{
    /**
     * @return FieldInterface[]
     */
    protected function buildIndexFields(): array
    {
        return [
            $this->createImageField('mainImageName', 'Image', '/uploads/images/articles'),
            $this->createTextField('title', 'Titre'),
            $this->createAuthorField(),
            $this->createTagsField(),
            $this->createContentCountField(),
            $this->createIsDeletedField(),
            $this->createBooleanField('isPublished', 'Publié'),
            $this->createDateTimeField('publishedAt', 'Publié le'),
            $this->createdAtField(),
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
                '/uploads/images/articles'
            ),
            $this->createTextField('title', 'Titre'),
            $this->createSlugField('title'),
            $this->createAuthorField(),
            $this->createTagsField(),
            $this->createContentCountField(),
            Field::new('contentElements', 'Images des blocs de contenu')
                ->setTemplatePath('admin/field/article_content_images.html.twig')
                ->hideOnForm()
                ->onlyOnDetail(),
            $this->createIsDeletedField(),
            $this->createBooleanField('isPublished', 'Publié'),
            $this->createDateTimeField('publishedAt', 'Publié le'),
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
            $this->createTextField('title', 'Titre')
                ->setColumns(12),
            $this->createSlugField('title')
                ->setColumns(12),
            $this->createVichImageUploadField('mainImageFile', 'Image principale')
                ->setHelp('Image principale de l\'article (JPEG, PNG, WEBP). Max 5MB.')
                ->setColumns(12),
            $this->createAuthorField()
                ->setColumns(12),
            $this->createTagsField()
                ->setColumns(12),
            $this->createBooleanField('isPublished', 'Publié')
                ->setColumns(12),
            $this->createDateTimeField('publishedAt', 'Date de publication')
                ->setColumns(12),
            CollectionField::new('contentElements', 'Contenu de l\'article')
                ->useEntryCrudForm(ArticleContentCrudController::class)
                ->setFormTypeOptions(['by_reference' => false])
                ->setColumns(12)
                ->onlyOnForms(),
        ];
    }

    private function createContentCountField(): Field
    {
        return Field::new('contentElements', 'Blocs de contenu')
            ->formatValue(static function (mixed $value, object $entity): string {
                if (!$entity instanceof Article) {
                    return '0';
                }
                $count = $entity->getContentElements()->count();

                return sprintf('%d bloc%s', $count, $count > 1 ? 's' : '');
            })
            ->hideOnForm()
            ->onlyOnIndex();
    }

    private function createAuthorField(): AssociationField
    {
        return $this->createAssociationField('author', 'Auteur');
    }

    private function createTagsField(): AssociationField
    {
        return $this->createAssociationField('tags', 'Tags')
            ->setFormTypeOptions([
                'by_reference' => false,
                'attr'         => ['data-ea-autocomplete-allow-item-create' => 'true'],
            ])
            ->setHelp('Tags associés. Tapez pour rechercher ou créer un nouveau tag.');
    }
}
