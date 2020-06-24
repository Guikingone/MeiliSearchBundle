<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class Search
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

    public static function create(
        array $hits,
        int $offset,
        int $limit,
        int $nbHits,
        int $exhaustiveNbHits,
        int $processingTimeMs,
        string $query
    ): self {
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
     * @return array<int,array>
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

    public function getExhaustiveNbHits(): int
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
}