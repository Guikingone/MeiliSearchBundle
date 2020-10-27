<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Document;

use MeiliSearch\Client;
use MeiliSearchBundle\Event\Document\PostDocumentCreationEvent;
use MeiliSearchBundle\Event\Document\PostDocumentDeletionEvent;
use MeiliSearchBundle\Event\Document\PostDocumentRetrievedEvent;
use MeiliSearchBundle\Event\Document\PostDocumentUpdateEvent;
use MeiliSearchBundle\Event\Document\PreDocumentCreationEvent;
use MeiliSearchBundle\Event\Document\PreDocumentDeletionEvent;
use MeiliSearchBundle\Event\Document\PreDocumentRetrievedEvent;
use MeiliSearchBundle\Event\Document\PreDocumentUpdateEvent;
use MeiliSearchBundle\Exception\InvalidArgumentException;
use MeiliSearchBundle\Exception\RuntimeException;
use MeiliSearchBundle\Result\ResultBuilderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;
use function array_key_exists;
use function array_merge;
use function array_walk;
use function in_array;
use function sprintf;
use function implode;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentEntryPoint implements DocumentEntryPointInterface
{
    private const UPDATE_ID = 'updateId';
    private const MODEL = 'model';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var ResultBuilderInterface
     */
    private $resultBuilder;

    /**
     * @var EventDispatcherInterface|null
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Client $client,
        ResultBuilderInterface $resultBuilder,
        ?EventDispatcherInterface $eventDispatcher = null,
        ?LoggerInterface $logger = null
    ) {
        $this->client = $client;
        $this->resultBuilder = $resultBuilder;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function addDocument(string $uid, array $document, string $primaryKey = null, string $model = null): void
    {
        try {
            $index = $this->client->getIndex($uid);

            if (null !== $model) {
                $document = array_merge($document, [
                    self::MODEL => $model,
                ]);
            }

            $this->dispatch(new PreDocumentCreationEvent($index, $document));
            $update = $index->addDocuments([$document], $primaryKey);
            $this->dispatch(new PostDocumentCreationEvent($index, $update[self::UPDATE_ID]));
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('The document cannot be created, error: "%s"', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDocument(string $uid, $id)
    {
        try {
            $index = $this->client->getIndex($uid);

            $this->dispatch(new PreDocumentRetrievedEvent($index, $id));
            $document = $index->getDocument($id);
            $this->dispatch(new PostDocumentRetrievedEvent($index, $document));

            return array_key_exists(self::MODEL, $document) ? $this->resultBuilder->build($document) : $document;
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('The document cannot be retrieved, error: "%s"', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDocuments(string $uid, array $options = []): array
    {
        if (!empty($options)) {
            foreach ($options as $option) {
                if (!in_array($option, ['offset', 'limit', 'attributesToReceive'])) {
                    throw new InvalidArgumentException(sprintf('The option "%s" is not a valid one.', $option));
                }
            }
        }

        try {
            $index = $this->client->getIndex($uid);

            $documents = $index->getDocuments($options);

            $data = [];
            array_walk($documents, function (array $document) use (&$data): void {
                $data[] = array_key_exists(self::MODEL, $document) ? $this->resultBuilder->build($document) : $document;
            });

            return $data;
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('The documents cannot be retrieved, error: "%s"', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateDocument(string $uid, array $documentUpdate, string $primaryKey = null): void
    {
        try {
            $index = $this->client->getIndex($uid);

            $this->dispatch(new PreDocumentUpdateEvent($index, $documentUpdate));
            $documentUpdateId = $index->updateDocuments([$documentUpdate], $primaryKey);
            $this->dispatch(new PostDocumentUpdateEvent($documentUpdateId[self::UPDATE_ID]));
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('The document cannot be updated, error: "%s"', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }
    }

    public function removeDocument(string $uid, $id): void
    {
        try {
            $index = $this->client->getIndex($uid);

            $this->dispatch(new PreDocumentDeletionEvent($index, $index->getDocument($id)));
            $update = $index->deleteDocument($id);
            $this->dispatch(new PostDocumentDeletionEvent($update[self::UPDATE_ID]));
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('The document cannot be removed, error: "%s"', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeSetOfDocuments(string $uid, array $ids): void
    {
        try {
            $index = $this->client->getIndex($uid);

            $update = $index->deleteDocuments($ids);

            $this->logger->info('A set of documents has been removed', [
                'documents' => implode(', ', $ids),
                'update_identifier' => $update[self::UPDATE_ID],
            ]);
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('The documents cannot be removed, error: "%s"', $exception->getMessage()));
            throw new RuntimeException($exception->getMessage());
        }
    }

    public function removeDocuments(string $uid): void
    {
        try {
            $index = $this->client->getIndex($uid);

            $index->deleteAllDocuments();
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('The documents cannot be removed, error: "%s"', $exception->getMessage()));
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
}
