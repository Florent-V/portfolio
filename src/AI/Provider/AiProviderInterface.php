<?php

declare(strict_types=1);

namespace App\AI\Provider;

use App\AI\Enum\AiProvider;
use Symfony\AI\Agent\AgentInterface;

interface AiProviderInterface
{
    public function getProvider(): AiProvider;

    public function getAgent(): AgentInterface;

    public function getModelName(): string;
}
