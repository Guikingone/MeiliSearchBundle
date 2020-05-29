<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

use MeiliSearch\Client;
use MeiliSearchBundle\Exception\RuntimeException;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexOrchestrator
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    public function __construct(Client $client, ?LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function getIndexes(): array
    {
        try {
            return $this->client->getAllIndexes();
        } catch (Throwable $exception) {
            $this->logError(sprintf('The indexes cannot be retrieved, error: "%s"', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }
    }

    public function logError(string $message, array $context = []): void
    {
        if (null === $this->logger) {
            return;
        }

        $this->logger->error($message, $context);
    }

    public function logInfo(string $message, array $context = []): void
    {
        if (null === $this->logger) {
            return;
        }

        $this->logger->info($message, $context);
    }
}
