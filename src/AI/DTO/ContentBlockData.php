<?php

declare(strict_types=1);

namespace App\AI\DTO;

final readonly class ContentBlockData
{
    public function __construct(
        public string $type,
        public string $content,
        public int $displayOrder,
        public ?string $language = null,
        public string $format = 'html',
    ) {
    }
}
