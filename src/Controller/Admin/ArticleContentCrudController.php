<?php

namespace App\Controller\Admin;

use App\Entity\ArticleContent;
use App\Enum\ArticleContentType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class ArticleContentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ArticleContent::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            NumberField::new('displayOrder', 'Ordre'),
            ChoiceField::new('type', 'Type')
                ->setChoices(ArticleContentType::class)
                ->setTranslatableChoices([
                    ArticleContentType::PARAGRAPH->value => 'Paragraphe',
                    ArticleContentType::IMAGE->value => 'Image',
                    ArticleContentType::CODE->value => 'Code',
                ]),
            TextareaField::new('content', 'Contenu')
                ->hideOnIndex()
                ->setHelp('Pour un paragraphe, entrez le texte ici.')
                ->displayAsHtml(),
            AssociationField::new('image', 'Image')
                ->setCrudController(ArticleImageCrudController::class)
                ->hideOnIndex()
                ->setHelp('Pour une image, sélectionnez-en une ici.'),
            AssociationField::new('codeSnippet', 'Extrait de code')
                ->setCrudController(ArticleCodeSnippetCrudController::class)
                ->hideOnIndex()
                ->setHelp('Pour un extrait de code, sélectionnez-en un ici.'),
        ];
    }
}
