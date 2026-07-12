<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\ArticleContent;
use App\Enum\ArticleContentFormat;
use League\CommonMark\CommonMarkConverter;
use Twig\Attribute\AsTwigFilter;

final class ArticleContentExtension
{
    private CommonMarkConverter $converter;

    public function __construct()
    {
        $this->converter = new CommonMarkConverter();
    }

    #[AsTwigFilter('markdown_to_html', isSafe: ['html'])]
    public function markdownToHtml(?string $content): string
    {
        if (null === $content || '' === $content) {
            return '';
        }

        return $this->converter->convert($content)->getContent();
    }

    #[AsTwigFilter('render_content', isSafe: ['html'])]
    public function renderContent(ArticleContent $block): string
    {
        return match ($block->getFormat()) {
            ArticleContentFormat::MARKDOWN => $this->markdownToHtml($block->getContent()),
            ArticleContentFormat::HTML     => $block->getContent() ?? '',
        };
    }
}
