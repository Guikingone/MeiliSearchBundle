<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Index;

use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;
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

    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly Client $client,
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function addIndex(string $uid, ?string $primaryKey = null, array $configuration = []): void
    {
        try {
            $this->client->createIndex($uid, [
                self::PRIMARY_KEY => $primaryKey,
            ]);

            $index = $this->getIndex($uid);

            $this->handleConfiguration($index, $configuration);
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('The index cannot be created, error: "%s"', $throwable->getMessage()));

            throw new RuntimeException($throwable->getMessage(), 0, $throwable);
        }

        $this->dispatch(
            new IndexCreatedEvent([
                self::UID => $uid,
                self::PRIMARY_KEY => $primaryKey,
            ], $index)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexes(): IndexListInterface
    {
        try {
            return new IndexList($this->client->getIndexes()->getResults());
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('The indexes cannot be retrieved, error: "%s".', $throwable->getMessage()));

            throw new RuntimeException($throwable->getMessage(), 0, $throwable);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIndex(string $uid): Indexes
    {
        try {
            $index = $this->client->index($uid);

            $this->dispatch(new IndexRetrievedEvent($index));
            $this->logger->info('An index has been retrieved', [
                self::UID => $uid,
            ]);

            return $index;
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('The index cannot be retrieved, error: "%s".', $throwable->getMessage()));
            throw new RuntimeException($throwable->getMessage());
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
            $this->logger->error(sprintf('The index cannot be updated, error: "%s"', $throwable->getMessage()));

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
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('The index cannot be deleted, error: "%s".', $throwable->getMessage()));
            throw new RuntimeException($throwable->getMessage(), 0, $throwable);
        }

        $this->logger->info('An index has been deleted', [
            self::UID => $uid,
        ]);
    }


    private function handleConfiguration(Indexes $index, array $configuration): void
    {
        if (empty($configuration)) {
            return;
        }

        if (array_key_exists(
            self::DISPLAYED_ATTRIBUTES,
            $configuration
        ) && [] !== $configuration[self::DISPLAYED_ATTRIBUTES]) {
            $index->updateDisplayedAttributes($configuration[self::DISPLAYED_ATTRIBUTES]);
        }

        if (array_key_exists(
            self::DISTINCT_ATTRIBUTE,
            $configuration
        ) && null !== $configuration[self::DISTINCT_ATTRIBUTE]) {
            $index->updateDistinctAttribute($configuration[self::DISTINCT_ATTRIBUTE]);
        }

        if (array_key_exists(
            self::FACETED_ATTRIBUTES,
            $configuration
        ) && [] !== $configuration[self::FACETED_ATTRIBUTES]) {
            $index->updateFaceting($configuration[self::FACETED_ATTRIBUTES]);
        }

        if (array_key_exists(self::RANKING_RULES_ATTRIBUTES, $configuration)) {
            $index->updateRankingRules($configuration[self::RANKING_RULES_ATTRIBUTES]);
        }

        if (array_key_exists(
            self::SEARCHABLE_ATTRIBUTES,
            $configuration
        ) && [] !== $configuration[self::SEARCHABLE_ATTRIBUTES]) {
            $index->updateSearchableAttributes($configuration[self::SEARCHABLE_ATTRIBUTES]);
        }

        if (array_key_exists(
            self::STOP_WORDS_ATTRIBUTES,
            $configuration
        ) && [] !== $configuration[self::STOP_WORDS_ATTRIBUTES]) {
            $index->updateStopWords($configuration[self::STOP_WORDS_ATTRIBUTES]);
        }
        if (!array_key_exists(self::SYNONYMS_ATTRIBUTES, $configuration)) {
            return;
        }
        if ([] === $configuration[self::SYNONYMS_ATTRIBUTES]) {
            return;
        }
        $index->updateSynonyms($configuration[self::SYNONYMS_ATTRIBUTES]);
    }

    private function dispatch(Event $event): void
    {
        if (null === $this->eventDispatcher) {
            return;
        }

        $this->eventDispatcher->dispatch($event);
    }
}
