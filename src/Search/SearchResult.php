<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Search;

use ArrayIterator;

use function array_filter;
use function count;
use function end;
use function is_array;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SearchResult implements SearchResultInterface
{
    /**
     * @var array<int, array>
     */
    private array $hits = [];

    private int $offset;

    private int $limit;

    private int $nbHits;

    private bool $exhaustiveNbHits = false;

    private int $processingTimeMs;

    private string $query;

    private ?bool $exhaustiveFacetsCount = null;

    /**
     * @var array<string, mixed>
     */
    private array $facetsDistribution = [];

    /**
     * @var mixed
     */
    private $lastIdentifier;

    public static function create(
        array $hits,
        int $offset,
        int $limit,
        int $nbHits,
        bool $exhaustiveNbHits,
        int $processingTimeMs,
        string $query,
        ?bool $exhaustiveFacetsCount = null,
        array $facetsDistribution = []
    ): SearchResultInterface {
        $self = new self();

        $self->hits = $hits;
        $self->offset = $offset;
        $self->limit = $limit;
        $self->nbHits = $nbHits;
        $self->exhaustiveNbHits = $exhaustiveNbHits;
        $self->processingTimeMs = $processingTimeMs;
        $self->query = $query;
        $self->exhaustiveFacetsCount = $exhaustiveFacetsCount;
        $self->facetsDistribution = $facetsDistribution;

        $self->findLastIdentifier();

        return $self;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $callback): SearchResultInterface
    {
        $results = array_filter($this->hits, $callback, ARRAY_FILTER_USE_BOTH);

        $this->hits = $results;
        $this->nbHits = count($results);

        return $this;
    }

    public function getHit(int $key, mixed $default = null): mixed
    {
        return $this->hits[$key] ?? $default;
    }

    /**
     * @return array<int, array>
     */
    public function getHits(): array
    {
        return $this->hits;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getNbHits(): int
    {
        return $this->nbHits;
    }

    public function getExhaustiveNbHits(): bool
    {
        return $this->exhaustiveNbHits;
    }

    public function getProcessingTimeMs(): int
    {
        return $this->processingTimeMs;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getExhaustiveFacetsCount(): ?bool
    {
        return $this->exhaustiveFacetsCount;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFacetsDistribution(): array
    {
        return $this->facetsDistribution;
    }

    /**
     * @return mixed
     */
    public function getLastIdentifier(): mixed
    {
        return $this->lastIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'hits' => $this->hits,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'nbHits' => $this->nbHits,
            'exhaustiveNbHits' => $this->exhaustiveNbHits,
            'processingTimeMs' => $this->processingTimeMs,
            'query' => $this->query,
            'exhaustiveFacetsCount' => $this->exhaustiveFacetsCount,
            'facetsDistribution' => $this->facetsDistribution,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->hits);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return $this->nbHits;
    }

    /**
     * {@internal This method used to determine the last identifier used if the hits are paginated}
     */
    private function findLastIdentifier(): void
    {
        if (empty($this->hits)) {
            return;
        }

        $lastHit = end($this->hits);
        if (!is_array($lastHit)) {
            return;
        }

        $this->lastIdentifier = $lastHit['id'] ?? null;
    }
}
