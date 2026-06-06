<?php

declare(strict_types=1);

namespace App\AI;

use App\AI\DTO\ContentBlockData;
use App\AI\DTO\GeneratedArticleData;
use App\AI\Exception\ArticleGenerationException;

final class ArticleResponseParser
{
    /**
     * Parses the raw LLM text response into a GeneratedArticleData DTO.
     *
     * @throws ArticleGenerationException
     */
    public function parse(string $rawResponse): GeneratedArticleData
    {
        $json = $this->extractJson($rawResponse);
        $data = $this->decodeJson($json);

        $this->validate($data);

        $contentBlocks = array_map(
            static fn (array $block): ContentBlockData => new ContentBlockData(
                type: (string) $block['type'],
                content: (string) $block['content'],
                displayOrder: (int) $block['display_order'],
                language: isset($block['language']) ? (string) $block['language'] : null,
            ),
            $data['content_blocks'],
        );

        usort(
            $contentBlocks,
            static fn (ContentBlockData $a, ContentBlockData $b): int => $a->displayOrder <=> $b->displayOrder
        );

        return new GeneratedArticleData(
            title: (string) $data['title'],
            slug: (string) $data['slug'],
            contentBlocks: $contentBlocks,
        );
    }

    private function extractJson(string $raw): string
    {
        $raw = trim($raw);

        // Strip markdown code fences if the model wrapped the JSON anyway
        if (str_starts_with($raw, '```')) {
            $raw = (string) preg_replace('/^```[a-z]*\n?/i', '', $raw);
            $raw = rtrim($raw, '`');
            $raw = trim($raw);
        }

        // Find the first { and last } to extract the JSON object
        $start = strpos($raw, '{');
        $end   = strrpos($raw, '}');

        if (false === $start || false === $end) {
            throw new ArticleGenerationException('The AI response does not contain a valid JSON object.');
        }

        return substr($raw, $start, $end - $start + 1);
    }

    /**
     * @throws ArticleGenerationException
     *
     * @return array<string, mixed>
     */
    private function decodeJson(string $json): array
    {
        $data = json_decode($json, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new ArticleGenerationException(
                \sprintf('Failed to parse AI response as JSON: %s', json_last_error_msg()),
            );
        }

        if (!\is_array($data)) {
            throw new ArticleGenerationException('AI response JSON is not an object.');
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws ArticleGenerationException
     */
    private function validate(array $data): void
    {
        $this->validateRequiredFields($data);
        $this->validateContentBlocks($data['content_blocks']);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws ArticleGenerationException
     */
    private function validateRequiredFields(array $data): void
    {
        foreach (['title', 'slug', 'content_blocks'] as $field) {
            if (!isset($data[$field]) || ('' === $data[$field] && 'content_blocks' !== $field)) {
                throw new ArticleGenerationException(\sprintf('AI response is missing required field "%s".', $field));
            }
        }
    }

    /**
     * @throws ArticleGenerationException
     */
    private function validateContentBlocks(mixed $blocks): void
    {
        if (!\is_array($blocks) || [] === $blocks) {
            throw new ArticleGenerationException('AI response "content_blocks" must be a non-empty array.');
        }

        foreach ($blocks as $i => $block) {
            $this->validateBlock((int) $i, (array) $block);
        }
    }

    /**
     * @param array<string, mixed> $block
     *
     * @throws ArticleGenerationException
     */
    private function validateBlock(int $i, array $block): void
    {
        if (!isset($block['type'], $block['content'], $block['display_order'])) {
            throw new ArticleGenerationException(\sprintf('Content block #%d is missing required fields.', $i));
        }

        if (!\in_array($block['type'], ['paragraph', 'code'], true)) {
            throw new ArticleGenerationException(
                \sprintf('Content block #%d has unsupported type "%s".', $i, $block['type']),
            );
        }
    }
}
