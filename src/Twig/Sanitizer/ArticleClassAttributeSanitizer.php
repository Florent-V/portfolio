<?php

declare(strict_types=1);

namespace App\Twig\Sanitizer;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

/**
 * Restricts the `class` attribute on article HTML content to the exact Tailwind/DaisyUI
 * utility classes documented in the AI prompt (resources/ai/prompts/_partials/content_block_schema.twig).
 */
final class ArticleClassAttributeSanitizer implements AttributeSanitizerInterface
{
    private const array ALLOWED_CLASSES = [
        'text-primary', 'text-secondary', 'text-accent', 'text-error', 'text-warning', 'text-success', 'text-info',
        'font-bold', 'italic', 'underline', 'uppercase', 'tracking-wide',
        'text-sm', 'text-base', 'text-lg', 'text-xl',
    ];

    public function getSupportedElements(): ?array
    {
        return null;
    }

    public function getSupportedAttributes(): array
    {
        return ['class'];
    }

    public function sanitizeAttribute(
        string $element,
        string $attribute,
        string $value,
        HtmlSanitizerConfig $config,
    ): ?string {
        $allowed = array_values(array_intersect(explode(' ', $value), self::ALLOWED_CLASSES));

        return [] === $allowed ? null : implode(' ', $allowed);
    }
}
