<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

use MeiliSearch\Client;
use MeiliSearch\Index;
use MeiliSearchBundle\Exception\RuntimeException;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class UpdateOrchestrator
{
    /**
     * @var Client
     */
    private $client;

    public function getUpdate(string $uid, int $updateId): Update
    {
        $index = $this->client->getIndex($uid);
        if (!$index instanceof Index) {
            throw new RuntimeException('The index uid does not exist');
        }

        $update = $index->getUpdateStatus($updateId);

        return Update::create($update['status'], $update['updateId'], $update['type'], $update['duration'], $update['enqueuedAt'], $update['processedAt']);
    }

    /**
     * @param string $uid
     *
     * @return array<int,Update>
     */
    public function getUpdates(string $uid): array
    {
        $index = $this->client->getIndex($uid);
        if (!$index instanceof Index) {
            throw new RuntimeException('The index uid does not exist');
        }

        $updates = $index->getAllUpdateStatus();

        $values = [];
        array_walk($updates, function (array $update) use (&$values): void {
            $values[] = Update::create($update['status'], $update['updateId'], $update['type'], $update['duration'], $update['enqueuedAt'], $update['processedAt']);
        });

        return $values;
    }
}
