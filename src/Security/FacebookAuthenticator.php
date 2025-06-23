<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Handler\OAuthUserHandler;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class FacebookAuthenticator extends AbstractOauthAuthenticator
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
        return 'facebook';
    }

    public function getUserFromToken(AccessToken $accessToken, OAuth2ClientInterface $client): User
    {
        /** @var FacebookUser $fetchUser */
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
