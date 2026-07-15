<?php

declare(strict_types=1);

namespace App\AI\DTO;

final readonly class GeneratedArticleData
{
    /**
     * @param ContentBlockData[] $contentBlocks
     * @param string[]           $tags
     */
    public function __construct(
        public string $title,
        public string $slug,
        public array $contentBlocks,
        public array $tags = [],
    ) {
    }
}
