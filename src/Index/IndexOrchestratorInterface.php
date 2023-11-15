<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Index;

use Meilisearch\Endpoints\Indexes;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface IndexOrchestratorInterface
{
    /**
     * @param string|null $primaryKey
     * @param array<string, string|int|bool> $configuration
     */
    public function addIndex(string $uid, ?string $primaryKey = null, array $configuration = []): void;

    /**
     * @param array<string, string|int|bool> $configuration
     */
    public function update(string $uid, array $configuration = []): void;

    /**
     * @return IndexListInterface<string, Indexes>
     */
    public function getIndexes(): IndexListInterface;


    public function getIndex(string $uid): Indexes;

    public function removeIndex(string $uid): void;
}
