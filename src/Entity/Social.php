<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\BlameableEntity;
use App\Repository\SocialRepository;
use App\Service\Admin\DuplicatableInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Mapping\Annotation\SoftDeleteable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SocialRepository::class)]
#[Gedmo\Loggable]
#[SoftDeleteable]
class Social implements DuplicatableInterface
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
    #[Assert\NotBlank(message: 'Le nom du réseau social est requis')]
    #[Assert\Length(max: 255, maxMessage: 'Le nom ne peut pas dépasser 255 caractères')]
    private ?string $name = null;

    #[ORM\Column(length: 500)]
    #[Assert\NotBlank(message: 'L\'URL est requise')]
    #[Assert\Url(message: 'L\'URL doit être valide')]
    #[Assert\Length(max: 500, maxMessage: 'L\'URL ne peut pas dépasser 500 caractères')]
    private ?string $url = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'icône est requise')]
    #[Assert\Length(max: 255, maxMessage: 'L\'icône ne peut pas dépasser 255 caractères')]
    private ?string $icon = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 0, max: 999, notInRangeMessage: 'L\'ordre doit être entre 0 et 999')]
    private ?int $sortOrder = 0;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\ManyToOne(targetEntity: AboutMe::class, inversedBy: 'socialLinks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AboutMe $aboutMe = null;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(?int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getAboutMe(): ?AboutMe
    {
        return $this->aboutMe;
    }

    public function setAboutMe(?AboutMe $aboutMe): static
    {
        $this->aboutMe = $aboutMe;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? 'Nouveau lien social';
    }

    public function prepareDuplicate(): void
    {
        $this->name     = 'Copie de ' . $this->name;
        $this->isActive = false;
    }
}
