<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Technology;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Technology>
 */
class TechnologyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Technology::class);
    }
}
