<?php

declare(strict_types=1);

namespace App\AI\Provider;

use App\AI\Enum\AiProvider;
use Symfony\AI\Agent\AgentInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AutoconfigureTag('app.ai_provider')]
final readonly class GeminiProvider implements AiProviderInterface
{
    public function __construct(
        #[Autowire(service: 'ai.agent.article_generator_gemini')]
        private AgentInterface $agent,
        #[Autowire(env: 'AI_GEMINI_MODEL')]
        private string $modelName,
    ) {
    }

    public function getProvider(): AiProvider
    {
        return AiProvider::GEMINI;
    }

    public function getAgent(): AgentInterface
    {
        return $this->agent;
    }

    public function getModelName(): string
    {
        return $this->modelName;
    }
}
