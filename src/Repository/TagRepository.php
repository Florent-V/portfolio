<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function findOneByNameOrCreate(string $name): Tag
    {
        $tag = $this->findOneBy(['name' => $name]);
        if (null === $tag) {
            $tag = (new Tag())->setName($name);
            $this->getEntityManager()->persist($tag);
        }

        return $tag;
    }
}
