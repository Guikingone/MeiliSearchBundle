<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Index;

use MeiliSearch\Endpoints\Indexes;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface IndexOrchestratorInterface
{
    /**
     * @param string                         $uid
     * @param string|null                    $primaryKey
     * @param array<string, string|int|bool> $config
     */
    public function addIndex(string $uid, ?string $primaryKey = null, array $config = []): void;

    /**
     * @param string                         $uid
     * @param array<string, string|int|bool> $configuration
     */
    public function update(string $uid, array $configuration = []): void;

    /**
     * @return IndexListInterface<string, Indexes>
     */
    public function getIndexes(): IndexListInterface;

    /**
     * @param string $uid
     *
     * @return Indexes
     */
    public function getIndex(string $uid): Indexes;

    public function removeIndexes(): void;

    /**
     * @param string $uid
     */
    public function removeIndex(string $uid): void;
}
