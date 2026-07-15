<?php

declare(strict_types=1);

namespace App\AI;

use App\AI\Enum\AiProvider;
use App\AI\Exception\ArticleGenerationException;
use App\AI\Trait\ExtractsAiContent;
use App\Entity\Article;
use App\Entity\ArticleContent;
use App\Enum\ArticleColumnSpan;
use App\Enum\ArticleContentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

final readonly class ArticleSummaryService
{
    use ExtractsAiContent;

    public function __construct(
        private AiProviderRegistry $providerRegistry,
        private PromptBuilder $promptBuilder,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws ArticleGenerationException
     */
    public function generateAndAttach(
        Article $article,
        AiProvider $provider = AiProvider::OPENROUTER,
        string $language = 'fr',
    ): void {
        $agent = $this->providerRegistry->get($provider);

        $langInstruction = 'fr' === $language
            ? 'Write the summary in French.'
            : 'Write the summary in English.';

        $systemPrompt = $this->promptBuilder->build('summarizer/system', [
            'language_instruction' => $langInstruction,
        ]);

        $userPrompt = $this->promptBuilder->build('summarizer/user', [
            'title'   => (string) $article->getTitle(),
            'content' => $this->buildArticleContent($article),
        ]);

        $messages = new MessageBag(
            Message::forSystem($systemPrompt),
            Message::ofUser($userPrompt),
        );

        $result      = $agent->call($messages);
        $htmlContent = trim($this->extractContent($result));

        $summaryBlock = $this->findOrCreateSummaryBlock($article);
        $summaryBlock->setContent($htmlContent);

        $this->entityManager->persist($summaryBlock);
        $this->entityManager->flush();
    }

    private function buildArticleContent(Article $article): string
    {
        $blocks = $article->getContentElements()
            ->filter(static fn (ArticleContent $ac) => \in_array(
                $ac->getType(),
                [ArticleContentType::PARAGRAPH, ArticleContentType::CODE],
                true,
            ))
            ->map(static fn (ArticleContent $ac) => strip_tags((string) $ac->getContent()))
            ->toArray();

        return implode("\n\n", array_filter($blocks));
    }

    private function findOrCreateSummaryBlock(Article $article): ArticleContent
    {
        foreach ($article->getContentElements() as $block) {
            if (ArticleContentType::SUMMARY === $block->getType()) {
                return $block;
            }
        }

        $block = new ArticleContent();
        $block->setType(ArticleContentType::SUMMARY);
        $block->setColumnSpan(ArticleColumnSpan::FULL);
        $block->setDisplayOrder(0);
        $article->addContentElement($block);

        return $block;
    }
}
