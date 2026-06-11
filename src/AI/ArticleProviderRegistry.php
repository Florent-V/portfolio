<?php

declare(strict_types=1);

namespace App\AI;

use App\AI\Enum\AiProvider;
use Symfony\AI\Agent\AgentInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class ArticleProviderRegistry
{
    public function __construct(
        #[Autowire(service: 'ai.agent.article_generator_openrouter')]
        private AgentInterface $openrouterAgent,
        #[Autowire(service: 'ai.agent.article_generator_openrouter_web')]
        private AgentInterface $openrouterWebAgent,
        #[Autowire(service: 'ai.agent.article_generator_ollama')]
        private AgentInterface $ollamaAgent,
        #[Autowire(service: 'ai.agent.article_generator_nvidia')]
        private AgentInterface $nvidiaAgent,
        #[Autowire(env: 'AI_DEFAULT_MODEL')]
        private string $openrouterModel,
        #[Autowire(env: 'AI_WEB_SEARCH_MODEL')]
        private string $openrouterWebModel,
        #[Autowire(env: 'AI_OLLAMA_MODEL')]
        private string $ollamaModel,
        #[Autowire(env: 'AI_NVIDIA_MODEL')]
        private string $nvidiaModel,
    ) {
    }

    public function get(AiProvider $provider): AgentInterface
    {
        return match ($provider) {
            AiProvider::OPENROUTER => $this->openrouterAgent,
            AiProvider::OLLAMA     => $this->ollamaAgent,
            AiProvider::NVIDIA     => $this->nvidiaAgent,
        };
    }

    public function getForWebSearch(): AgentInterface
    {
        return $this->openrouterWebAgent;
    }

    public function getModelName(AiProvider $provider): string
    {
        return match ($provider) {
            AiProvider::OPENROUTER => $this->openrouterModel,
            AiProvider::OLLAMA     => $this->ollamaModel,
            AiProvider::NVIDIA     => $this->nvidiaModel,
        };
    }

    public function getWebSearchModelName(): string
    {
        return $this->openrouterWebModel;
    }
}
