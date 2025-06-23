<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Enum\Role;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Role::ADMIN->value)]
class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom'),
            TextareaField::new('description', 'Description')->hideOnIndex(),
            MoneyField::new('price', 'Prix')->setCurrency('EUR'),
            IntegerField::new('stock', 'Stock'),
            ImageField::new('picture', 'Image')
                ->setUploadDir('public/uploads/images/products')
                ->setBasePath('/uploads/images/products')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),
            DateTimeField::new('createdAt', 'Date de création')->hideOnForm(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DELETE) // Empêche la suppression
            ->setPermission(Action::NEW, 'ROLE_SUPER_ADMIN') // Seuls les super admins peuvent créer
            ->setPermission(Action::EDIT, 'ROLE_ADMIN') // Les admins peuvent modifier
//            ->setPermission(Action::DETAIL, 'ROLE_USER'); // Tous les utilisateurs peuvent voir
        ;
    }
}
