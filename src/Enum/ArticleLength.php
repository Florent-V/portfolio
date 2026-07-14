<?php

declare(strict_types=1);

namespace App\Enum;

enum ArticleLength: string
{
    case SHORT  = 'short';
    case MEDIUM = 'medium';
    case LONG   = 'long';

    public function label(): string
    {
        return match ($this) {
            self::SHORT  => 'Court (~500 mots)',
            self::MEDIUM => 'Moyen (~1000 mots, par défaut)',
            self::LONG   => 'Long (~2000 mots)',
        };
    }

    public function promptInstruction(): string
    {
        return match ($this) {
            self::SHORT => 'Target a short read: ~500 words total, 2-3 paragraph blocks '
                . '(intro + 1 section + conclusion), plus 0-1 code block if relevant.',
            self::MEDIUM => 'Target a 2-minute read: ~1000 words total, 4-6 paragraph blocks '
                . '(intro + 2-3 sections + conclusion), plus 0-2 code blocks if relevant.',
            self::LONG => 'Target a long, in-depth read: ~2000 words total, 7-10 paragraph blocks '
                . '(intro + 5-8 sections + conclusion), plus 1-3 code blocks if relevant.',
        };
    }
}
