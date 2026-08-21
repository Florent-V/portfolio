<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

readonly class ContactFormHandler
{
    public function __construct(
        private MailerInterface $mailer,
        #[Autowire(param: 'app.admin_email')]
        private string $adminEmail,
    ) {
    }

    /**
     * @param array{name: string, email: string, subject: string, message: string} $formData
     */
    public function send(array $formData): bool
    {
        $email = (new TemplatedEmail())
            ->replyTo($formData['email'])
            ->to($this->adminEmail)
            ->subject('Nouveau message de contact Portfolio: ' . $formData['subject'])
            ->htmlTemplate('emails/contact_email.html.twig')
            ->context([
                'name'         => $formData['name'],
                'sender_email' => $formData['email'],
                'subject'      => $formData['subject'],
                'message'      => $formData['message'],
            ]);

        try {
            $this->mailer->send($email);

            return true;
        } catch (TransportExceptionInterface) {
            return false;
        }
    }
}
