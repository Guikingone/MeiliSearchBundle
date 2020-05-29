<?php

namespace MeiliBundle\Client;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface ClientInterface
{
    public function createIndex(string $primaryKey, string $uid = null): void;

    public function deleteIndex(string $uid): void;

    public function getIndexes(): array;
}
