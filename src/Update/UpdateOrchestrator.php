<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Update;

use Meilisearch\Client;
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

    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly Client $client,
        ?LoggerInterface $logger = null
    ) {
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
            $this->logger->error(
                sprintf(
                    'An error occurred when trying to fetch the index, error "%s"',
                    $throwable->getMessage()
                )
            );

            throw $throwable;
        }

        $update = $index->getTask($updateId);

        $this->logger->info('An update has been retrieved', [
            self::INDEX_LOG_KEY => $uid,
        ]);

        return Update::create(
            $update['uid'],
            $update['indexUid'],
            $update['status'],
            $update['type'],
            $update['canceledBy'],
            $update['details'],
            $update['error'],
            $update['duration'],
            $update['enqueuedAt'],
            $update['startedAt'],
            $update['finishedAt']
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
            $this->logger->error(
                sprintf(
                    'An error occurred when trying to fetch the index, error "%s"',
                    $throwable->getMessage()
                )
            );

            throw $throwable;
        }

        $updates = $index->getTasks()->getResults();

        $values = [];
        array_walk($updates, static function (array $update) use (&$values): void {
            $values[] = Update::create(
                $update['uid'],
                $update['indexUid'],
                $update['status'],
                $update['type'],
                $update['canceledBy'],
                $update['details'],
                $update['error'],
                $update['duration'],
                $update['enqueuedAt'],
                $update['startedAt'],
                $update['finishedAt']
            );
        });

        $this->logger->info('A set of updates has been retrieved', [
            self::INDEX_LOG_KEY => $uid,
        ]);

        return $values;
    }
}
