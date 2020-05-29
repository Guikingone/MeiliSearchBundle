<?php

namespace MeiliBundle\Client;

use MeiliBundle\Client\ClientInterface as CoreClientInterface;
use MeiliBundle\Event\IndexCreatedEvent;
use MeiliBundle\Event\IndexRemovedEvent;
use MeiliSearch\Client;
use Psr\Http\Client\ClientInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliClient implements CoreClientInterface
{
    private $client;
    private $eventDispatcher;

    public function __construct(string $host, string $apiKey, ClientInterface $client = null, EventDispatcherInterface $eventDispatcher = null)
    {
        $this->client = new Client($host, $apiKey, $client);
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function createIndex(string $primaryKey, string $uid = null): void
    {
        $config = [
            'uid' => $uid ?? $primaryKey,
            'primaryKey' => $primaryKey,
        ];

        $this->client->createIndex($config);
        $this->dispatch(new IndexCreatedEvent($config));
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex(string $uid): void
    {
        $this->client->deleteIndex($uid);
        $this->dispatch(new IndexRemovedEvent($uid));
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexes(): array
    {
        return $this->client->getAllIndexes();
    }

    private function dispatch(Event $event): void
    {
        if (null === $this->eventDispatcher) {
            return;
        }

        $this->eventDispatcher->dispatch($event);
    }
}
