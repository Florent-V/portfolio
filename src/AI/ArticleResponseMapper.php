<?php

declare(strict_types=1);

namespace App\AI;

use App\AI\DTO\ContentBlockData;
use App\AI\DTO\GeneratedArticleData;
use App\Entity\Article;
use App\Entity\ArticleContent;
use App\Entity\User;
use App\Enum\ArticleColumnSpan;
use App\Enum\ArticleContentFormat;
use App\Enum\ArticleContentType;
use App\Repository\ArticleRepository;
use App\Repository\TagRepository;
use Symfony\Component\String\Slugger\AsciiSlugger;

final readonly class ArticleResponseMapper
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private TagRepository $tagRepository,
    ) {
    }

    public function map(GeneratedArticleData $data, User $author): Article
    {
        $article = new Article();
        $article->setTitle($data->title);
        $article->setSlug($this->uniqueSlug($data->slug));
        $article->setAuthor($author);
        $article->setIsPublished(false);

        foreach ($data->contentBlocks as $blockData) {
            $article->addContentElement($this->mapBlock($blockData));
        }

        foreach ($data->tags as $tagName) {
            $article->addTag($this->tagRepository->findOneByNameOrCreate($tagName));
        }

        return $article;
    }

    private function mapBlock(ContentBlockData $data): ArticleContent
    {
        $block = new ArticleContent();
        $block->setType($this->resolveContentType($data->type));
        $block->setContent($data->content);
        $block->setFormat($this->resolveContentFormat($data->format));
        $block->setDisplayOrder($data->displayOrder);
        $block->setColumnSpan(ArticleColumnSpan::FULL);

        if ('code' === $data->type && null !== $data->language) {
            $block->setLanguage($data->language);
        }

        return $block;
    }

    private function resolveContentType(string $type): ArticleContentType
    {
        return match ($type) {
            'code'  => ArticleContentType::CODE,
            default => ArticleContentType::PARAGRAPH,
        };
    }

    private function resolveContentFormat(string $format): ArticleContentFormat
    {
        return match ($format) {
            'markdown' => ArticleContentFormat::MARKDOWN,
            default    => ArticleContentFormat::HTML,
        };
    }

    private function uniqueSlug(string $rawSlug): string
    {
        $slugger = new AsciiSlugger();
        $base    = strtolower((string) $slugger->slug($rawSlug));

        if (null === $this->articleRepository->findOneBy(['slug' => $base])) {
            return $base;
        }

        // Append short random suffix until unique
        do {
            $candidate = $base . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
        } while (null !== $this->articleRepository->findOneBy(['slug' => $candidate]));

        return $candidate;
    }
}
