<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Handler\OAuthUserHandler;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class GoogleAuthenticator extends AbstractOauthAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        ClientRegistry $clientRegistry,
        RouterInterface $router,
        private readonly OAuthUserHandler $oauthUserHandler,
    ) {
        parent::__construct($clientRegistry, $router);
    }

    public function getProviderName(): string
    {
        return 'google';
    }

    public function getUserFromToken(AccessToken $accessToken, OAuth2ClientInterface $client): User
    {
        /** @var GoogleUser $fetchUser */
        $fetchUser = $client->fetchUserFromToken($accessToken);

        return $this->oauthUserHandler->getUser(
            provider: $this->getProviderName(),
            providerId: $fetchUser->getId(),
            email: $fetchUser->getEmail(),
            username: $fetchUser->getName(),
            firstName: $fetchUser->getFirstName(),
            lastName: $fetchUser->getLastName()
        );
    }
}
