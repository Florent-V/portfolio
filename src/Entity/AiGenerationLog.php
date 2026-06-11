<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AiGenerationLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AiGenerationLogRepository::class)]
#[ORM\Table(name: 'ai_generation_log')]
#[ORM\Index(columns: ['provider', 'created_at'])]
class AiGenerationLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // @phpstan-ignore-next-line
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private string $action = '';

    #[ORM\Column(length: 64)]
    private string $provider = '';

    #[ORM\Column(length: 128)]
    private string $model = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $systemPrompt = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $userPrompt = '';

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['columnDefinition' => 'LONGTEXT NULL'])]
    private ?string $rawResponse = null;

    #[ORM\Column]
    private int $durationMs = 0;

    #[ORM\Column(nullable: true)]
    private ?int $promptTokens = null;

    #[ORM\Column(nullable: true)]
    private ?int $completionTokens = null;

    #[ORM\Column(nullable: true)]
    private ?int $totalTokens = null;

    #[ORM\Column(nullable: true)]
    private ?int $cachedTokens = null;

    #[ORM\Column]
    private bool $success = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Article $article = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $author = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getSystemPrompt(): string
    {
        return $this->systemPrompt;
    }

    public function setSystemPrompt(string $systemPrompt): static
    {
        $this->systemPrompt = $systemPrompt;

        return $this;
    }

    public function getUserPrompt(): string
    {
        return $this->userPrompt;
    }

    public function setUserPrompt(string $userPrompt): static
    {
        $this->userPrompt = $userPrompt;

        return $this;
    }

    public function getRawResponse(): ?string
    {
        return $this->rawResponse;
    }

    public function setRawResponse(?string $rawResponse): static
    {
        $this->rawResponse = $rawResponse;

        return $this;
    }

    public function getDurationMs(): int
    {
        return $this->durationMs;
    }

    public function setDurationMs(int $durationMs): static
    {
        $this->durationMs = $durationMs;

        return $this;
    }

    public function getPromptTokens(): ?int
    {
        return $this->promptTokens;
    }

    public function setPromptTokens(?int $promptTokens): static
    {
        $this->promptTokens = $promptTokens;

        return $this;
    }

    public function getCompletionTokens(): ?int
    {
        return $this->completionTokens;
    }

    public function setCompletionTokens(?int $completionTokens): static
    {
        $this->completionTokens = $completionTokens;

        return $this;
    }

    public function getTotalTokens(): ?int
    {
        return $this->totalTokens;
    }

    public function setTotalTokens(?int $totalTokens): static
    {
        $this->totalTokens = $totalTokens;

        return $this;
    }

    public function getCachedTokens(): ?int
    {
        return $this->cachedTokens;
    }

    public function setCachedTokens(?int $cachedTokens): static
    {
        $this->cachedTokens = $cachedTokens;

        return $this;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): static
    {
        $this->success = $success;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): static
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }
}
