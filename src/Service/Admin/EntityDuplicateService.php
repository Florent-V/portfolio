<?php

declare(strict_types=1);

namespace App\Service\Admin;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final class EntityDuplicateService
{
    /** @var list<string> */
    private const array LIFECYCLE_FIELDS = ['id', 'createdAt', 'updatedAt', 'deletedAt', 'createdBy', 'updatedBy'];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Clones an entity, resets its lifecycle fields, reinitializes its collections,
     * calls prepareDuplicate() if the entity implements DuplicatableInterface, then persists.
     *
     * @template T of object
     *
     * @param class-string<T> $entityClass
     *
     * @throws \InvalidArgumentException if entity not found
     *
     * @return T
     */
    public function duplicate(string $entityClass, mixed $id): object
    {
        $this->em->getFilters()->disable('softdeleteable');
        $entity = $this->em->getRepository($entityClass)->find($id);
        $this->em->getFilters()->enable('softdeleteable');

        if (!$entity instanceof $entityClass) {
            throw new \InvalidArgumentException(sprintf(
                'Entity %s with id "%s" not found.',
                $entityClass,
                (string) $id,
            ));
        }

        $clone = clone $entity;

        $this->resetLifecycleFields($clone);
        $this->reinitializeCollections($clone);

        if ($clone instanceof DuplicatableInterface) {
            $clone->prepareDuplicate();
        }

        $this->em->persist($clone);
        $this->em->flush();

        $this->logger->info('Entity duplicated.', [
            'class'       => $entityClass,
            'original_id' => (string) $id,
            'clone_id'    => (string) $clone->getId(),
        ]);

        return $clone;
    }

    private function resetLifecycleFields(object $entity): void
    {
        foreach (self::LIFECYCLE_FIELDS as $field) {
            $this->setPropertyNull($entity, $field);
        }
    }

    private function setPropertyNull(object $entity, string $fieldName): void
    {
        $class = new \ReflectionClass($entity);

        do {
            if ($class->hasProperty($fieldName)) {
                $property = $class->getProperty($fieldName);
                $property->setValue($entity, null);

                return;
            }
        } while ($class = $class->getParentClass());
    }

    private function reinitializeCollections(object $clone): void
    {
        $metadata = $this->em->getClassMetadata($clone::class);

        foreach (array_keys($metadata->getAssociationMappings()) as $fieldName) {
            if (!$metadata->isCollectionValuedAssociation($fieldName)) {
                continue;
            }

            $accessor = $metadata->getPropertyAccessor($fieldName);
            if (null === $accessor) {
                continue;
            }

            $collection = $accessor->getValue($clone);
            if ($collection instanceof Collection) {
                $accessor->setValue($clone, new ArrayCollection($collection->toArray()));
            }
        }
    }
}
