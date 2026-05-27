<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ArticleContent;
use App\Enum\ArticleColumnSpan;
use App\Enum\ArticleContentType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ArticleContentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ArticleContent::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            NumberField::new('displayOrder', 'Ordre')
                ->setColumns(12),
            ChoiceField::new('type', 'Type de bloc')
                ->setFormType(EnumType::class)
                ->setFormTypeOptions([
                    'class'        => ArticleContentType::class,
                    'choice_label' => fn (ArticleContentType $t) => match ($t) {
                        ArticleContentType::PARAGRAPH => 'Paragraphe',
                        ArticleContentType::IMAGE     => 'Image',
                        ArticleContentType::CODE      => 'Code',
                    },
                ])
                ->setColumns(12),
            ChoiceField::new('columnSpan', 'Largeur')
                ->setFormType(EnumType::class)
                ->setFormTypeOptions([
                    'class'        => ArticleColumnSpan::class,
                    'choice_label' => fn (ArticleColumnSpan $s) => match ($s) {
                        ArticleColumnSpan::FULL       => 'Pleine largeur',
                        ArticleColumnSpan::ONE_THIRD  => '1/3',
                        ArticleColumnSpan::TWO_THIRDS => '2/3',
                    },
                ])
                ->setColumns(12),
            TextEditorField::new('content', 'Contenu')
                ->setNumOfRows(8)
                ->setHelp('Paragraphe : utilisez l\'éditeur. Code : collez votre code source.')
                ->setColumns(12)
                ->hideOnIndex(),
            TextField::new('language', 'Langage (ex: php, javascript, bash)')
                ->setColumns(12)
                ->setHelp('Blocs Code uniquement.')
                ->hideOnIndex(),
            TextareaField::new('imageFile', 'Image')
                ->setFormType(VichImageType::class)
                ->setColumns(12)
                ->onlyOnForms(),
            ImageField::new('imageName', 'Image')
                ->setBasePath('/uploads/images/articles')
                ->onlyOnIndex(),
            TextField::new('altText', 'Texte alternatif')
                ->setColumns(12)
                ->setHelp('Description de l\'image pour l\'accessibilité.')
                ->hideOnIndex(),
        ];
    }
}
