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
     * @param string $uid
     *
     * @return array<string,array>
     *
     * @throws Throwable
     *
     * {@see https://docs.meilisearch.com/references/synonyms.html#get-synonyms}
     */
    public function getSynonyms(string $uid): array;

    /**
     * @param string              $uid
     * @param array<string,array> $synonyms
     *
     * @throws Throwable
     *
     * {@see https://docs.meilisearch.com/references/synonyms.html#update-synonyms}
     */
    public function updateSynonyms(string $uid, array $synonyms): void;

    /**
     * @param string $uid
     *
     * @throws Throwable
     *
     * {@see https://docs.meilisearch.com/references/synonyms.html#reset-synonyms}
     */
    public function resetSynonyms(string $uid): void;
}
