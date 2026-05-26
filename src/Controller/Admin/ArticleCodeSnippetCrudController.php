<?php

namespace App\Controller\Admin;

use App\Entity\ArticleCodeSnippet;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ArticleCodeSnippetCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ArticleCodeSnippet::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('language', 'Langage'),
            CodeEditorField::new('content', 'Code'),
        ];
    }
}
