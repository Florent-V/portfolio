<?php

declare(strict_types=1);

namespace App\AI;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class PromptBuilder
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
    }

    /**
     * @param array<string, string> $variables
     */
    public function build(
        string $templateName,
        array $variables = [],
    ): string {
        $path = $this->projectDir . '/resources/ai/prompts/' . $templateName . '.md';

        if (!is_file($path)) {
            throw new \RuntimeException(
                \sprintf(
                    'AI prompt template "%s" not found at: %s',
                    $templateName,
                    $path
                )
            );
        }

        $content = (string) file_get_contents($path);

        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . strtoupper($key) . '}}', $value, $content);
        }

        return $content;
    }
}
