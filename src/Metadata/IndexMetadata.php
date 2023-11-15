<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Metadata;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexMetadata implements IndexMetadataInterface
{
    public function __construct(
        private readonly string $uid,
        private readonly bool $async = false,
        private readonly ?string $primaryKey = null,
        /**
         * @var array<int, string>
         */
        private readonly array $rankingRules = [],
        /**
         * @var array<int, string>
         */
        private readonly array $stopWords = [],
        private readonly ?string $distinctAttribute = null,
        /**
         * @var array<int, string>
         */
        private readonly array $facetedAttributes = [],
        /**
         * @var array<int, string>
         */
        private readonly array $searchableAttributes = [],
        /**
         * @var array<int, string>
         */
        private readonly array $displayedAttributes = [],
        /**
         * @var array<string, array>
         */
        private readonly array $synonyms = []
    ) {
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function isAsync(): bool
    {
        return $this->async;
    }

    public function getPrimaryKey(): ?string
    {
        return $this->primaryKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getRankingRules(): array
    {
        return $this->rankingRules;
    }

    /**
     * {@inheritdoc}
     */
    public function getStopWords(): array
    {
        return $this->stopWords;
    }

    public function getDistinctAttribute(): ?string
    {
        return $this->distinctAttribute;
    }

    public function getFacetedAttributes(): array
    {
        return $this->facetedAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableAttributes(): array
    {
        return $this->searchableAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayedAttributes(): array
    {
        return $this->displayedAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getSynonyms(): array
    {
        return $this->synonyms;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'primaryKey' => $this->primaryKey,
            'rankingRules' => $this->rankingRules,
            'stopWords' => $this->stopWords,
            'distinctAttribute' => $this->distinctAttribute,
            'facetedAttributes' => $this->facetedAttributes,
            'searchableAttributes' => $this->searchableAttributes,
            'displayedAttributes' => $this->displayedAttributes,
            'synonyms' => $this->synonyms,
        ];
    }
}
