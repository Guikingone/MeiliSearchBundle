<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface SearchInterface
{
    public static function create(array $hits, int $offset, int $limit, int $nbHits, int $exhaustiveNbHits, int $processingTimeMs, string $query): SearchInterface;

    /**
     * Return a new `SearchInterface` instance with the hits filtered using `array_filter($this->hits, $callback, ARRAY_FILTER_USE_BOTH)`.
     *
     * The method DOES not trigger a new request.
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function filter(callable $callback): SearchInterface;

    public function getHits(): array;

    public function getOffset(): int;

    public function getLimit(): int;

    public function getNbHits(): int;

    public function getExhaustiveNbHits(): int;

    public function getProcessingTimeMs(): int;

    public function getQuery(): string;
}
