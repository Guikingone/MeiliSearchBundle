<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Settings;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class Settings
{
    /**
     * @var array<int, string>
     */
    private $rankingRules = [];

    /**
     * @var array<int, string>
     */
    private $attributesForFaceting = [];

    private ?string $distinctAttribute = null;

    /**
     * @var array<int, string>
     */
    private $searchableAttributes = [];

    /**
     * @var array<int, string>
     */
    private $displayedAttributes = [];

    /**
     * @var array<int, string>
     */
    private $stopWords = [];

    /**
     * @var array<string, array<int, string>>|null
     */
    private ?array $synonyms = null;

    public static function create(array $settings): self
    {
        $self = new self();

        $self->rankingRules = $settings['rankingRules'];
        $self->attributesForFaceting = $settings['attributesForFaceting'];
        $self->distinctAttribute = $settings['distinctAttribute'];
        $self->searchableAttributes = $settings['searchableAttributes'];
        $self->displayedAttributes = $settings['displayedAttributes'];
        $self->stopWords = $settings['stopWords'];
        $self->synonyms = $settings['synonyms'];

        return $self;
    }

    /**
     * @return array<int, string>
     */
    public function getRankingRules(): array
    {
        return $this->rankingRules;
    }

    /**
     * @return array<int, string>
     */
    public function getAttributesForFaceting(): array
    {
        return $this->attributesForFaceting;
    }

    public function getDistinctAttribute(): ?string
    {
        return $this->distinctAttribute;
    }

    /**
     * @return array<int, string>
     */
    public function getDisplayedAttributes(): array
    {
        return $this->displayedAttributes;
    }

    /**
     * @return array<int, string>
     */
    public function getSearchableAttributes(): array
    {
        return $this->searchableAttributes;
    }

    /**
     * @return array<int, string>|null
     */
    public function getStopWords(): ?array
    {
        return $this->stopWords;
    }

    /**
     * @return array<string, array<int, string>>|null
     */
    public function getSynonyms(): ?array
    {
        return $this->synonyms;
    }
}
