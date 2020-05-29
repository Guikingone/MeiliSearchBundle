<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

use MeiliSearchBundle\Client\ClientInterface as CoreClientInterface;
use MeiliSearchBundle\Event\IndexCreatedEvent;
use MeiliSearchBundle\Event\IndexRemovedEvent;
use MeiliSearchBundle\Event\IndexRetrievedEvent;
use MeiliSearch\Client;
use MeiliSearchBundle\Exception\RuntimeException;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliClient implements CoreClientInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var EventDispatcherInterface|null
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    public function __construct(string $host, string $apiKey = null, ClientInterface $client = null, EventDispatcherInterface $eventDispatcher = null, LoggerInterface $logger = null)
    {
        $this->client = new Client($host, $apiKey, $client);
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function createIndex(string $uid, string $primaryKey = null): void
    {
        $config = [
            'uid' => $uid,
            'primaryKey' => $primaryKey,
        ];

        try {
            $this->client->createIndex($config);
        } catch (Throwable $exception) {
            $this->logError(sprintf('The "%s" encounter an error, message: "%s"', self::class, $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }

        $this->dispatch(new IndexCreatedEvent($config));

        $this->logInfo('An index has been created.', $config);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex(string $uid): void
    {
        try {
            $this->client->deleteIndex($uid);
        } catch (Throwable $exception) {
            $this->logError(sprintf('The "%s" encounter an error, message: "%s"', self::class, $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }

        $this->dispatch(new IndexRemovedEvent($uid));

        $this->logInfo('An index has been deleted.', ['uid' => $uid]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexes(): array
    {
        try {
            return $this->client->getAllIndexes();
        } catch (Throwable $exception) {
            $this->logError(sprintf('The indexes cannot be retrieved, error "%s"', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndexes(): void
    {
        try {
            $this->client->deleteAllIndexes();
        } catch (Throwable $exception) {
            $this->logError(sprintf('The indexes cannot be deleted, error "%s"', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }

        $this->logInfo('The indexes has been removed.');
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $index, string $query, array $options = null): array
    {
        $index = $this->client->getIndex($index);

        $this->dispatch(new IndexRetrievedEvent($index));

        $this->logInfo(sprintf('A query has been made in the index "%s"', $index), array_merge($options, ['query' => $query]));

        return $index->search($query, $options);
    }

    /**
     * {@see https://docs.meilisearch.com/references/sys-info.html#get-pretty-system-information}
     */
    public function getSystemInformations(): array
    {
        try {
            return $this->client->prettySysInfo();
        } catch (Throwable $exception) {
            $this->logError(sprintf('The system informations cannot be retrieved due to an error, message "%s"', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }
    }

    private function dispatch(Event $event): void
    {
        if (null === $this->eventDispatcher) {
            return;
        }

        $this->eventDispatcher->dispatch($event);
    }

    private function logInfo(string $message, array $context = []): void
    {
        if (null === $this->logger) {
            return;
        }

        $this->logger->info($message, $context);
    }

    private function logError(string $message, array $context = []): void
    {
        if (null === $this->logger) {
            return;
        }

        $this->logger->error($message, $context);
    }
}
