<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class OAuthAccountUsedException extends CustomUserMessageAuthenticationException
{
    private string $provider;

    public function __construct(string $provider)
    {
        $this->provider = $provider;
        // Le message d'erreur affiché pour l'utilisateur
        parent::__construct(
            sprintf('Ce compte est lié à %s Connect. Veuillez vous connecter avec ce service.', ucfirst($provider)),
            [
                'provider' => $provider,
            ]
        );
    }

    public function getProvider(): string
    {
        return $this->provider;
    }
}
