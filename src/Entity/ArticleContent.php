<?php

namespace App\Entity;

use App\Enum\ArticleContentType;
use App\Repository\ArticleContentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleContentRepository::class)]
class ArticleContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', enumType: ArticleContentType::class)]
    private ArticleContentType $type;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column]
    private ?int $displayOrder = null;

    #[ORM\ManyToOne(inversedBy: 'contentElements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?ArticleImage $image = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?ArticleCodeSnippet $codeSnippet = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ArticleContentType
    {
        return $this->type;
    }

    public function setType(ArticleContentType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getDisplayOrder(): ?int
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(int $displayOrder): static
    {
        $this->displayOrder = $displayOrder;

        return $this;
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

    public function getImage(): ?ArticleImage
    {
        return $this->image;
    }

    public function setImage(?ArticleImage $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCodeSnippet(): ?ArticleCodeSnippet
    {
        return $this->codeSnippet;
    }

    public function setCodeSnippet(?ArticleCodeSnippet $codeSnippet): static
    {
        $this->codeSnippet = $codeSnippet;

        return $this;
    }
}
