<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ContactMessageLog;
use App\Exception\ContactRateLimitExceededException;
use App\Message\EnrichContactMessageLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;

readonly class ContactFormHandler
{
    private const HEADER_DENYLIST = ['cookie', 'authorization'];

    /** Délai minimum, en secondes, entre l'affichage du formulaire et sa soumission. */
    private const MIN_FILL_SECONDS = 3;

    public function __construct(
        private MailerInterface $mailer,
        #[Autowire(param: 'app.admin_email')]
        private string $adminEmail,
        #[Autowire(param: 'kernel.secret')]
        private string $appSecret,
        #[Autowire(service: 'limiter.contact_form_ip')]
        private RateLimiterFactory $ipLimiter,
        #[Autowire(service: 'limiter.contact_form_global')]
        private RateLimiterFactory $globalLimiter,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus,
    ) {
    }

    /**
     * Jeton signé à afficher dans le formulaire (champ caché "renderedAt").
     */
    public function generateTimestampToken(): string
    {
        $timestamp = (string) time();

        return $timestamp . '.' . $this->sign($timestamp);
    }

    /**
     * @param array{
     *     name: string,
     *     email: string,
     *     subject: string,
     *     message: string
     * } $formData
     */
    public function send(
        array $formData,
        Request $request,
        ?string $honeypot,
        ?string $timestampToken,
    ): bool {
        $botReason = $this->detectBotReason($honeypot, $timestampToken);

        if (null !== $botReason) {
            $this->logAttempt($formData, $request, blocked: true, blockReason: $botReason, mailSent: false);

            // Réponse identique à un envoi réussi : on ne renseigne pas le bot sur la détection.
            return true;
        }

        $ip = $request->getClientIp() ?? 'unknown';

        $ipLimit     = $this->ipLimiter->create($ip)->consume();
        $globalLimit = $this->globalLimiter->create('global')->consume();

        if (!$ipLimit->isAccepted() || !$globalLimit->isAccepted()) {
            $blockReason = !$ipLimit->isAccepted() ? 'ip_rate_limit' : 'global_rate_limit';
            $retryAfter  = max(
                $ipLimit->getRetryAfter()->getTimestamp(),
                $globalLimit->getRetryAfter()->getTimestamp()
            ) - time();

            $this->logAttempt($formData, $request, blocked: true, blockReason: $blockReason, mailSent: false);

            throw new ContactRateLimitExceededException($retryAfter);
        }

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
            $this->logAttempt($formData, $request, blocked: false, blockReason: null, mailSent: true);

            return true;
        } catch (TransportExceptionInterface) {
            $this->logAttempt($formData, $request, blocked: false, blockReason: null, mailSent: false);

            return false;
        }
    }

    /**
     * @return non-empty-string|null la raison du blocage, ou null si la soumission semble humaine
     */
    private function detectBotReason(?string $honeypot, ?string $timestampToken): ?string
    {
        if ($this->isHoneypotFilled($honeypot)) {
            return 'honeypot';
        }

        return $this->timestampTokenIssue($timestampToken);
    }

    private function isHoneypotFilled(?string $honeypot): bool
    {
        return null !== $honeypot && '' !== $honeypot;
    }

    /**
     * @return non-empty-string|null
     */
    private function timestampTokenIssue(?string $timestampToken): ?string
    {
        if (null === $timestampToken || !str_contains($timestampToken, '.')) {
            return 'missing_timing_token';
        }

        [$timestamp, $signature] = explode('.', $timestampToken, 2);

        if (!hash_equals($this->sign($timestamp), $signature)) {
            return 'invalid_timing_token';
        }

        if (time() - (int) $timestamp < self::MIN_FILL_SECONDS) {
            return 'submitted_too_fast';
        }

        return null;
    }

    private function sign(string $value): string
    {
        return hash_hmac('sha256', $value, $this->appSecret);
    }

    /**
     * @param array{
     *     name: string,
     *     email: string,
     *     subject: string,
     *     message: string
     * } $formData
     */
    private function logAttempt(
        array $formData,
        Request $request,
        bool $blocked,
        ?string $blockReason,
        bool $mailSent,
    ): void {
        $log = (new ContactMessageLog())
            ->setName($formData['name'])
            ->setEmail($formData['email'])
            ->setSubject($formData['subject'])
            ->setMessage($formData['message'])
            ->setIpAddress($request->getClientIp())
            ->setUserAgent($request->headers->get('User-Agent'))
            ->setReferer($request->headers->get('Referer'))
            ->setAcceptLanguage($request->headers->get('Accept-Language'))
            ->setForwardedFor($request->headers->get('X-Forwarded-For'))
            ->setRequestHeaders($this->filteredHeaders($request))
            ->setBlocked($blocked)
            ->setBlockReason($blockReason)
            ->setMailSent($mailSent);

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        $this->messageBus->dispatch(new EnrichContactMessageLog((int) $log->getId()));
    }

    /**
     * @return array<string, string>
     */
    private function filteredHeaders(
        Request $request,
    ): array {
        $headers = [];

        foreach ($request->headers->all() as $name => $values) {
            if (\in_array(strtolower($name), self::HEADER_DENYLIST, true)) {
                continue;
            }

            $headers[$name] = implode(', ', $values);
        }

        return $headers;
    }
}
