<?php

declare(strict_types=1);

namespace App\Tests\AI;

use App\AI\PromptBuilder;
use PHPUnit\Framework\TestCase;

final class PromptBuilderTest extends TestCase
{
    private PromptBuilder $promptBuilder;

    protected function setUp(): void
    {
        $this->promptBuilder = new PromptBuilder(\dirname(__DIR__, 2));
    }

    public function testGeneratorSystemPromptRendersHtmlFormatRules(): void
    {
        $result = $this->promptBuilder->build('generator/system', [
            'language_instruction' => 'Write in French.',
            'format'               => 'html',
            'length_instruction'   => 'Target ~500 words.',
        ]);

        self::assertStringContainsString('<h2> tag for the section title', $result);
        self::assertStringContainsString('"format": "html"', $result);
        self::assertStringContainsString('Target ~500 words.', $result);
        self::assertStringContainsString('Write in French.', $result);
    }

    public function testGeneratorSystemPromptRendersMarkdownFormatRules(): void
    {
        $result = $this->promptBuilder->build('generator/system', [
            'language_instruction' => 'Write in English.',
            'format'               => 'markdown',
            'length_instruction'   => 'Target ~2000 words.',
        ]);

        self::assertStringContainsString('## Section Title', $result);
        self::assertStringContainsString('"format": "markdown"', $result);
        self::assertStringNotContainsString('<h2>', $result);
    }

    public function testHumanizerSystemPromptWorksWithoutALengthInstruction(): void
    {
        $result = $this->promptBuilder->build('humanizer/system', [
            'language_instruction' => 'Write in French.',
            'format'               => 'html',
        ]);

        self::assertStringContainsString('"format": "html"', $result);
        self::assertStringContainsString("Preserve the author's voice", $result);
    }

    public function testUnknownTemplateThrows(): void
    {
        $this->expectException(\Twig\Error\LoaderError::class);

        $this->promptBuilder->build('does-not-exist/system', []);
    }

    /**
     * Pins the exact keys ArticleGeneratorService::generate() passes to PromptBuilder::build()
     * ('language_instruction', 'format', 'length_instruction'). The _partials/content_block_schema.twig
     * partial guards length_instruction with `is defined and is not empty`, so a key-name typo or
     * rename in ArticleGeneratorService would silently drop the length tier from the prompt without
     * strict_variables catching it. This test fails if that key name drifts.
     */
    public function testGeneratorSystemPromptWithMediumLengthContainsThatTiersInstructionText(): void
    {
        $result = $this->promptBuilder->build('generator/system', [
            'language_instruction' => 'Write in French.',
            'format'               => \App\Enum\ArticleContentFormat::HTML->value,
            'length_instruction'   => \App\Enum\ArticleLength::MEDIUM->promptInstruction(),
        ]);

        self::assertStringContainsString(\App\Enum\ArticleLength::MEDIUM->promptInstruction(), $result);
    }
}
