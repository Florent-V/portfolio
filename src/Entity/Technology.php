<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\BlameableEntity;
use App\Repository\TechnologyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Mapping\Annotation\SoftDeleteable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: TechnologyRepository::class)]
#[Gedmo\Loggable]
#[SoftDeleteable]
#[Vich\Uploadable]
class Technology
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
    private ?string $name = null;

    #[Vich\UploadableField(mapping: 'technology_image', fileNameProperty: 'imageName')]
    #[Assert\File(
        maxSize: '1M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'],
        mimeTypesMessage: 'Please upload a valid image (JPEG, PNG, WEBP, SVG).'
    )]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Range(min: 1, max: 5)]
    private ?int $level = null;

    #[ORM\ManyToOne(inversedBy: 'technologies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SkillCategory $category = null;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\ManyToMany(targetEntity: Article::class, mappedBy: 'technologies')]
    private Collection $articles;

    /**
     * @var Collection<int, Project>
     */
    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'technologies')]
    private Collection $projects;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null; // For Symfony UX icons

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->projects = new ArrayCollection();
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

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile = null): static
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->updatedAt = new \DateTime();
        }

        return $this;
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

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getCategory(): ?SkillCategory
    {
        return $this->category;
    }

    public function setCategory(?SkillCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->addTechnology($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            $article->removeTechnology($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->addTechnology($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            $project->removeTechnology($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? 'New Technology';
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }
}
