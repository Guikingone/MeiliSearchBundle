<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Cache;

use MeiliSearchBundle\Exception\InvalidArgumentException as InternalInvalidArgumentException;
use MeiliSearchBundle\Exception\RuntimeException;
use MeiliSearchBundle\Search\SearchResult;
use MeiliSearchBundle\Search\SearchResultInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

use function is_array;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SearchResultCacheOrchestrator implements SearchResultCacheOrchestratorInterface
{
    private const RESULT_IDENTIFIER_CACHE_KEY = 'identifier';

    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly CacheItemPoolInterface $cacheItemPool,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $searchResultIdentifier, SearchResultInterface $searchResult): void
    {
        if ($this->has($searchResultIdentifier)) {
            throw new InternalInvalidArgumentException('This search result already exist');
        }

        $item = $this->cacheItemPool->getItem($searchResultIdentifier);
        $item->set($searchResult->toArray());

        $this->cacheItemPool->save($item);

        $this->logger->info('A search result has been saved', [
            self::RESULT_IDENTIFIER_CACHE_KEY => $searchResultIdentifier,
            'result' => $searchResult->toArray(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $searchResultIdentifier): SearchResultInterface
    {
        if (!$this->has($searchResultIdentifier)) {
            throw new InternalInvalidArgumentException('The desired search result cannot be found');
        }

        $item = $this->cacheItemPool->getItem($searchResultIdentifier);

        $this->logger->info('A search result has been retrieved', [
            self::RESULT_IDENTIFIER_CACHE_KEY => $searchResultIdentifier,
        ]);

        $values = $item->get();
        if (!is_array($values)) {
            throw new InternalInvalidArgumentException('The desired search result does not contain valid data');
        }

        return SearchResult::create(
            $values['hits'],
            $values['offset'],
            $values['limit'],
            $values['nbHits'],
            $values['exhaustiveNbHits'],
            $values['processingTimeMs'],
            $values['query'],
            $values['exhaustiveFacetsCount'],
            $values['facetsDistribution']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        if (!$this->cacheItemPool->clear()) {
            throw new RuntimeException('The cache pool cannot be cleared');
        }
    }

    /**
     * @throws Throwable|InvalidArgumentException
     */
    private function has(string $searchResultIdentifier): bool
    {
        return $this->cacheItemPool->hasItem($searchResultIdentifier);
    }
}
