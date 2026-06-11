<?php

declare(strict_types=1);

namespace App\AI\Enum;

enum AiProvider: string
{
    case OPENROUTER = 'openrouter';
    case OLLAMA     = 'ollama';
    case NVIDIA     = 'nvidia';

    public function label(): string
    {
        return match ($this) {
            self::OPENROUTER => 'OpenRouter (Cloud)',
            self::OLLAMA     => 'Ollama (Cloud)',
            self::NVIDIA     => 'Nvidia NIM (Cloud)',
        };
    }

    public function supportsWebSearch(): bool
    {
        return self::OPENROUTER === $this;
    }
}
