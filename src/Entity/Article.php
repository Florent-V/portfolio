<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\BlameableEntity;
use App\Repository\ArticleRepository;
use App\Service\Admin\DuplicatableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Mapping\Annotation\SoftDeleteable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[Gedmo\Loggable]
#[SoftDeleteable]
#[Vich\Uploadable]
class Article implements DuplicatableInterface
{
    use TimestampableEntity;
    use BlameableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // @phpstan-ignore-next-line
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(
        message: 'Le slug ne peut pas être vide. Il est généralement généré à partir du titre.'
    )]
    private ?string $slug = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $publishedAt = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'articles')]
    private Collection $tags;

    #[Vich\UploadableField(mapping: 'article_image', fileNameProperty: 'mainImageName')]
    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        mimeTypesMessage: 'Veuillez uploader une image valide (JPEG, PNG, WEBP). Max 5MB.'
    )]
    private ?File $mainImageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mainImageName = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Type(type: 'bool')]
    private bool $isPublished = false;

    /**
     * @var Collection<int, ArticleContent>
     */
    #[ORM\OneToMany(
        targetEntity: ArticleContent::class,
        mappedBy: 'article',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['displayOrder' => 'ASC'])]
    private Collection $contentElements;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
        $this->tags            = new ArrayCollection();
        $this->contentElements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPublishedAt(): \DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeInterface $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

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

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getMainImageFile(): ?File
    {
        return $this->mainImageFile;
    }

    public function setMainImageFile(?File $mainImageFile = null): void
    {
        $this->mainImageFile = $mainImageFile;
        if (null !== $mainImageFile) {
            $this->setUpdatedAt(new \DateTime());
        }
    }

    public function getMainImageName(): ?string
    {
        return $this->mainImageName;
    }

    public function setMainImageName(?string $mainImageName): static
    {
        $this->mainImageName = $mainImageName;

        return $this;
    }

    public function isIsPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;
        if ($isPublished && null === $this->publishedAt) {
            $this->publishedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? 'Nouvel Article';
    }

    /**
     * @return Collection<int, ArticleContent>
     */
    public function getContentElements(): Collection
    {
        return $this->contentElements;
    }

    public function addContentElement(ArticleContent $contentElement): static
    {
        if (!$this->contentElements->contains($contentElement)) {
            $this->contentElements->add($contentElement);
            $contentElement->setArticle($this);
        }

        return $this;
    }

    public function removeContentElement(ArticleContent $contentElement): static
    {
        if ($this->contentElements->removeElement($contentElement)) {
            // set the owning side to null (unless already changed)
            if ($contentElement->getArticle() === $this) {
                $contentElement->setArticle(null);
            }
        }

        return $this;
    }

    public function prepareDuplicate(): void
    {
        $this->slug            = ($this->slug ?? 'article') . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
        $this->contentElements = new ArrayCollection();
        $this->mainImageFile   = null;
        $this->mainImageName   = null;
        $this->isPublished     = false;
        $this->publishedAt     = null;
    }
}
