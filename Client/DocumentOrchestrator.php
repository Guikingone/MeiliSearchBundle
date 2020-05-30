<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

use MeiliSearch\Client;
use MeiliSearchBundle\Event\Document\PostDocumentDeletionEvent;
use MeiliSearchBundle\Event\Document\PostDocumentUpdateEvent;
use MeiliSearchBundle\Event\Document\PreDocumentUpdateEvent;
use MeiliSearchBundle\Exception\InvalidArgumentException;
use MeiliSearchBundle\Exception\RuntimeException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentOrchestrator
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

    public function __construct(Client $client, ?EventDispatcherInterface $eventDispatcher, ?LoggerInterface $logger)
    {
        $this->client = $client;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }


    public function getDocument(string $uid, string $id): array
    {
        try {
            $index = $this->client->getIndex($uid);

            return $index->getDocument($id);
        } catch (Throwable $exception) {
            $this->logError(sprintf('The document cannot be retrieved, error: "%s"', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }
    }

    public function getDocuments(string $uid, array $options = null): array
    {
        if (null !== $options) {
            foreach ($options as $option) {
                if (!\in_array($option, ['offset', 'limit', 'attributesToReceive'])) {
                    throw new InvalidArgumentException(sprintf('The option "%s" is not a valid one.', $option));
                }
            }
        }

        try {
            $index = $this->client->getIndex($uid);

            return $index->getDocuments($options);
        } catch (Throwable $exception) {
            $this->logError(sprintf('The documents cannot be retrieved, error: "%s"', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }
    }

    public function updateDocument(string $uid, array $documentUpdate, array $documentKey = null): void
    {
        try {
            $index = $this->client->getIndex($uid);

            $this->dispatch(new PreDocumentUpdateEvent($documentUpdate));
            $documentUpdateId = $index->updateDocuments($documentUpdate, $documentKey);
            $this->dispatch(new PostDocumentUpdateEvent($documentUpdateId['updateId']));
        } catch (Throwable $exception) {
            $this->logError(sprintf('The document cannot be updated, error: "%s"', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }
    }

    public function removeDocument(string $uid, string $id): void
    {
        try {
            $index = $this->client->getIndex($uid);

            $update = $index->deleteDocument($id);
            $this->dispatch(new PostDocumentDeletionEvent($update['updateId']));
        } catch (Throwable $exception) {
            $this->logError(sprintf('The document cannot be removed, error: "%s"', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }
    }

    public function removeSetOfDocuments(string $uid, array $ids): void
    {
        try {
            $index = $this->client->getIndex($uid);

            $updateId = $index->deleteDocuments($ids);

            $this->logInfo('A set of documents has been removed', ['documents' => implode(', ', $ids), 'update_identifier' => $updateId]);
        } catch (Throwable $exception) {
            $this->logError(sprintf('The documents cannot be removed, error: "%s"', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }
    }

    public function removeDocuments(string $uid): void
    {
        try {
            $index = $this->client->getIndex($uid);

            $index->deleteAllDocuments();
        } catch (Throwable $exception) {
            $this->logError(sprintf('The documents cannot be removed, error: "%s"', $exception->getMessage()));
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
