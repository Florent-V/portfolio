<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ArticleColumnSpan;
use App\Enum\ArticleContentFormat;
use App\Enum\ArticleContentType;
use App\Repository\ArticleContentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: ArticleContentRepository::class)]
#[Vich\Uploadable]
class ArticleContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // @phpstan-ignore-next-line
    private ?int $id = null;

    #[ORM\Column(type: 'string', enumType: ArticleContentType::class)]
    private ArticleContentType $type;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: 'string', enumType: ArticleContentFormat::class, options: ['default' => 'html'])]
    private ArticleContentFormat $format = ArticleContentFormat::HTML;

    #[ORM\Column]
    private int $displayOrder = 0;

    #[ORM\ManyToOne(inversedBy: 'contentElements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    #[Vich\UploadableField(mapping: 'article_image', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    private ?int $imageWidth = null;

    #[ORM\Column(nullable: true)]
    private ?int $imageHeight = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $altText = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $language = null;

    #[ORM\Column(type: 'string', enumType: ArticleColumnSpan::class)]
    private ArticleColumnSpan $columnSpan = ArticleColumnSpan::FULL;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

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

    public function getFormat(): ArticleContentFormat
    {
        return $this->format;
    }

    public function setFormat(ArticleContentFormat $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function getDisplayOrder(): int
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

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;
        if (null !== $imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getImageWidth(): ?int
    {
        return $this->imageWidth;
    }

    public function setImageWidth(?int $imageWidth): static
    {
        $this->imageWidth = $imageWidth;

        return $this;
    }

    public function getImageHeight(): ?int
    {
        return $this->imageHeight;
    }

    public function setImageHeight(?int $imageHeight): static
    {
        $this->imageHeight = $imageHeight;

        return $this;
    }

    public function getAltText(): ?string
    {
        return $this->altText;
    }

    public function setAltText(?string $altText): static
    {
        $this->altText = $altText;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getColumnSpan(): ArticleColumnSpan
    {
        return $this->columnSpan;
    }

    public function setColumnSpan(ArticleColumnSpan $columnSpan): static
    {
        $this->columnSpan = $columnSpan;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
