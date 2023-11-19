<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Cache;

use MeiliSearchBundle\Exception\RuntimeException;
use MeiliSearchBundle\Search\SearchResultInterface;
use Psr\Cache\InvalidArgumentException;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface SearchResultCacheOrchestratorInterface
{
    /**
     * @throws InvalidArgumentException
     * @param SearchResultInterface<SearchResultInterface> $searchResult
     */
    public function add(string $searchResultIdentifier, SearchResultInterface $searchResult): void;

    /**
     *
     * @throws InvalidArgumentException
     * @return SearchResultInterface<string, mixed>
     */
    public function get(string $searchResultIdentifier): SearchResultInterface;

    /**
     * @throws RuntimeException If the cache pool cannot be cleared
     */
    public function clear(): void;
}
