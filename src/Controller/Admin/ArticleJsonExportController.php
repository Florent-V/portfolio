<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Enum\Role;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    path: '/admin/article/{id}/export-json',
    name: 'admin_article_export_json',
    requirements: ['id' => '\d+'],
    methods: ['GET']
)]
#[IsGranted(Role::ADMIN->value)]
final class ArticleJsonExportController extends AbstractController
{
    public function __invoke(int $id, ArticleRepository $articleRepository): JsonResponse
    {
        $article = $articleRepository->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article introuvable.');
        }

        $response = new JsonResponse($this->toArray($article));
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $article->getSlug() . '.json'
            )
        );

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(Article $article): array
    {
        return [
            'title' => $article->getTitle(),
            'slug'  => $article->getSlug(),
            'tags'  => array_map(
                static fn ($tag) => $tag->getName(),
                $article->getTags()->toArray()
            ),
            'content_blocks' => array_map(
                static fn ($block) => array_filter([
                    'type'          => $block->getType()->value,
                    'content'       => $block->getContent(),
                    'display_order' => $block->getDisplayOrder(),
                    'language'      => $block->getLanguage(),
                    'format'        => $block->getFormat()->value,
                ], static fn ($value) => null !== $value),
                $article->getContentElements()->toArray()
            ),
        ];
    }
}
