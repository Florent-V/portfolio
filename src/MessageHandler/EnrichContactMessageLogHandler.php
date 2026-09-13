<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\ContactMessageLog;
use App\Message\EnrichContactMessageLog;
use App\Repository\ContactMessageLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
readonly class EnrichContactMessageLogHandler
{
    private const string GEO_LOOKUP_URL = 'http://ip-api.com/json/%s'
        . '?fields=status,message,countryCode,country,regionName,city,isp,org,as,query';

    public function __construct(
        private ContactMessageLogRepository $repository,
        private EntityManagerInterface $entityManager,
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(
        EnrichContactMessageLog $message,
    ): void {
        $log = $this->repository->find($message->getContactMessageLogId());

        if (!$log instanceof ContactMessageLog) {
            return;
        }

        $ip = $this->resolvePublicIp($log);

        if (null === $ip) {
            $log->setGeoLookupStatus('skipped_private_ip');
            $this->entityManager->flush();

            return;
        }

        try {
            $response = $this->httpClient->request(
                'GET',
                sprintf(self::GEO_LOOKUP_URL, $ip),
                ['timeout' => 3, 'max_duration' => 3]
            );
            $data = $response->toArray(false);
        } catch (\Throwable $e) {
            $this->logger->warning('Contact log IP enrichment failed', [
                'ip'    => $ip,
                'error' => $e->getMessage(),
            ]);
            $log->setGeoLookupStatus('error');
            $this->entityManager->flush();

            return;
        }

        if ('success' !== ($data['status'] ?? null)) {
            $log->setGeoLookupStatus('fail');
            $this->entityManager->flush();

            return;
        }

        $log->setGeoLookupStatus('success')
            ->setCountryCode($data['countryCode'] ?? null)
            ->setCountry($data['country'] ?? null)
            ->setRegion($data['regionName'] ?? null)
            ->setCity($data['city'] ?? null)
            ->setIsp($data['isp'] ?? null)
            ->setOrg($data['org'] ?? null)
            ->setAsn($data['as'] ?? null);

        $this->entityManager->flush();
    }

    private function resolvePublicIp(ContactMessageLog $log): ?string
    {
        $ip = $log->getIpAddress();

        if (null === $ip || '' === $ip || !$this->isPublicIp($ip)) {
            return null;
        }

        return $ip;
    }

    private function isPublicIp(string $ip): bool
    {
        return false !== filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}
