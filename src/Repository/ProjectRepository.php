<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Project;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * @param Collection<int, Tag>|Tag[] $tags
     *
     * @return Project[]
     */
    public function findPublishedByTags(iterable $tags, ?Project $exclude = null, int $limit = 5): array
    {
        $tagIds = [];
        foreach ($tags as $tag) {
            $tagIds[] = $tag->getId();
        }

        if ([] === $tagIds) {
            return [];
        }

        $qb = $this->createQueryBuilder('p')
            ->join('p.tags', 't')
            ->andWhere('p.published = :published')
            ->andWhere('t.id IN (:tagIds)')
            ->setParameter('published', true)
            ->setParameter('tagIds', $tagIds)
            ->orderBy('p.startDate', 'DESC')
            ->setMaxResults($limit)
        ;

        if (null !== $exclude && null !== $exclude->getId()) {
            $qb->andWhere('p.id != :excludeId')->setParameter('excludeId', $exclude->getId());
        }

        return $qb->getQuery()->getResult();
    }
}
