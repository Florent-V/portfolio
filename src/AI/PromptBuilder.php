<?php

declare(strict_types=1);

namespace App\AI;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final readonly class PromptBuilder
{
    private Environment $twig;

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        string $projectDir,
    ) {
        $this->twig = new Environment(
            new FilesystemLoader($projectDir . '/resources/ai/prompts'),
            [
                'autoescape'       => false,
                'strict_variables' => true,
                'cache'            => false,
            ],
        );
    }

    /**
     * @param array<string, string> $variables
     */
    public function build(string $templateName, array $variables = []): string
    {
        return $this->twig->render($templateName . '.twig', $variables);
    }
}
