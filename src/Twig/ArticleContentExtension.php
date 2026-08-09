<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\ArticleContent;
use App\Enum\ArticleContentFormat;
use App\Service\Sanitizer\ArticleClassAttributeSanitizer;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerAction;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Twig\Attribute\AsTwigFilter;

final class ArticleContentExtension
{
    private MarkdownConverter $converter;
    private HtmlSanitizer $htmlSanitizer;

    public function __construct()
    {
        $environment = new Environment();
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new AutolinkExtension());
        $environment->addExtension(new DisallowedRawHtmlExtension());

        $this->converter = new MarkdownConverter($environment);

        $sanitizerConfig = (new HtmlSanitizerConfig())
            ->defaultAction(HtmlSanitizerAction::Block)
            ->dropElement('script')
            // defaultAction(Block) below keeps text of disallowed tags;
            // script bodies must still be fully removed, not just unwrapped.
            // Note: style/title tag bodies cannot be fully dropped this way (Symfony's
            // HtmlSanitizer silently ignores dropElement() for head-context elements in
            // body content) — their text will render as visible page text. Accepted risk:
            // harmless (not executable), and no existing content currently uses these tags.
            ->allowElement('h2', ['class'])
            ->allowElement('h3', ['class'])
            ->allowElement('p', ['class'])
            ->allowElement('a', ['href', 'title', 'target', 'class'])
            ->allowElement('strong', ['class'])
            ->allowElement('em', ['class'])
            ->allowElement('code', ['class'])
            ->allowElement('ul', ['class'])
            ->allowElement('ol', ['class'])
            ->allowElement('li', ['class'])
            ->allowElement('blockquote', ['class'])
            ->allowElement('table', ['class'])
            ->allowElement('thead', ['class'])
            ->allowElement('tbody', ['class'])
            ->allowElement('tr', ['class'])
            ->allowElement('th', ['class'])
            ->allowElement('td', ['class'])
            ->withAttributeSanitizer(new ArticleClassAttributeSanitizer());

        $this->htmlSanitizer = new HtmlSanitizer($sanitizerConfig);
    }

    #[AsTwigFilter('markdown_to_html', isSafe: ['html'])]
    public function markdownToHtml(?string $content): string
    {
        if (null === $content || '' === $content) {
            return '';
        }

        return $this->htmlSanitizer->sanitize($this->converter->convert($content)->getContent());
    }

    #[AsTwigFilter('render_formatted_content', isSafe: ['html'])]
    public function renderFormattedContent(?string $content, ArticleContentFormat $format): string
    {
        return match ($format) {
            ArticleContentFormat::MARKDOWN => $this->markdownToHtml($content),
            ArticleContentFormat::HTML     => $this->htmlSanitizer->sanitize($content ?? ''),
        };
    }

    #[AsTwigFilter('render_content', isSafe: ['html'])]
    public function renderContent(ArticleContent $block): string
    {
        return $this->renderFormattedContent($block->getContent(), $block->getFormat());
    }
}
