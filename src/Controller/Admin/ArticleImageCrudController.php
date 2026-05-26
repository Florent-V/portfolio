<?php

namespace App\Controller\Admin;

use App\Entity\ArticleImage;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ArticleImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ArticleImage::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('altText', 'Texte alternatif'),
            TextareaField::new('imageFile', 'Image')
                ->setFormType(VichImageType::class)
                ->setLabel('Image'),
            ImageField::new('imageName', 'Image')
                ->setBasePath('/uploads/images/articles')
                ->onlyOnIndex(),
        ];
    }
}
