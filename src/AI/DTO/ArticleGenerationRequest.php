<?php

declare(strict_types=1);

namespace App\AI\DTO;

use App\AI\Enum\AiProvider;
use App\Entity\User;
use App\Enum\ArticleContentFormat;
use App\Enum\ArticleLength;

final readonly class ArticleGenerationRequest
{
    public function __construct(
        public string $topic,
        public string $language,
        public bool $useWebSearch,
        public ?string $extraInstructions,
        public User $author,
        public AiProvider $provider = AiProvider::OPENROUTER,
        public ArticleLength $length = ArticleLength::MEDIUM,
        public ArticleContentFormat $format = ArticleContentFormat::HTML,
    ) {
    }
}
