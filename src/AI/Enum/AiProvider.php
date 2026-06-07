<?php

declare(strict_types=1);

namespace App\AI\Enum;

enum AiProvider: string
{
    case OPENROUTER = 'openrouter';
    case OLLAMA     = 'ollama';
    case NVIDIA     = 'nvidia';
    case GEMINI     = 'gemini';
    case MISTRAL    = 'mistral';

    public function label(): string
    {
        return match ($this) {
            self::OPENROUTER => 'OpenRouter (Cloud)',
            self::OLLAMA     => 'Ollama (Cloud)',
            self::NVIDIA     => 'Nvidia NIM (Cloud)',
            self::GEMINI     => 'Google Gemini (Cloud)',
            self::MISTRAL    => 'Mistral AI (Cloud)',
        };
    }

    public function supportsWebSearch(): bool
    {
        return self::OPENROUTER === $this;
    }
}
