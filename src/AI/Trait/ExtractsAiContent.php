<?php

declare(strict_types=1);

namespace App\AI\Trait;

use App\AI\Exception\ArticleGenerationException;
use Symfony\AI\Platform\Result\MultiPartResult;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\AI\Platform\Result\TextResult;

trait ExtractsAiContent
{
    private function extractContent(ResultInterface $result): string
    {
        $content = match (true) {
            $result instanceof TextResult      => $result->getContent(),
            $result instanceof MultiPartResult => $result->asText(),
            default                            => throw new ArticleGenerationException(
                \sprintf('Unexpected result type from AI agent: %s', $result::class),
            ),
        };

        if ('' === $content) {
            throw new ArticleGenerationException('AI provider returned an empty response.');
        }

        return $content;
    }
}
