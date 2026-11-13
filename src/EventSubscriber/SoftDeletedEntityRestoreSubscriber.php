<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * EasyAdmin loads the entity of {entityId} routes before the controller runs. A soft-deleted
 * entity is hidden by the "softdeleteable" filter, so the restore action would end in a 404.
 */
class SoftDeletedEntityRestoreSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        // Must run before AdminRouterSubscriber (priority 1) builds the admin context.
        return [RequestEvent::class => ['onKernelRequest', 2]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $routeName = $event->getRequest()->attributes->get('_route');

        $isRestoreRoute = \is_string($routeName)
            && str_starts_with($routeName, 'admin_')
            && str_ends_with($routeName, '_restore_entity');

        if (!$isRestoreRoute) {
            return;
        }

        $filters = $this->entityManager->getFilters();

        if ($filters->isEnabled('softdeleteable')) {
            $filters->disable('softdeleteable');
        }
    }
}
