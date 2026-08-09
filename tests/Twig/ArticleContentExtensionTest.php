<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Enum\ArticleContentFormat;
use App\Twig\ArticleContentExtension;
use PHPUnit\Framework\TestCase;

final class ArticleContentExtensionTest extends TestCase
{
    private ArticleContentExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new ArticleContentExtension();
    }

    public function testRenderFormattedContentConvertsMarkdownToHtml(): void
    {
        $result = $this->extension->renderFormattedContent('**bold** text', ArticleContentFormat::MARKDOWN);

        self::assertStringContainsString('<strong>bold</strong>', $result);
    }

    public function testRenderFormattedContentSanitizesDisallowedHtmlTags(): void
    {
        $result = $this->extension->renderFormattedContent(
            '<div>wrapped</div><script>alert(1)</script>',
            ArticleContentFormat::HTML,
        );

        self::assertStringNotContainsString('<div>', $result);
        self::assertStringNotContainsString('<script>', $result);
        self::assertStringContainsString('wrapped', $result);
        self::assertStringNotContainsString('alert(1)', $result);
    }

    public function testRenderFormattedContentKeepsAllowedHtmlTags(): void
    {
        $result = $this->extension->renderFormattedContent(
            '<p>Hello <strong>world</strong></p>',
            ArticleContentFormat::HTML,
        );

        self::assertSame('<p>Hello <strong>world</strong></p>', $result);
    }

    public function testRenderFormattedContentReturnsEmptyStringForNullHtmlContent(): void
    {
        self::assertSame('', $this->extension->renderFormattedContent(null, ArticleContentFormat::HTML));
    }

    public function testRenderFormattedContentReturnsEmptyStringForNullOrEmptyMarkdownContent(): void
    {
        self::assertSame('', $this->extension->renderFormattedContent(null, ArticleContentFormat::MARKDOWN));
        self::assertSame('', $this->extension->renderFormattedContent('', ArticleContentFormat::MARKDOWN));
    }
}
