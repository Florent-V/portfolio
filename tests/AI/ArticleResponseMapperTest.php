<?php

declare(strict_types=1);

namespace App\Tests\AI;

use App\AI\ArticleResponseMapper;
use App\AI\DTO\ContentBlockData;
use App\AI\DTO\GeneratedArticleData;
use App\Entity\User;
use App\Enum\ArticleContentFormat;
use App\Repository\ArticleRepository;
use PHPUnit\Framework\TestCase;

final class ArticleResponseMapperTest extends TestCase
{
    public function testMapAssignsTheFormatRequestedPerBlock(): void
    {
        $articleRepository = $this->createMock(ArticleRepository::class);
        $articleRepository->method('findOneBy')->willReturn(null);

        $mapper = new ArticleResponseMapper($articleRepository);

        $data = new GeneratedArticleData(
            title: 'Titre',
            slug: 'titre',
            contentBlocks: [
                new ContentBlockData(type: 'paragraph', content: '## Intro', displayOrder: 1, format: 'markdown'),
                new ContentBlockData(type: 'code', content: 'echo 1;', displayOrder: 2, language: 'php', format: 'html'),
            ],
        );

        $article = $mapper->map($data, $this->createMock(User::class));
        $blocks  = $article->getContentElements();

        self::assertSame(ArticleContentFormat::MARKDOWN, $blocks[0]->getFormat());
        self::assertSame(ArticleContentFormat::HTML, $blocks[1]->getFormat());
    }
}
