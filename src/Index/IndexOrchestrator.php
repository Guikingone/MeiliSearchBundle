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
    private const DISPLAYED_ATTRIBUTES = 'displayedAttributes';
    private const RANKING_RULES_ATTRIBUTES = 'rankingRulesAttributes';
    private const SEARCHABLE_ATTRIBUTES = 'searchableAttributes';
    private const STOP_WORDS_ATTRIBUTES = 'stopWords';
    private const SYNONYMS_ATTRIBUTES = 'synonyms';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var EventDispatcherInterface|null
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Client $client,
        ?EventDispatcherInterface $eventDispatcher = null,
        ?LoggerInterface $logger = null
    ) {
        $this->client = $client;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger ?: new NullLogger();
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

            $this->handleConfiguration($index, $configuration);
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('The index cannot be created, error: "%s"', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }

        $this->dispatch(new IndexCreatedEvent([
            self::UID => $uid,
            self::PRIMARY_KEY => $primaryKey,
        ], $index));
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
    public function update(string $uid, array $configuration = []): void
    {
        try {
            $index = $this->getIndex($uid);

            if (null === $index->getPrimaryKey()) {
                $index->update(['primaryKey' => $configuration['primaryKey']]);
            }

            $this->handleConfiguration($index, $configuration);
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('The index cannot be created, error: "%s"', $throwable->getMessage()), [
                'trace' => $throwable->getTrace(),
            ]);

            throw new RuntimeException($throwable->getMessage(), 0, $throwable);
        }
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

    /**
     * {@inheritdoc}
     */
    public function removeIndexes(): void
    {
        try {
            $this->client->deleteAllIndexes();
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('The indexes cannot be deleted, error: "%s".', $exception->getMessage()));

            throw new RuntimeException($exception->getMessage(), 0, $exception);
        }

        $this->logger->info('The indexes have been deleted');
    }

    private function handleConfiguration(Indexes $index, array $configuration): void
    {
        if (empty($configuration)) {
            return;
        }

        if (array_key_exists(self::DISPLAYED_ATTRIBUTES, $configuration) && [] !== $configuration[self::DISPLAYED_ATTRIBUTES]) {
            $index->updateDisplayedAttributes($configuration[self::DISPLAYED_ATTRIBUTES]);
        }

        if (array_key_exists(self::DISTINCT_ATTRIBUTE, $configuration) && null !== $configuration[self::DISTINCT_ATTRIBUTE]) {
            $index->updateDistinctAttribute($configuration[self::DISTINCT_ATTRIBUTE]);
        }

        if (array_key_exists(self::FACETED_ATTRIBUTES, $configuration) && [] !== $configuration[self::FACETED_ATTRIBUTES]) {
            $index->updateAttributesForFaceting($configuration[self::FACETED_ATTRIBUTES]);
        }

        if (array_key_exists(self::RANKING_RULES_ATTRIBUTES, $configuration)) {
            $index->updateRankingRules($configuration[self::RANKING_RULES_ATTRIBUTES]);
        }

        if (array_key_exists(self::SEARCHABLE_ATTRIBUTES, $configuration) && [] !== $configuration[self::SEARCHABLE_ATTRIBUTES]) {
            $index->updateSearchableAttributes($configuration[self::SEARCHABLE_ATTRIBUTES]);
        }

        if (array_key_exists(self::STOP_WORDS_ATTRIBUTES, $configuration) && [] !== $configuration[self::STOP_WORDS_ATTRIBUTES]) {
            $index->updateStopWords($configuration[self::STOP_WORDS_ATTRIBUTES]);
        }

        if (array_key_exists(self::SYNONYMS_ATTRIBUTES, $configuration) && [] !== $configuration[self::SYNONYMS_ATTRIBUTES]) {
            $index->updateSynonyms($configuration[self::SYNONYMS_ATTRIBUTES]);
        }
    }

    private function dispatch(Event $event): void
    {
        if (null === $this->eventDispatcher) {
            return;
        }

        $this->eventDispatcher->dispatch($event);
    }
}
