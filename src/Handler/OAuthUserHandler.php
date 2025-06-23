<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\OAuthAccount;
use App\Entity\User;
use App\Repository\OAuthAccountRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class OAuthUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private OAuthAccountRepository $oauthAccountRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function getUser(
        string $provider,
        string $providerId,
        string $email,
        string $username,
        ?string $firstName = null,
        ?string $lastName = null,
    ): User {
        // 1. Check if an OAuth account already exists
        $oauthAccount = $this->oauthAccountRepository->findOneBy([
            'provider'   => $provider,
            'providerId' => $providerId,
        ]);

        if ($oauthAccount) {
            return $oauthAccount->getUser();
        }

        // 2. Try to find a user by email
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setUserName($username);
            $user->setFirstName($firstName ?? '');
            $user->setLastName($lastName ?? '');
            $user->setPassword('');
            $user->setRoles(['ROLE_USER']);
            $user->setIsVerified(true);
            $this->em->persist($user);
        }

        // 3. Create new OAuth account
        $account = new OAuthAccount();
        $account->setProvider($provider);
        $account->setProviderId($providerId);
        $account->setUser($user);

        $this->em->persist($account);
        $this->em->flush();

        return $user;
    }
}
