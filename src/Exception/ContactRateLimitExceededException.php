<?php

declare(strict_types=1);

namespace App\Exception;

class ContactRateLimitExceededException extends \RuntimeException
{
    public function __construct(private readonly int $retryAfterSeconds)
    {
        parent::__construct('Contact form rate limit exceeded.');
    }

    public function getRetryAfterSeconds(): int
    {
        return max(0, $this->retryAfterSeconds);
    }
}
