<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\AboutMe;
use App\Service\Admin\AboutMeFieldsConfigurationService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/**
 * @extends AbstractCrudController<AboutMe>
 */
class AboutMeCrudController extends AbstractCrudController
{
    use AdminCrudControllerTrait;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly AboutMeFieldsConfigurationService $fieldsService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return AboutMe::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $this->configureCommonCrud($crud, 'À propos', 'À propos')
            ->addFormTheme('@VichUploader/Form/fields.html.twig');
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return $this->fieldsService->getFieldsForPage(
            $pageName,
            $this->getContext()
        );
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $count = $this->entityManager->getRepository(AboutMe::class)->count([]);

        // Always disable batch delete for this singleton
        $actions->disable(Action::BATCH_DELETE);

        if ($count > 0) {
            // If an entry exists, disable NEW and DELETE
            $actions->disable(Action::NEW, Action::DELETE);

            return $actions;
        }
        // If no entry exists, still disable DELETE
        $actions->disable(Action::DELETE);

        return $actions;
    }

    /**
     * Redirects to edit if a single AboutMe entry exists; otherwise stays on index.
     */
    public function index(AdminContext $context): \Symfony\Component\HttpFoundation\Response
    {
        $aboutMeEntry = $this->entityManager->getRepository(AboutMe::class)->findOneBy([]);

        if ($aboutMeEntry) {
            return $this->redirectToEdit(
                $this->adminUrlGenerator,
                (int) $aboutMeEntry->getId()
            );
        }

        return parent::index($context);
    }

    protected function getAdminUrlGenerator(): AdminUrlGenerator
    {
        return $this->adminUrlGenerator;
    }
}
