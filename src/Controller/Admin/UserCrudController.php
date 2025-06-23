<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\Role;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Role::ADMIN->value)]
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        /** @var User $user */
        $user = $this->getUser();

        $rolesField = ChoiceField::new('roles')
            ->setChoices([
                'User'        => 'ROLE_USER',
                'Admin'       => 'ROLE_ADMIN',
                'Super Admin' => 'ROLE_SUPER_ADMIN',
            ])
            ->allowMultipleChoices()
//            ->renderExpanded()
        ;


        // Vérifier que l'on est bien en mode édition
        if (Crud::PAGE_EDIT === $pageName) {
            $entityInstance = $this->getContext()->getEntity()->getInstance();

            if ($entityInstance && $user->getId() === $entityInstance->getId()) {
                $rolesField->setFormTypeOption('disabled', true);
            }
        }


        return [
            EmailField::new('email'),
            TextField::new('userName'),
            TextField::new('firstName'),
            TextField::new('lastName'),
            TextField::new('password')->hideOnIndex(),
            //            ArrayField::new('roles'),
            $rolesField,
        ];
    }
}
