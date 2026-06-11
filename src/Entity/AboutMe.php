<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\BlameableEntity;
use App\Repository\AboutMeRepository;
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

#[ORM\Entity(repositoryClass: AboutMeRepository::class)]
#[Gedmo\Loggable]
#[SoftDeleteable]
#[Vich\Uploadable]
class AboutMe
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
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[Vich\UploadableField(mapping: 'cv_file', fileNameProperty: 'cvFileName')]
    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['application/pdf', 'application/x-pdf'],
        mimeTypesMessage: 'Please upload a valid PDF document for the CV.'
    )]
    private ?File $cvFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cvFileName = null;

    #[Vich\UploadableField(mapping: 'profile_image', fileNameProperty: 'profilePictureName')]
    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        mimeTypesMessage: 'Please upload a valid image (JPEG, PNG, WEBP) for the profile picture.'
    )]
    private ?File $profilePictureFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profilePictureName = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(nullable: true)]
    private ?int $yearsExperience = null;

    /**
     * @var Collection<int, Social>
     */
    #[ORM\OneToMany(
        targetEntity: Social::class,
        mappedBy: 'aboutMe',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['sortOrder' => 'ASC', 'name' => 'ASC'])]
    private Collection $socialLinks;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
        $this->socialLinks = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCvFile(): ?File
    {
        return $this->cvFile;
    }

    public function setCvFile(?File $cvFile = null): static
    {
        $this->cvFile = $cvFile;

        if (null !== $cvFile) {
            $this->updatedAt = new \DateTime();
        }

        return $this;
    }

    public function getCvFileName(): ?string
    {
        return $this->cvFileName;
    }

    public function setCvFileName(?string $cvFileName): static
    {
        $this->cvFileName = $cvFileName;

        return $this;
    }

    public function getProfilePictureFile(): ?File
    {
        return $this->profilePictureFile;
    }

    public function setProfilePictureFile(?File $profilePictureFile = null): static
    {
        $this->profilePictureFile = $profilePictureFile;

        if (null !== $profilePictureFile) {
            $this->updatedAt = new \DateTime();
        }

        return $this;
    }

    public function getProfilePictureName(): ?string
    {
        return $this->profilePictureName;
    }

    public function setProfilePictureName(?string $profilePictureName): static
    {
        $this->profilePictureName = $profilePictureName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getYearsExperience(): ?int
    {
        return $this->yearsExperience;
    }

    public function setYearsExperience(?int $yearsExperience): static
    {
        $this->yearsExperience = $yearsExperience;

        return $this;
    }

    /**
     * @return Collection<int, Social>
     */
    public function getSocialLinks(): Collection
    {
        return $this->socialLinks;
    }

    public function addSocialLink(Social $socialLink): static
    {
        if (!$this->socialLinks->contains($socialLink)) {
            $this->socialLinks[] = $socialLink;
            $socialLink->setAboutMe($this);
        }

        return $this;
    }

    public function removeSocialLink(Social $socialLink): static
    {
        if ($this->socialLinks->removeElement($socialLink)) {
            // set the owning side to null (unless already changed)
            if ($socialLink->getAboutMe() === $this) {
                $socialLink->setAboutMe(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? 'About Me Default Title';
    }
}
