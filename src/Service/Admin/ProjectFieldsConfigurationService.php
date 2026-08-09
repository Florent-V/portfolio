<?php

declare(strict_types=1);

namespace App\Service\Admin;

use App\Controller\Admin\TechnologyCrudController;
use App\Entity\Project;
use App\Enum\ArticleContentFormat;
use App\Form\ProjectImageFormType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class ProjectFieldsConfigurationService extends AbstractFieldsConfigurationService
{
    /**
     * @return FieldInterface[]
     */
    protected function buildIndexFields(): array
    {
        return [
            $this->createImageField('mainImageName', 'Image', '/uploads/images/projects'),
            $this->createTextField('title', 'Titre'),
            $this->createTechnologiesField(),
            $this->createTagsCountField(),
            $this->createDateField('startDate', 'Début'),
            $this->createDateField('endDate', 'Fin'),
            $this->createBooleanField('published', 'Publié'),
            $this->createIsDeletedField(),
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
                '/uploads/images/projects'
            ),
            $this->createTextField('title', 'Titre'),
            $this->createFormatField()
                ->onlyOnDetail(),
            Field::new('description', 'Description')
                ->setTemplatePath('admin/field/project_description_preview.html.twig')
                ->onlyOnDetail(),
            $this->createTechnologiesField(),
            $this->createTagsDetailField(),
            $this->createUrlField('url', 'URL du projet')
                ->setHelp('URL publique du projet'),
            $this->createUrlField('repositoryUrl', 'URL du dépôt')
                ->setHelp('GitHub, GitLab, etc.'),
            $this->createDateField('startDate', 'Date de début'),
            $this->createDateField('endDate', 'Date de fin'),
            $this->createBooleanField('published', 'Publié'),
            $this->createTextField('slug', 'Slug')->hideOnForm(),
            Field::new('projectImages', 'Galerie d\'images')
                ->setTemplatePath('admin/field/project_gallery.html.twig')
                ->onlyOnDetail(),
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
        $fields = [];

        if (null !== $context && Crud::PAGE_EDIT === $context->getCrud()->getCurrentPage()) {
            $fields[] = $this->createTextField('slug', 'Slug')
                ->setFormTypeOption('disabled', true)
                ->setColumns(12)
                ->setHelp('Généré automatiquement — non modifiable.');
        }

        return array_merge($fields, [
            $this->createTextField('title', 'Titre')
                ->setColumns(12),
            $this->createFormatField()
                ->setColumns(12)
                ->setHelp(
                    'HTML : balises sémantiques (h2, p, strong, em, code, ul, ol, li, blockquote). Pas de style, '
                    . 'pas de <div>/<span>. class autorisée uniquement avec ces utilitaires Tailwind/DaisyUI '
                    . '(seuls compilés pour ce contenu) : text-primary/secondary/accent/error/warning/success/info, '
                    . 'font-bold, italic, underline, uppercase, tracking-wide, text-sm/base/lg/xl — toute autre '
                    . 'classe n\'existera pas dans le CSS compilé. '
                    . 'Markdown : ## titres, **gras**, `code`, etc.'
                    . ' Note : le Markdown n\'est pas filtré comme le HTML — évitez d\'y coller du HTML brut '
                    . 'non fiable.'
                ),
            $this->createTextAreaField('description', 'Description')
                ->setColumns(12)
                ->setNumOfRows(20)
                ->setHelp('Contenu en HTML ou Markdown selon le format choisi ci-dessus.'),
            $this->createTechnologiesField()
                ->setColumns(12),
            $this->createTagsField()
                ->setColumns(12),
            $this->createUrlField('url', 'URL du projet')
                ->setColumns(12)
                ->setHelp('URL publique du projet'),
            $this->createUrlField('repositoryUrl', 'URL du dépôt')
                ->setColumns(12)
                ->setHelp('GitHub, GitLab, etc.'),
            $this->createVichImageUploadField('mainImageFile', 'Image Principale')
                ->setHelp('Image principale du projet (JPEG, PNG, WEBP). Max 5MB.')
                ->setRequired(null !== $context && Crud::PAGE_NEW === $context->getCrud()->getCurrentPage())
                ->setColumns(12),
            $this->createDateField('startDate', 'Date de début')
                ->renderAsNativeWidget(),
            $this->createDateField('endDate', 'Date de fin')
                ->renderAsNativeWidget()
                ->setRequired(false),
            $this->createBooleanField('published', 'Publié'),
            $this->createProjectImagesField(),
        ]);
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

    private function createTagsDetailField(): AssociationField
    {
        return $this->createAssociationField('tags', 'Tags')
            ->setTemplatePath('admin/fields/tags.html.twig')
            ->onlyOnDetail();
    }

    private function createTagsCountField(): Field
    {
        return Field::new('tags', 'Tags')
            ->formatValue(static function (mixed $value, object $entity): string {
                if (!$entity instanceof Project) {
                    return '0';
                }
                $count = $entity->getTags()->count();

                return sprintf('%d tag%s', $count, $count > 1 ? 's' : '');
            })
            ->hideOnForm()
            ->onlyOnIndex();
    }

    private function createTechnologiesField(): AssociationField
    {
        return $this->createAssociationField('technologies', 'Technologies')
            ->setCrudController(TechnologyCrudController::class)
            ->setTemplatePath('admin/fields/project_technologies.html.twig')
            ->setFormTypeOptions(['by_reference' => false])
            ->setColumns(12);
    }

    private function createProjectImagesField(): CollectionField
    {
        return CollectionField::new('projectImages', 'Galerie d\'images')
            ->setEntryType(ProjectImageFormType::class)
            ->setColumns(12)
            ->setFormTypeOptions(['by_reference' => false])
            ->setHelp('Images supplémentaires pour la galerie du projet.');
    }

    private function createFormatField(): ChoiceField
    {
        return ChoiceField::new('format', 'Format de la description')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class'        => ArticleContentFormat::class,
                'choice_label' => static fn (ArticleContentFormat $f): string => match ($f) {
                    ArticleContentFormat::HTML     => 'HTML',
                    ArticleContentFormat::MARKDOWN => 'Markdown',
                },
            ]);
    }
}
