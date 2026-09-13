<?php

declare(strict_types=1);

namespace App\Message;

readonly class EnrichContactMessageLog
{
    public function __construct(private int $contactMessageLogId)
    {
    }

    public function getContactMessageLogId(): int
    {
        return $this->contactMessageLogId;
    }
}
