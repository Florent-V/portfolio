<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Hobby;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hobby>
 */
class HobbyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hobby::class);
    }
}
