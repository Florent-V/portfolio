<?php

declare(strict_types=1);

namespace App\AI;

use App\AI\Enum\AiProvider;
use App\AI\Provider\AiProviderInterface;
use Symfony\AI\Agent\AgentInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final readonly class AiProviderRegistry
{
    /** @var array<string, AiProviderInterface> */
    private array $map;

    /**
     * @param iterable<AiProviderInterface> $providers
     */
    public function __construct(
        #[TaggedIterator('app.ai_provider')]
        iterable $providers,
        #[Autowire(service: 'ai.agent.article_generator_openrouter_web')]
        private AgentInterface $webSearchAgent,
        #[Autowire(env: 'AI_WEB_SEARCH_MODEL')]
        private string $webSearchModel,
    ) {
        $map = [];
        foreach ($providers as $provider) {
            $map[$provider->getProvider()->value] = $provider;
        }
        $this->map = $map;
    }

    public function get(AiProvider $provider): AgentInterface
    {
        return $this->resolve($provider)->getAgent();
    }

    public function getForWebSearch(): AgentInterface
    {
        return $this->webSearchAgent;
    }

    public function getModelName(AiProvider $provider): string
    {
        return $this->resolve($provider)->getModelName();
    }

    public function getWebSearchModelName(): string
    {
        return $this->webSearchModel;
    }

    private function resolve(AiProvider $provider): AiProviderInterface
    {
        return $this->map[$provider->value]
            ?? throw new \InvalidArgumentException("No AI provider registered for \"{$provider->value}\".");
    }
}
