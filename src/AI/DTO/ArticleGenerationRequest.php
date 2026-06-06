<?php

declare(strict_types=1);

namespace App\AI\DTO;

use App\AI\Enum\AiProvider;
use App\Entity\User;

final readonly class ArticleGenerationRequest
{
    public function __construct(
        public string $topic,
        public string $language,
        public bool $useWebSearch,
        public ?string $extraInstructions,
        public User $author,
        public AiProvider $provider = AiProvider::OPENROUTER,
    ) {
    }
}
