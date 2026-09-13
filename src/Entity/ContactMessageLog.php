<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ContactMessageLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * @SuppressWarnings("PHPMD.ExcessivePublicCount")
 */
#[ORM\Entity(repositoryClass: ContactMessageLogRepository::class)]
#[ORM\Table(name: 'contact_message_log')]
#[ORM\Index(columns: ['ip_address', 'created_at'])]
#[ORM\Index(columns: ['blocked'])]
class ContactMessageLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // @phpstan-ignore-next-line
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 255)]
    private string $email = '';

    #[ORM\Column(length: 255)]
    private string $subject = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $message = '';

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $referer = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $acceptLanguage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $forwardedFor = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $requestHeaders = null;

    #[ORM\Column]
    private bool $blocked = false;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $blockReason = null;

    #[ORM\Column]
    private bool $mailSent = false;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $geoLookupStatus = null;

    #[ORM\Column(length: 8, nullable: true)]
    private ?string $countryCode = null;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $region = null;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $isp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $org = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $asn = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getReferer(): ?string
    {
        return $this->referer;
    }

    public function setReferer(?string $referer): static
    {
        $this->referer = $referer;

        return $this;
    }

    public function getAcceptLanguage(): ?string
    {
        return $this->acceptLanguage;
    }

    public function setAcceptLanguage(?string $acceptLanguage): static
    {
        $this->acceptLanguage = $acceptLanguage;

        return $this;
    }

    public function getForwardedFor(): ?string
    {
        return $this->forwardedFor;
    }

    public function setForwardedFor(?string $forwardedFor): static
    {
        $this->forwardedFor = $forwardedFor;

        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRequestHeaders(): ?array
    {
        return $this->requestHeaders;
    }

    /**
     * @param array<string, mixed>|null $requestHeaders
     */
    public function setRequestHeaders(?array $requestHeaders): static
    {
        $this->requestHeaders = $requestHeaders;

        return $this;
    }

    public function isBlocked(): bool
    {
        return $this->blocked;
    }

    public function setBlocked(bool $blocked): static
    {
        $this->blocked = $blocked;

        return $this;
    }

    public function getBlockReason(): ?string
    {
        return $this->blockReason;
    }

    public function setBlockReason(?string $blockReason): static
    {
        $this->blockReason = $blockReason;

        return $this;
    }

    public function isMailSent(): bool
    {
        return $this->mailSent;
    }

    public function setMailSent(bool $mailSent): static
    {
        $this->mailSent = $mailSent;

        return $this;
    }

    public function getGeoLookupStatus(): ?string
    {
        return $this->geoLookupStatus;
    }

    public function setGeoLookupStatus(?string $geoLookupStatus): static
    {
        $this->geoLookupStatus = $geoLookupStatus;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): static
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getIsp(): ?string
    {
        return $this->isp;
    }

    public function setIsp(?string $isp): static
    {
        $this->isp = $isp;

        return $this;
    }

    public function getOrg(): ?string
    {
        return $this->org;
    }

    public function setOrg(?string $org): static
    {
        $this->org = $org;

        return $this;
    }

    public function getAsn(): ?string
    {
        return $this->asn;
    }

    public function setAsn(?string $asn): static
    {
        $this->asn = $asn;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
