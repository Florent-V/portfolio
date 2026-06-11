<?php

declare(strict_types=1);

namespace App\AI;

use App\Entity\AiGenerationLog;
use App\Entity\Article;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\AI\Platform\TokenUsage\TokenUsage;

final readonly class AiGenerationLogger
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /** @SuppressWarnings("PHPMD.ExcessiveParameterList") */
    public function log(
        string $action,
        string $provider,
        string $model,
        string $systemPrompt,
        string $userPrompt,
        int $durationMs,
        bool $success,
        ?string $rawResponse = null,
        ?ResultInterface $result = null,
        ?string $errorMessage = null,
        ?Article $article = null,
        ?User $author = null,
    ): AiGenerationLog {
        $log = new AiGenerationLog();
        $log->setAction($action);
        $log->setProvider($provider);
        $log->setModel($model);
        $log->setSystemPrompt($systemPrompt);
        $log->setUserPrompt($userPrompt);
        $log->setDurationMs($durationMs);
        $log->setSuccess($success);
        $log->setRawResponse($rawResponse);
        $log->setErrorMessage($errorMessage);
        $log->setArticle($article);
        $log->setAuthor($author);

        if (null !== $result) {
            /** @var TokenUsage|null $tokenUsage */
            $tokenUsage = $result->getMetadata()->get('token_usage');
            if (null !== $tokenUsage) {
                $log->setPromptTokens($tokenUsage->getPromptTokens());
                $log->setCompletionTokens($tokenUsage->getCompletionTokens());
                $log->setTotalTokens($tokenUsage->getTotalTokens());
                $log->setCachedTokens($tokenUsage->getCachedTokens());
            }
        }

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        return $log;
    }
}
