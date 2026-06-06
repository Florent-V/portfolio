<?php

declare(strict_types=1);

namespace App\AI;

use App\AI\DTO\ArticleGenerationRequest;
use App\AI\Exception\ArticleGenerationException;
use App\Entity\Article;
use Psr\Log\LoggerInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

final readonly class ArticleGeneratorService
{
    public function __construct(
        private ArticleProviderRegistry $providerRegistry,
        private PromptBuilder $promptBuilder,
        private AiArticleProcessor $processor,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws ArticleGenerationException
     */
    public function generate(ArticleGenerationRequest $request): Article
    {
        $useWebSearch = $request->useWebSearch;
        $agent        = $useWebSearch
            ? $this->providerRegistry->getForWebSearch()
            : $this->providerRegistry->get($request->provider);
        $model = $useWebSearch
            ? $this->providerRegistry->getWebSearchModelName()
            : $this->providerRegistry->getModelName($request->provider);

        $systemPrompt = $this->promptBuilder->build('generator/system', [
            'LANGUAGE_INSTRUCTION' => $this->buildLangInstruction($request->language),
        ]);

        $userPrompt = $this->promptBuilder->build('generator/user', [
            'TOPIC'              => $request->topic,
            'EXTRA_INSTRUCTIONS' => $this->buildExtraBlock($request->extraInstructions),
            'WEB_SEARCH_HINT'    => $useWebSearch
                ? "\n\nUse your web search capabilities to find the most recent and accurate information on this topic."
                : '',
        ]);

        $messages = new MessageBag(
            Message::forSystem($systemPrompt),
            Message::ofUser($userPrompt),
        );

        $this->logger->info('AI article generation: start', [
            'topic'          => $request->topic,
            'language'       => $request->language,
            'provider'       => $request->provider->value,
            'model'          => $model,
            'use_web_search' => $useWebSearch,
        ]);

        return $this->processor->process(
            agent: $agent,
            messages: $messages,
            action: 'generate',
            provider: $request->provider->value,
            model: $model,
            systemPrompt: $systemPrompt,
            userPrompt: $userPrompt,
            author: $request->author,
        );
    }

    private function buildLangInstruction(string $language): string
    {
        return 'fr' === $language
            ? 'Write the entire article in French (titles, paragraphs, all content).'
            : 'Write the entire article in English (titles, paragraphs, all content).';
    }

    private function buildExtraBlock(?string $extraInstructions): string
    {
        if (null === $extraInstructions || '' === $extraInstructions) {
            return '';
        }

        return "\n\nAdditional instructions:\n{$extraInstructions}";
    }
}
