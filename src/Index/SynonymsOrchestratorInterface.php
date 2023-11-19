<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Index;

use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface SynonymsOrchestratorInterface
{
    /**
     *
     * @throws Throwable
     *
     * {@see https://docs.meilisearch.com/references/synonyms.html#get-synonyms}
     * @return array<string,array>
     */
    public function getSynonyms(string $uid): array;

    /**
     * @throws Throwable
     *
     * {@see https://docs.meilisearch.com/references/synonyms.html#update-synonyms}
     * @param array<string,array> $synonyms
     */
    public function updateSynonyms(string $uid, array $synonyms): void;

    /**
     * @throws Throwable
     *
     * {@see https://docs.meilisearch.com/references/synonyms.html#reset-synonyms}
     */
    public function resetSynonyms(string $uid): void;
}
