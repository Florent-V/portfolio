<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\Admin\EntityDuplicateService;
use App\Service\Admin\EntityRestoreService;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

trait AdminCrudControllerTrait
{
    protected function redirectToIndex(AdminUrlGenerator $urlGenerator): Response
    {
        return $this->redirect(
            $urlGenerator->setController(static::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );
    }

    protected function redirectToEdit(
        AdminUrlGenerator $urlGenerator,
        int $entityId,
    ): Response {
        return $this->redirect(
            $urlGenerator->setController(static::class)
                ->setAction(Action::EDIT)
                ->setEntityId($entityId)
                ->generateUrl()
        );
    }

    protected function redirectToDetail(
        AdminUrlGenerator $urlGenerator,
        int $entityId,
    ): Response {
        return $this->redirect(
            $urlGenerator->setController(static::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($entityId)->generateUrl()
        );
    }

    /**
     * Common CRUD configuration shared by all controllers.
     */
    protected function configureCommonCrud(
        Crud $crud,
        string $singularLabel,
        string $pluralLabel,
    ): Crud {
        return $crud
            ->setEntityLabelInSingular($singularLabel)
            ->setEntityLabelInPlural($pluralLabel)
            ->setPageTitle('index', 'Gestion des %entity_label_plural%')
            ->setPageTitle('new', 'Créer : %entity_label_singular%')
            ->setPageTitle('edit', 'Modifier : %entity_label_singular%')
            ->setPageTitle('detail', 'Détail : %entity_label_singular%')
            ->setDateTimeFormat('dd/MM/yyyy HH:mm')
            ->setPaginatorPageSize(25)
            ->setPaginatorRangeSize(4)
            ->setDefaultRowAction(Action::DETAIL)
            ->setAutofocusSearch()
            ->setTimezone('Europe/Paris');
    }

    /**
     * Common action configuration: icons, labels, Detail on INDEX, Save labels.
     */
    protected function configureCommonActions(Actions $actions): Actions
    {
        return $actions
            ->update(
                Crud::PAGE_INDEX,
                Action::EDIT,
                fn (Action $a) => $a
                    ->setIcon('fa fa-pencil')
                    ->setLabel('Modifier')
            )
            ->add(
                Crud::PAGE_INDEX,
                Action::DETAIL
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::DETAIL,
                fn (Action $a) => $a
                    ->setIcon('fa fa-eye')
                    ->setLabel('Détails')
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::DELETE,
                fn (Action $a) => $a
                    ->setIcon('fa fa-trash')
                    ->setLabel('Supprimer')
            )
            ->add(
                Crud::PAGE_NEW,
                Action::INDEX
            )
            ->update(
                Crud::PAGE_NEW,
                Action::SAVE_AND_RETURN,
                fn (Action $a) => $a
                    ->setLabel('Créer')
                    ->setIcon('fas fa-plus')
            )
            ->add(
                Crud::PAGE_NEW,
                Action::SAVE_AND_CONTINUE
            )
            ->update(
                Crud::PAGE_NEW,
                Action::SAVE_AND_CONTINUE,
                fn (Action $a) => $a
                    ->setLabel('Créer et continuer')
                    ->setIcon('fas fa-plus')
                    ->setCssClass('btn btn-outline-success')
            )
            ->update(
                Crud::PAGE_NEW,
                Action::SAVE_AND_ADD_ANOTHER,
                fn (Action $a) => $a
                    ->setLabel('Créer et ajouter un autre')
                    ->setIcon('fas fa-plus')
                    ->setCssClass('btn btn-outline-success')
            )
            ->reorder(
                Crud::PAGE_NEW,
                [
                    Action::INDEX, Action::SAVE_AND_CONTINUE, Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_RETURN,
                ]
            )
            ->add(
                Crud::PAGE_EDIT,
                Action::INDEX
            )
            ->update(
                Crud::PAGE_EDIT,
                Action::SAVE_AND_RETURN,
                fn (Action $a) => $a
                    ->setLabel('Enregistrer')
                    ->setIcon('fa fa-save')
            )
            ->update(
                Crud::PAGE_EDIT,
                Action::SAVE_AND_CONTINUE,
                fn (Action $a) => $a

                    ->setLabel('Enregistrer et continuer')
                    ->setIcon('fas fa-save')
                    ->setCssClass('btn btn-outline-success')
            );
    }

    /**
     * Adds soft-delete aware actions:
     * - hides Edit/Detail/Delete for deleted entities
     * - adds Restore action
     */
    protected function configureSoftDeleteActions(Actions $actions): Actions
    {
        return $actions
            ->add(
                Crud::PAGE_INDEX,
                $this->buildRestoreAction()
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::DETAIL,
                static fn (Action $a) => $a
                ->displayIf(
                    static fn (object $e) => !method_exists($e, 'getDeletedAt') || null === $e->getDeletedAt()
                )
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::EDIT,
                static fn (Action $a) => $a
                ->displayIf(
                    static fn (object $e) => !method_exists($e, 'getDeletedAt') || null === $e->getDeletedAt()
                )
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::DELETE,
                static fn (Action $a) => $a
                ->displayIf(
                    static fn (object $e) => !method_exists($e, 'getDeletedAt') || null === $e->getDeletedAt()
                )
            );
    }

    protected function buildRestoreAction(): Action
    {
        return Action::new('restore', 'Restaurer', 'fas fa-undo')
            ->linkToCrudAction('restoreEntity')
            ->addCssClass('btn btn-warning')
            ->displayIf(
                static fn (object $e) => method_exists($e, 'getDeletedAt') && null !== $e->getDeletedAt()
            );
    }

    protected function buildDuplicateAction(): Action
    {
        return Action::new('duplicateEntity', 'Dupliquer', 'fa fa-copy')
            ->linkToCrudAction('duplicateEntity')
            ->addCssClass('btn btn-secondary')
            ->displayIf(
                static fn (object $e) => !method_exists($e, 'getDeletedAt') || null === $e->getDeletedAt()
            );
    }

    /**
     * Restores a soft-deleted entity. Call from controllers that have SoftDeleteable entities.
     *
     * @param AdminContext<object> $context
     */
    public function restoreEntity(
        AdminContext $context,
        EntityRestoreService $restoreService,
    ): Response {
        $entityId = $context->getRequest()->query->get('entityId');

        if (!$entityId) {
            $this->addFlash('danger', 'Impossible de restaurer : ID manquant.');

            return $this->redirectToIndex($this->getAdminUrlGenerator());
        }

        /** @var class-string $entityClass */
        $entityClass = static::getEntityFqcn();

        try {
            $restoreService->restore($entityClass, $entityId);
            $this->addFlash('success', 'L\'entité a été restaurée avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur lors de la restauration : ' . $e->getMessage());
        }

        return $this->redirectToIndex($this->getAdminUrlGenerator());
    }

    /**
     * Duplicates an entity. Call from controllers that support duplication.
     *
     * @param AdminContext<object> $context
     */
    public function duplicateEntity(
        AdminContext $context,
        EntityDuplicateService $duplicateService,
    ): Response {
        $entityId = $context->getRequest()->query->get('entityId');

        if (!$entityId) {
            $this->addFlash('danger', 'Impossible de dupliquer : ID manquant.');

            return $this->redirectToIndex($this->getAdminUrlGenerator());
        }

        /** @var class-string $entityClass */
        $entityClass = static::getEntityFqcn();

        try {
            $clone = $duplicateService->duplicate($entityClass, $entityId);
            $this->addFlash(
                'success',
                'L\'entité a été dupliquée avec succès.'
            );

            return $this->redirectToEdit(
                $this->getAdminUrlGenerator(),
                $clone->getId()
            );
        } catch (\Exception $e) {
            $this->addFlash(
                'danger',
                'Erreur lors de la duplication : ' . $e->getMessage()
            );
        }

        return $this->redirectToIndex($this->getAdminUrlGenerator());
    }

    /**
     * Overrides the index query builder to show soft-deleted entities too.
     */
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        $queryBuilder = parent::createIndexQueryBuilder(
            $searchDto,
            $entityDto,
            $fields,
            $filters
        );
        $queryBuilder
            ->getEntityManager()
            ->getFilters()
            ->disable('softdeleteable')
        ;

        return $queryBuilder;
    }

    /**
     * @param array<string, string> $parameters
     */
    protected function addSuccessFlash(string $message, array $parameters = []): void
    {
        $this->addFlash('success', $this->formatFlashMessage($message, $parameters));
    }

    /**
     * @param array<string, string> $parameters
     */
    protected function addErrorFlash(string $message, array $parameters = []): void
    {
        $this->addFlash('danger', $this->formatFlashMessage($message, $parameters));
    }

    /**
     * @param array<string, string> $parameters
     */
    protected function addInfoFlash(string $message, array $parameters = []): void
    {
        $this->addFlash('info', $this->formatFlashMessage($message, $parameters));
    }

    /**
     * @param array<string, string> $parameters
     */
    private function formatFlashMessage(string $message, array $parameters = []): string
    {
        if ([] === $parameters) {
            return $message;
        }

        return strtr($message, $parameters);
    }

    abstract protected function getAdminUrlGenerator(): AdminUrlGenerator;
}
