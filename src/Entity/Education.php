<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\BlameableEntity;
use App\Repository\EducationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Mapping\Annotation\SoftDeleteable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EducationRepository::class)]
#[Gedmo\Loggable]
#[SoftDeleteable]
class Education
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
    private ?string $degree = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $institution = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\LessThanOrEqual('today')]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\LessThanOrEqual('today')]
    #[Assert\GreaterThanOrEqual(propertyPath: 'startDate')]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDegree(): ?string
    {
        return $this->degree;
    }

    public function setDegree(string $degree): static
    {
        $this->degree = $degree;

        return $this;
    }

    public function getInstitution(): ?string
    {
        return $this->institution;
    }

    public function setInstitution(string $institution): static
    {
        $this->institution = $institution;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getStartDateDisplay(): string
    {
        if (null !== $this->getStartDate()) {
            return $this->getStartDate()->format('F Y');
        }

        return '';
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getEndDateDisplay(): string
    {
        if (null !== $this->getEndDate()) {
            return $this->getEndDate()->format('F Y');
        }

        return 'Present';
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

    public function __toString(): string
    {
        return ($this->degree && $this->institution)
            ? sprintf('%s at %s', $this->degree, $this->institution)
            : 'New Education Entry';
    }
}
