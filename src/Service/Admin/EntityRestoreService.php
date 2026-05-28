<?php

declare(strict_types=1);

namespace App\Service\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final readonly class EntityRestoreService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Restores a soft-deleted entity by setting deletedAt to null.
     *
     * @template T of object
     *
     * @param class-string<T> $entityClass
     *
     * @throws \InvalidArgumentException if entity not found
     * @throws \LogicException           if entity is not deleted
     *
     * @return T
     */
    public function restore(string $entityClass, mixed $id): object
    {
        $this->entityManager->getFilters()->disable('softdeleteable');

        $entity = $this->entityManager->getRepository($entityClass)->find($id);

        $this->entityManager->getFilters()->enable('softdeleteable');

        if (!$entity instanceof $entityClass) {
            throw new \InvalidArgumentException(sprintf(
                'Entity %s with id "%s" not found.',
                $entityClass,
                (string) $id,
            ));
        }

        if (!method_exists($entity, 'getDeletedAt') || null === $entity->getDeletedAt()) {
            throw new \LogicException(sprintf(
                'Entity %s #%s is not deleted.',
                $entityClass,
                (string) $id,
            ));
        }

        $entity->setDeletedAt(null);
        $this->entityManager->flush();

        $this->logger->info('Entity restored.', [
            'class' => $entityClass,
            'id'    => (string) $id,
        ]);

        return $entity;
    }
}
