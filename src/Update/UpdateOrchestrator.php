<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Update;

use MeiliSearch\Client;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;
use function array_walk;
use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class UpdateOrchestrator implements UpdateOrchestratorInterface
{
    private const INDEX_LOG_KEY = 'index';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Client $client,
        ?LoggerInterface $logger = null
    ) {
        $this->client = $client;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdate(string $uid, int $updateId): UpdateInterface
    {
        try {
            $index = $this->client->getIndex($uid);
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf(
                'An error occurred when trying to fetch the index, error "%s"',
                $throwable->getMessage()
            ));

            throw $throwable;
        }

        $update = $index->getUpdateStatus($updateId);

        $this->logger->info('An update has been retrieved', [
            self::INDEX_LOG_KEY => $uid,
        ]);

        return Update::create(
            $update['status'],
            $update['updateId'],
            $update['type'],
            $update['duration'],
            $update['enqueuedAt'],
            $update['processedAt']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdates(string $uid): array
    {
        try {
            $index = $this->client->getIndex($uid);
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf(
                'An error occurred when trying to fetch the index, error "%s"',
                $throwable->getMessage()
            ));

            throw $throwable;
        }

        $updates = $index->getAllUpdateStatus();

        $values = [];
        array_walk($updates, function (array $update) use (&$values): void {
            $values[] = Update::create(
                $update['status'],
                $update['updateId'],
                $update['type'],
                $update['duration'],
                $update['enqueuedAt'],
                $update['processedAt']
            );
        });

        $this->logger->info('A set of updates has been retrieved', [
            self::INDEX_LOG_KEY => $uid,
        ]);

        return $values;
    }
}
