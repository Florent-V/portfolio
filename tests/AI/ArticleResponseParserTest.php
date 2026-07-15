<?php

declare(strict_types=1);

namespace App\Tests\AI;

use App\AI\ArticleResponseParser;
use App\AI\Exception\ArticleGenerationException;
use PHPUnit\Framework\TestCase;

final class ArticleResponseParserTest extends TestCase
{
    private ArticleResponseParser $parser;

    protected function setUp(): void
    {
        $this->parser = new ArticleResponseParser();
    }

    public function testFormatDefaultsToHtmlWhenOmitted(): void
    {
        $data = $this->parser->parse(json_encode([
            'title'          => 'Titre',
            'slug'           => 'titre',
            'content_blocks' => [
                ['type' => 'paragraph', 'content' => '<h2>Intro</h2><p>Texte.</p>', 'display_order' => 1],
            ],
        ], JSON_THROW_ON_ERROR));

        self::assertSame('html', $data->contentBlocks[0]->format);
    }

    public function testFormatIsPreservedWhenMarkdown(): void
    {
        $data = $this->parser->parse(json_encode([
            'title'          => 'Titre',
            'slug'           => 'titre',
            'content_blocks' => [
                ['type' => 'paragraph', 'content' => '## Intro', 'display_order' => 1, 'format' => 'markdown'],
            ],
        ], JSON_THROW_ON_ERROR));

        self::assertSame('markdown', $data->contentBlocks[0]->format);
    }

    public function testTagsAreExtractedAndCappedAtFive(): void
    {
        $data = $this->parser->parse(json_encode([
            'title'          => 'Titre',
            'slug'           => 'titre',
            'tags'           => ['PHP', 'Symfony', 'Symfony', ' ', 'Doctrine', 'API', 'Tests'],
            'content_blocks' => [
                ['type' => 'paragraph', 'content' => '<h2>Intro</h2><p>Texte.</p>', 'display_order' => 1],
            ],
        ], JSON_THROW_ON_ERROR));

        self::assertSame(['PHP', 'Symfony', 'Doctrine', 'API', 'Tests'], $data->tags);
    }

    public function testTagsDefaultToEmptyArrayWhenMissing(): void
    {
        $data = $this->parser->parse(json_encode([
            'title'          => 'Titre',
            'slug'           => 'titre',
            'content_blocks' => [
                ['type' => 'paragraph', 'content' => '<h2>Intro</h2><p>Texte.</p>', 'display_order' => 1],
            ],
        ], JSON_THROW_ON_ERROR));

        self::assertSame([], $data->tags);
    }

    public function testInvalidFormatValueThrows(): void
    {
        $this->expectException(ArticleGenerationException::class);
        $this->expectExceptionMessage('unsupported format "yaml"');

        $this->parser->parse(json_encode([
            'title'          => 'Titre',
            'slug'           => 'titre',
            'content_blocks' => [
                ['type' => 'paragraph', 'content' => 'x', 'display_order' => 1, 'format' => 'yaml'],
            ],
        ], JSON_THROW_ON_ERROR));
    }
}
