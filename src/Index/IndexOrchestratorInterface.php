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
     * @param string                        $uid
     * @param string|null                   $primaryKey
     * @param array<string,string|int|bool> $config
     */
    public function addIndex(string $uid, ?string $primaryKey = null, array $config = []): void;

    /**
     * @return array<string,Indexes>
     */
    public function getIndexes(): array;

    public function getIndex(string $uid): Indexes;

    public function removeIndexes(): void;

    public function removeIndex(string $uid): void;
}
