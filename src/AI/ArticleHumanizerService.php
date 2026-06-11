<?php

declare(strict_types=1);

namespace App\AI;

use App\AI\DTO\ArticleHumanizeRequest;
use App\AI\Exception\ArticleGenerationException;
use App\Entity\Article;
use Psr\Log\LoggerInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

final readonly class ArticleHumanizerService
{
    public function __construct(
        private AiProviderRegistry $providerRegistry,
        private PromptBuilder $promptBuilder,
        private AiArticleProcessor $processor,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws ArticleGenerationException
     */
    public function humanize(ArticleHumanizeRequest $request): Article
    {
        $agent = $this->providerRegistry->get($request->provider);
        $model = $this->providerRegistry->getModelName($request->provider);

        $systemPrompt = $this->promptBuilder->build('humanizer/system', [
            'LANGUAGE_INSTRUCTION' => 'fr' === $request->language
                ? 'The article must be written in French. Preserve the original language of any code blocks.'
                : 'The article must be written in English. Preserve the original language of any code blocks.',
        ]);

        $userPrompt = $this->promptBuilder->build('humanizer/user', [
            'RAW_TEXT' => $request->rawText,
        ]);

        $messages = new MessageBag(
            Message::forSystem($systemPrompt),
            Message::ofUser($userPrompt),
        );

        $this->logger->info('AI article humanization: start', [
            'provider'     => $request->provider->value,
            'model'        => $model,
            'language'     => $request->language,
            'raw_text_len' => \strlen($request->rawText),
        ]);

        return $this->processor->process(
            agent: $agent,
            messages: $messages,
            action: 'humanize',
            provider: $request->provider->value,
            model: $model,
            systemPrompt: $systemPrompt,
            userPrompt: $userPrompt,
            author: $request->author,
        );
    }
}
