<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User as AppUser;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class UserChecker implements UserCheckerInterface
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        // Rien à faire ici
    }

    public function checkPostAuth(
        UserInterface $user,
        ?TokenInterface $token = null,
    ): void {
        if (!$user instanceof AppUser) {
            return;
        }

        if (!$user->isVerified()) {
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->to($user->getEmail())
                    ->subject('Portfolio : Confirmez votre Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            throw new CustomUserMessageAccountStatusException(
                message: 'Votre compte <strong>n\'est pas activé</strong>,' .
                'un mail vient de vous être envoyé pour procéder à l\'activation ',
                code: 2000
            );
        }
    }
}
