<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Metadata;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface IndexMetadataInterface
{
    public function getUid(): string;

    public function isAsync(): bool;

    public function getPrimaryKey(): ?string;

    /**
     * @return array<int, string>
     */
    public function getRankingRules(): array;

    /**
     * @return array<int, string>
     */
    public function getStopWords(): array;

    public function getDistinctAttribute(): ?string;

    public function getFacetedAttributes(): array;

    /**
     * @return array<int, string>
     */
    public function getSearchableAttributes(): array;

    /**
     * @return array<int, string>
     */
    public function getDisplayedAttributes(): array;

    /**
     * @return array<string, array>
     */
    public function getSynonyms(): array;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
