<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\Role;
use App\Service\Admin\UserFieldsConfigurationService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @extends AbstractCrudController<User>
 */
#[IsGranted(Role::ADMIN->value)]
class UserCrudController extends AbstractCrudController
{
    use AdminCrudControllerTrait;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly UserFieldsConfigurationService $fieldsService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $this->configureCommonCrud($crud, 'Utilisateur', 'Utilisateurs');
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        return $this->configureCommonActions($actions);
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $rolesField = ChoiceField::new('roles')
            ->setChoices([
                'User'        => 'ROLE_USER',
                'Admin'       => 'ROLE_ADMIN',
                'Super Admin' => 'ROLE_SUPER_ADMIN',
            ])
            ->allowMultipleChoices();

        if (Crud::PAGE_EDIT === $pageName) {
            $entityInstance = $this->getContext()?->getEntity()->getInstance();
            if ($entityInstance instanceof User && $currentUser->getId() === $entityInstance->getId()) {
                $rolesField->setFormTypeOption('disabled', true);
            }
        }

        return [...$this->fieldsService->getFieldsForPage($pageName, $this->getContext()), $rolesField];
    }

    protected function getAdminUrlGenerator(): AdminUrlGenerator
    {
        return $this->adminUrlGenerator;
    }
}
