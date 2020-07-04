<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Index;

use MeiliSearch\Client;
use MeiliSearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Index\IndexCreatedEvent;
use MeiliSearchBundle\Event\Index\IndexRetrievedEvent;
use MeiliSearchBundle\Exception\RuntimeException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;
use function array_key_exists;
use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexOrchestrator implements IndexOrchestratorInterface
{
    private const PRIMARY_KEY = 'primaryKey';
    private const UID = 'uid';
    private const DISTINCT_ATTRIBUTE = 'distinctAttribute';
    private const FACETED_ATTRIBUTES = 'facetedAttributes';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var EventDispatcherInterface|null
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    public function __construct(
        Client $client,
        ?EventDispatcherInterface $eventDispatcher = null,
        ?LoggerInterface $logger = null
    ) {
        $this->client = $client;
        $this->logger = $logger ?: new NullLogger();
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function addIndex(string $uid, ?string $primaryKey = null, array $configuration = []): void
    {
        try {
            $index = $this->client->createIndex($uid, [
                self::PRIMARY_KEY => $primaryKey,
            ]);

            if (!empty($configuration)) {
                if (array_key_exists(self::DISTINCT_ATTRIBUTE, $configuration)) {
                    $index->updateDistinctAttribute($configuration[self::DISTINCT_ATTRIBUTE]);
                }

                if (array_key_exists(self::FACETED_ATTRIBUTES, $configuration)) {
                    $index->updateAttributesForFaceting($configuration[self::FACETED_ATTRIBUTES]);
                }

                if (array_key_exists('searchableAttributes', $configuration)) {
                    $index->updateSearchableAttributes($configuration['searchableAttributes']);
                }

                if (array_key_exists('displayedAttributes', $configuration)) {
                    $index->updateDisplayedAttributes($configuration['displayedAttributes']);
                }
            }
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('The index cannot be created, error: "%s"', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }

        $this->dispatch(new IndexCreatedEvent([
            self::UID => $uid,
            self::PRIMARY_KEY => $primaryKey,
        ], $index));

        $this->logger->info('An index has been created.', [
            'configuration' => [
                self::UID => $uid,
                self::PRIMARY_KEY => $primaryKey,
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexes(): array
    {
        try {
            return $this->client->getAllIndexes();
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('The indexes cannot be retrieved, error: "%s".', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIndex(string $uid): Indexes
    {
        try {
            $index = $this->client->getIndex($uid);

            $this->dispatch(new IndexRetrievedEvent($index));
            $this->logger->info('An index has been retrieved', [
                self::UID => $uid,
            ]);

            return $index;
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('The index cannot be retrieved, error: "%s".', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeIndexes(): void
    {
        try {
            $this->client->deleteAllIndexes();
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('The indexes cannot be deleted, error: "%s".', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }

        $this->logger->info('The indexes have been deleted');
    }

    /**
     * {@inheritdoc}
     */
    public function removeIndex(string $uid): void
    {
        try {
            $this->client->deleteIndex($uid);
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('The index cannot be deleted, error: "%s".', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }

        $this->logger->info('An index has been deleted', [
            self::UID => $uid,
        ]);
    }

    private function dispatch(Event $event): void
    {
        if (null === $this->eventDispatcher) {
            return;
        }

        $this->eventDispatcher->dispatch($event);
    }
}
