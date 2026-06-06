<?php

declare(strict_types=1);

namespace App\AI\DTO;

use App\AI\Enum\AiProvider;
use App\Entity\User;

final readonly class ArticleHumanizeRequest
{
    public function __construct(
        public string $rawText,
        public string $language,
        public User $author,
        public AiProvider $provider = AiProvider::OPENROUTER,
    ) {
    }
}
