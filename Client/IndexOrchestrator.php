<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

use MeiliSearch\Client;
use MeiliSearch\Index;
use MeiliSearchBundle\Event\Index\IndexCreatedEvent;
use MeiliSearchBundle\Event\Index\IndexRetrievedEvent;
use MeiliSearchBundle\Exception\RuntimeException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexOrchestrator implements IndexOrchestratorInterface
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

    public function __construct(
        Client $client,
        ?EventDispatcherInterface $eventDispatcher = null,
        ?LoggerInterface $logger = null
    ) {
        $this->client = $client;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function addIndex(string $uid, ?string $primaryKey = null): void
    {
        $config = [
            'uid' => $uid,
            'primaryKey' => $primaryKey,
        ];

        try {
            $index = $this->client->createIndex($config);
        } catch (Throwable $exception) {
            $this->logError(sprintf('The index cannot be created, error: "%s"', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }

        $this->dispatch(new IndexCreatedEvent($config, $index));
        $this->logInfo('An index has been created.', $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexes(): array
    {
        try {
            return $this->client->getAllIndexes();
        } catch (Throwable $exception) {
            $this->logError(sprintf('The indexes cannot be retrieved, error: "%s".', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIndex(string $uid): Index
    {
        try {
            $index = $this->client->getIndex($uid);

            $this->dispatch(new IndexRetrievedEvent($index));
            $this->logInfo('An index has been retrieved', ['uid' => $uid]);

            return $index;
        } catch (Throwable $exception) {
            $this->logError(sprintf('The index cannot be retrieved, error: "%s".', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeIndexes(): void
    {
        try {
            $this->client->deleteAllIndexes();
        } catch (Throwable $exception) {
            $this->logError(sprintf('The indexes cannot be deleted, error: "%s".', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }

        $this->logInfo('The indexes have been deleted');
    }

    /**
     * {@inheritdoc}
     */
    public function removeIndex(string $uid): void
    {
        try {
            $this->client->deleteIndex($uid);
        } catch (Throwable $exception) {
            $this->logError(sprintf('The index cannot be deleted, error: "%s".', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }

        $this->logInfo('An index has been deleted', ['uid' => $uid]);
    }

    private function dispatch(Event $event): void
    {
        if (null === $this->eventDispatcher) {
            return;
        }

        $this->eventDispatcher->dispatch($event);
    }

    private function logError(string $message, array $context = []): void
    {
        if (null === $this->logger) {
            return;
        }

        $this->logger->error($message, $context);
    }

    private function logInfo(string $message, array $context = []): void
    {
        if (null === $this->logger) {
            return;
        }

        $this->logger->info($message, $context);
    }
}
