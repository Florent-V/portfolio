<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\OAuthAccountUsedException;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

#[AsEventListener]
readonly class CheckPassportListener
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function __invoke(CheckPassportEvent $event): void
    {
        // ✅ On vérifie que l’authenticator utilisé est celui du formulaire
        $authenticator = $event->getAuthenticator();
        if (!str_contains($authenticator::class, 'FormLoginAuthenticator')) {
            return;
        }

        $passport = $event->getPassport();
        /** @var UserBadge $userBadge */
        $userBadge      = $passport->getBadge(UserBadge::class);
        $userIdentifier = $userBadge->getUserIdentifier();

        $user = $this->userRepository->findOneBy(['email' => $userIdentifier]);

        if ($user && $user->getOAuthAccounts()->count()) {
            throw new OAuthAccountUsedException(
                $user->getOAuthAccounts()->first()->getProvider(),
            );
        }
    }
}
