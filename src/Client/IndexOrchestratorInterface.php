<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

use MeiliSearch\Index;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface IndexOrchestratorInterface
{
    public function addIndex(string $uid, ?string $primaryKey = null, array $config = []): void;

    public function getIndexes(): array;

    public function getIndex(string $uid): Index;

    public function removeIndexes(): void;

    public function removeIndex(string $uid): void;
}
