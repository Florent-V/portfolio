<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @return Article[] Returns an array of published Article objects ordered by publishedAt DESC
     */
    public function findPublished(?int $limit = null, ?int $offset = null): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isPublished = :isPublished')
            ->andWhere('a.publishedAt <= :now')
            ->setParameter('isPublished', true)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countPublished(): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.isPublished = :isPublished')
            ->andWhere('a.publishedAt <= :now')
            ->setParameter('isPublished', true)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findOneBySlug(string $slug): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOnePublishedBySlug(string $slug): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.slug = :slug')
            ->andWhere('a.isPublished = :isPublished')
            ->andWhere('a.publishedAt <= :now')
            ->setParameter('slug', $slug)
            ->setParameter('isPublished', true)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param Collection<int, Tag>|Tag[] $tags
     *
     * @return Article[]
     */
    public function findPublishedByTags(iterable $tags, ?Article $exclude = null, int $limit = 5): array
    {
        $tagIds = [];
        foreach ($tags as $tag) {
            $tagIds[] = $tag->getId();
        }

        if ([] === $tagIds) {
            return [];
        }

        $qb = $this->createQueryBuilder('a')
            ->join('a.tags', 't')
            ->andWhere('a.isPublished = :isPublished')
            ->andWhere('a.publishedAt <= :now')
            ->andWhere('t.id IN (:tagIds)')
            ->setParameter('isPublished', true)
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('tagIds', $tagIds)
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($limit)
        ;

        if (null !== $exclude && null !== $exclude->getId()) {
            $qb->andWhere('a.id != :excludeId')->setParameter('excludeId', $exclude->getId());
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Article[]
     */
    public function findLatestPublished(?Article $exclude = null, int $limit = 2): array
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.isPublished = :isPublished')
            ->andWhere('a.publishedAt <= :now')
            ->setParameter('isPublished', true)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($limit)
        ;

        if (null !== $exclude && null !== $exclude->getId()) {
            $qb->andWhere('a.id != :excludeId')->setParameter('excludeId', $exclude->getId());
        }

        return $qb->getQuery()->getResult();
    }
}
