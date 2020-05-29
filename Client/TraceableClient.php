<?php

namespace MeiliBundle\Client;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableClient implements ClientInterface
{
    private $client;
    private $createdIndexes = [];
    private $deletedIndexes = [];

    public function __construct(MeiliClient $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function createIndex(string $primaryKey, string $uid = null): void
    {
        $this->client->createIndex($primaryKey, $uid);

        $this->createdIndexes[$uid] = $primaryKey;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex(string $uid): void
    {
        $this->client->deleteIndex($uid);

        $this->deletedIndexes[] = $uid;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexes(): array
    {
        return $this->client->getIndexes();
    }

    public function getCreatedIndexes(): array
    {
        return $this->createdIndexes;
    }

    public function getDeletedIndexes(): array
    {
        return $this->deletedIndexes;
    }
}
