<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class Search implements SearchInterface
{
    /**
     * @var array<int,array>
     */
    private $hits;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $nbHits;

    /**
     * @var int
     */
    private $exhaustiveNbHits;


    /**
     * @var int
     */
    private $processingTimeMs;

    /**
     * @var string
     */
    private $query;

    /**
     * {@inheritdoc}
     */
    public static function create(
        array $hits,
        int $offset,
        int $limit,
        int $nbHits,
        int $exhaustiveNbHits,
        int $processingTimeMs,
        string $query
    ): SearchInterface {
        $self = new self();

        $self->hits = $hits;
        $self->offset = $offset;
        $self->limit = $limit;
        $self->nbHits = $nbHits;
        $self->exhaustiveNbHits = $exhaustiveNbHits;
        $self->processingTimeMs = $processingTimeMs;
        $self->query = $query;

        return $self;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $callback): SearchInterface
    {
        $results = array_filter($this->hits, $callback, ARRAY_FILTER_USE_BOTH);

        return self::create($results, $this->offset, $this->limit, $this->nbHits, $this->exhaustiveNbHits, $this->processingTimeMs, $this->query);
    }

    /**
     * {@inheritdoc}
     */
    public function getHits(): array
    {
        return $this->hits;
    }

    /**
     * {@inheritdoc}
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * {@inheritdoc}
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbHits(): int
    {
        return $this->nbHits;
    }

    /**
     * {@inheritdoc}
     */
    public function getExhaustiveNbHits(): int
    {
        return $this->exhaustiveNbHits;
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessingTimeMs(): int
    {
        return $this->processingTimeMs;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(): string
    {
        return $this->query;
    }
}
