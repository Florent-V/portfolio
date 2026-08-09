<?php

declare(strict_types=1);

namespace App\Service\Admin;

use App\Enum\ArticleColumnSpan;
use App\Enum\ArticleContentFormat;
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
            $this->createTextEditorField('content', 'Contenu'),
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
            $this->createTypeField()
                ->setColumns(12)
                ->setFormTypeOption('attr.data-action', 'change->articleContentTypeToggle#update')
                ->setFormTypeOption('attr.data-articleContentTypeToggle-target', 'typeSelect'),
            $this->createColumnSpanField()->setColumns(12),
            $this->createFormatField()
                ->setColumns(12)
                ->setHelp(
                    'HTML : balises sémantiques (h2, p, strong, em, code, ul, ol, li, blockquote). Pas de style, '
                    . 'pas de <div>/<span>. class autorisée uniquement avec ces utilitaires Tailwind/DaisyUI '
                    . '(seuls compilés pour ce contenu) : text-primary/secondary/accent/error/warning/success/info, '
                    . 'font-bold, italic, underline, uppercase, tracking-wide, text-sm/base/lg/xl — toute autre '
                    . 'classe n\'existera pas dans le CSS compilé. '
                    . 'Markdown : ## titres, **gras**, `code`, etc. Uniquement pour les blocs Paragraphe/Résumé.'
                    . ' Note : le Markdown n\'est pas filtré comme le HTML — évitez d\'y coller du HTML brut '
                    . 'non fiable.'
                )
                ->hideOnIndex()
                ->setFormTypeOption('row_attr.data-articleContentTypeToggle-target', 'formatGroup'),
            $this->createTextAreaField('content', 'Contenu')
                ->setNumOfRows(8)
                ->setHelp('Paragraphe : HTML ou Markdown selon le format choisi. Code : collez votre code source.')
                ->setColumns(12)
                ->hideOnIndex()
                ->setFormTypeOption('row_attr.data-articleContentTypeToggle-target', 'contentGroup'),
            $this->createTextField('language', 'Langage (ex: php, javascript, bash)')
                ->setColumns(12)
                ->setHelp('Blocs Code uniquement.')
                ->hideOnIndex()
                ->setFormTypeOption('row_attr.data-articleContentTypeToggle-target', 'languageGroup'),
            $this->createVichImageUploadField('imageFile', 'Image')
                ->setColumns(12)
                ->onlyOnForms()
                ->setFormTypeOption('row_attr.data-articleContentTypeToggle-target', 'imageGroup'),
            $this->createTextField('altText', 'Texte alternatif')
                ->setColumns(12)
                ->setHelp('Description de l\'image pour l\'accessibilité.')
                ->hideOnIndex()
                ->setFormTypeOption('row_attr.data-articleContentTypeToggle-target', 'altTextGroup'),
        ];
    }

    private function createTypeField(): ChoiceField
    {
        return ChoiceField::new('type', 'Type de bloc')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class'        => ArticleContentType::class,
                'choice_label' => static fn (ArticleContentType $t): string => match ($t) {
                    ArticleContentType::SUMMARY   => 'Résumé',
                    ArticleContentType::PARAGRAPH => 'Paragraphe',
                    ArticleContentType::IMAGE     => 'Image',
                    ArticleContentType::CODE      => 'Code',
                },
            ]);
    }

    private function createFormatField(): ChoiceField
    {
        return ChoiceField::new('format', 'Format du contenu')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class'        => ArticleContentFormat::class,
                'choice_label' => static fn (ArticleContentFormat $f): string => match ($f) {
                    ArticleContentFormat::HTML     => 'HTML',
                    ArticleContentFormat::MARKDOWN => 'Markdown',
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
