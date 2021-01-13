<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Cache;

use MeiliSearchBundle\Exception\RuntimeException;
use MeiliSearchBundle\Search\SearchResultInterface;
use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface SearchResultCacheOrchestratorInterface
{
    /**
     * @param string                                       $searchResultIdentifier
     * @param SearchResultInterface<SearchResultInterface> $searchResult
     *
     * @throws Throwable
     */
    public function add(string $searchResultIdentifier, SearchResultInterface $searchResult): void;

    /**
     * @param string $searchResultIdentifier
     *
     * @return SearchResultInterface<string, mixed>
     *
     * @throws Throwable
     */
    public function get(string $searchResultIdentifier): SearchResultInterface;

    /**
     * @throws RuntimeException If the cache pool cannot be cleared
     */
    public function clear(): void;
}
