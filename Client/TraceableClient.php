<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableClient implements ClientInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var string[]
     */
    private $createdIndexes = [];

    /**
     * @var string[]
     */
    private $deletedIndexes = [];

    private $queries = [];

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function createIndex(string $uid, string $primaryKey = null): void
    {
        $this->client->createIndex($uid, $primaryKey);

        $this->createdIndexes[$uid] = ['primary_key' => $primaryKey];
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

    /**
     * {@inheritdoc}
     */
    public function deleteIndexes(): void
    {
        $this->client->deleteIndexes();
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $index, string $query, array $options = null): array
    {
        $this->queries[$index] = [
            'query' => $query,
            'options' => $options,
        ];

        return $this->client->search($index, $query, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getSystemInformations(): array
    {
        return $this->client->getSystemInformations();
    }

    public function getCreatedIndexes(): array
    {
        return $this->createdIndexes;
    }

    public function getDeletedIndexes(): array
    {
        return $this->deletedIndexes;
    }

    public function getQueries(): array
    {
        return $this->queries;
    }
}
