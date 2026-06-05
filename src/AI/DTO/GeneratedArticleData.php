<?php

declare(strict_types=1);

namespace App\AI\DTO;

final readonly class GeneratedArticleData
{
    /**
     * @param ContentBlockData[] $contentBlocks
     */
    public function __construct(
        public string $title,
        public string $slug,
        public array $contentBlocks,
    ) {
    }
}
