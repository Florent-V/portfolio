<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailerService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
    ) {
    }

    /**
     * Envoie un email générique avec template twig.
     *
     * @param array<string, mixed> $context
     */
    public function sendMail(string $to, string $subject, string $template, array $context = []): void
    {
        $htmlContent = $this->twig->render($template, $context);

        $email = (new Email())
            ->from('noreply@tondomaine.com')
            ->to($to)
            ->subject($subject)
            ->html($htmlContent);

        $this->mailer->send($email);
    }

    /**
     * Envoie un mail de confirmation d'inscription.
     */
    public function sendConfirmationEmail(User $user): void
    {
        $this->sendMail(
            $user->getEmail(),
            'Confirmation de votre adresse email',
            'registration/confirmation_email.html.twig',
            ['user' => $user]
        );
    }
}
