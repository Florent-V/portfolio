<?php

declare(strict_types=1);

namespace App\AI;

use App\AI\Exception\ArticleGenerationException;
use App\AI\Trait\ExtractsAiContent;
use App\Entity\Article;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\ResultInterface;

final readonly class AiArticleProcessor
{
    use ExtractsAiContent;

    public function __construct(
        private ArticleResponseParser $responseParser,
        private ArticleResponseMapper $responseMapper,
        private AiGenerationLogger $generationLogger,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws ArticleGenerationException
     */
    public function process(
        AgentInterface $agent,
        MessageBag $messages,
        string $action,
        string $provider,
        string $model,
        string $systemPrompt,
        string $userPrompt,
        User $author,
    ): Article {
        $callStart  = microtime(true);
        $result     = null;
        $rawContent = null;
        $article    = null;
        $error      = null;

        try {
            $result = $agent->call($messages);

            $durationMs = (int) ((microtime(true) - $callStart) * 1000);
            $this->logger->info('AI agent call completed', [
                'duration_ms' => $durationMs,
                'result_type' => $result::class,
            ]);

            $rawContent = $this->extractContent($result);

            $this->logger->debug('AI raw response received', [
                'duration_ms' => $durationMs,
                'length'      => \strlen($rawContent),
                'preview'     => mb_substr($rawContent, 0, 500),
            ]);

            $generatedData = $this->responseParser->parse($rawContent);

            $this->logger->info('AI article parsed successfully', [
                'title'       => $generatedData->title,
                'block_count' => \count($generatedData->contentBlocks),
            ]);

            $article = $this->responseMapper->map($generatedData, $author);
            $this->entityManager->persist($article);
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            $durationMs = (int) ((microtime(true) - $callStart) * 1000);
            $error      = $e;
            $this->logger->error(\sprintf('AI %s failed', $action), [
                'duration_ms' => $durationMs,
                'error'       => $e->getMessage(),
                'class'       => $e::class,
            ]);
        } finally {
            $durationMs = $durationMs ?? (int) ((microtime(true) - $callStart) * 1000);
            $this->generationLogger->log(
                action: $action,
                provider: $provider,
                model: $model,
                systemPrompt: $systemPrompt,
                userPrompt: $userPrompt,
                durationMs: $durationMs,
                success: null === $error,
                rawResponse: $rawContent,
                result: $result instanceof ResultInterface ? $result : null,
                errorMessage: $error?->getMessage(),
                article: $article,
                author: $author,
            );
        }

        if (null !== $error) {
            throw new ArticleGenerationException($error->getMessage(), previous: $error);
        }

        \assert($article instanceof Article);

        return $article;
    }
}
