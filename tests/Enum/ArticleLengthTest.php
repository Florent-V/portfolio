<?php

declare(strict_types=1);

namespace App\Tests\Enum;

use App\Enum\ArticleLength;
use PHPUnit\Framework\TestCase;

final class ArticleLengthTest extends TestCase
{
    public function testEachTierHasANonEmptyLabel(): void
    {
        foreach (ArticleLength::cases() as $length) {
            self::assertNotSame('', $length->label());
        }
    }

    public function testEachTierHasADistinctPromptInstruction(): void
    {
        $instructions = array_map(
            static fn (ArticleLength $length): string => $length->promptInstruction(),
            ArticleLength::cases(),
        );

        self::assertCount(3, array_unique($instructions));
    }

    public function testMediumInstructionMatchesThePreviousDefaultBehavior(): void
    {
        self::assertStringContainsString('4-6 paragraph blocks', ArticleLength::MEDIUM->promptInstruction());
    }
}
