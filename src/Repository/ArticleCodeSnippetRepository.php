<?php

namespace App\Repository;

use App\Entity\ArticleCodeSnippet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticleCodeSnippet>
 *
 * @method ArticleCodeSnippet|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArticleCodeSnippet|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArticleCodeSnippet[]    findAll()
 * @method ArticleCodeSnippet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleCodeSnippetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleCodeSnippet::class);
    }
}
