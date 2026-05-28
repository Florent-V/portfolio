<?php

declare(strict_types=1);

namespace App\Service\Admin;

use App\Enum\ArticleColumnSpan;
use App\Enum\ArticleContentType;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class ArticleContentFieldsConfigurationService extends AbstractFieldsConfigurationService
{
    /**
     * @return FieldInterface[]
     */
    protected function buildIndexFields(): array
    {
        return [
            $this->createAssociationField('article', 'Article'),
            $this->createNumberField('displayOrder', 'Ordre'),
            $this->createTypeField(),
            Field::new('imageName', 'Aperçu')
                ->setTemplatePath('admin/field/article_content_preview.html.twig')
                ->addCssClass('text-center')
                ->hideOnForm(),
            $this->createIsDeletedField(),
            $this->updatedAtField(),
        ];
    }

    /**
     * @return FieldInterface[]
     */
    protected function buildDetailFields(): array
    {
        return [
            $this->createAssociationField('article', 'Article'),
            $this->createNumberField('displayOrder', 'Ordre'),
            $this->createTypeField(),
            $this->createColumnSpanField(),
            Field::new('imageName', 'Image')
                ->setTemplatePath('admin/field/article_content_preview.html.twig')
                ->addCssClass('text-center')
                ->hideOnForm(),
            $this->createTextField('altText', 'Texte alternatif'),
            $this->createTextEditorField('content', 'Contenu')
                ->setNumOfRows(8),
            $this->createTextField('language', 'Langage'),
            $this->updatedAtField(),
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
            $this->createNumberField('displayOrder', 'Ordre')->setColumns(12),
            $this->createTypeField()->setColumns(12),
            $this->createColumnSpanField()->setColumns(12),
            $this->createTextEditorField('content', 'Contenu')
                ->setNumOfRows(8)
                ->setHelp('Paragraphe : utilisez l\'éditeur. Code : collez votre code source.')
                ->setColumns(12)
                ->hideOnIndex(),
            $this->createTextField('language', 'Langage (ex: php, javascript, bash)')
                ->setColumns(12)
                ->setHelp('Blocs Code uniquement.')
                ->hideOnIndex(),
            $this->createVichImageUploadField('imageFile', 'Image')->setColumns(12)->onlyOnForms(),
            $this->createTextField('altText', 'Texte alternatif')
                ->setColumns(12)
                ->setHelp('Description de l\'image pour l\'accessibilité.')
                ->hideOnIndex(),
        ];
    }

    private function createTypeField(): ChoiceField
    {
        return ChoiceField::new('type', 'Type de bloc')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class'        => ArticleContentType::class,
                'choice_label' => static fn (ArticleContentType $t): string => match ($t) {
                    ArticleContentType::PARAGRAPH => 'Paragraphe',
                    ArticleContentType::IMAGE     => 'Image',
                    ArticleContentType::CODE      => 'Code',
                },
            ]);
    }

    private function createColumnSpanField(): ChoiceField
    {
        return ChoiceField::new('columnSpan', 'Largeur')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class'        => ArticleColumnSpan::class,
                'choice_label' => static fn (ArticleColumnSpan $s): string => match ($s) {
                    ArticleColumnSpan::FULL       => 'Pleine largeur',
                    ArticleColumnSpan::ONE_THIRD  => '1/3',
                    ArticleColumnSpan::TWO_THIRDS => '2/3',
                },
            ]);
    }
}
