<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AboutMe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AboutMe>
 */
class AboutMeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AboutMe::class);
    }
}
