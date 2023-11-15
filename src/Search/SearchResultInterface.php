<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Search;

use Countable;
use IteratorAggregate;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface SearchResultInterface extends Countable, IteratorAggregate
{
    public static function create(
        array $hits,
        int $offset,
        int $limit,
        int $nbHits,
        bool $exhaustiveNbHits,
        int $processingTimeMs,
        string $query
    ): SearchResultInterface;

    /**
     * Return a new {@see SearchResultInterface} instance with the hits filtered using `array_filter($this->hits, $callback, ARRAY_FILTER_USE_BOTH)`.
     *
     * The method DOES not trigger a new search.
     *
     *
     */
    public function filter(callable $callback): SearchResultInterface;

    public function getHit(int $key, mixed $default = null): mixed;

    public function getHits(): array;

    public function getOffset(): int;

    public function getLimit(): int;

    public function getNbHits(): int;

    public function getExhaustiveNbHits(): bool;

    public function getProcessingTimeMs(): int;

    public function getQuery(): string;

    public function getExhaustiveFacetsCount(): ?bool;

    public function getFacetsDistribution(): array;

    /**
     * @return mixed Can be either a string, integer or null.
     */
    public function getLastIdentifier();

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
