<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Document;

use InvalidArgumentException;
use Meilisearch\Client;
use Meilisearch\Contracts\DocumentsQuery;
use MeiliSearchBundle\Event\Document\PostDocumentCreationEvent;
use MeiliSearchBundle\Event\Document\PostDocumentDeletionEvent;
use MeiliSearchBundle\Event\Document\PostDocumentRetrievedEvent;
use MeiliSearchBundle\Event\Document\PostDocumentUpdateEvent;
use MeiliSearchBundle\Event\Document\PreDocumentCreationEvent;
use MeiliSearchBundle\Event\Document\PreDocumentDeletionEvent;
use MeiliSearchBundle\Event\Document\PreDocumentRetrievedEvent;
use MeiliSearchBundle\Event\Document\PreDocumentUpdateEvent;
use MeiliSearchBundle\Exception\RuntimeException;
use MeiliSearchBundle\Result\ResultBuilderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

use function array_key_exists;
use function array_map;
use function implode;
use function in_array;
use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentEntryPoint implements DocumentEntryPointInterface
{
    private const TASK_UID = 'taskUid';

    private const MODEL = 'model';

    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly Client $client,
        private readonly ResultBuilderInterface $resultBuilder,
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function addDocument(string $uid, array $document, string $primaryKey = null, string $model = null): void
    {
        try {
            $index = $this->client->index($uid);

            if (null !== $model) {
                $document = [...$document, self::MODEL => $model];
            }

            $this->dispatch(new PreDocumentCreationEvent($index, $document));
            $update = $index->addDocuments([$document], $primaryKey);
            $this->dispatch(new PostDocumentCreationEvent($index, $update[self::TASK_UID]));
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('The document cannot be created, error: "%s"', $throwable->getMessage()));

            throw new RuntimeException($throwable->getMessage(), 0, $throwable);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addDocuments(string $uid, array $documents, string $primaryKey = null): void
    {
        try {
            $index = $this->client->index($uid);

            $this->dispatch(new PreDocumentCreationEvent($index, $documents));
            $update = $index->addDocuments($documents, $primaryKey);
            $this->dispatch(new PostDocumentCreationEvent($index, $update[self::TASK_UID]));
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('The document cannot be created, error: "%s"', $throwable->getMessage()));

            throw new RuntimeException($throwable->getMessage(), 0, $throwable);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDocument(string $uid, $id)
    {
        try {
            $index = $this->client->index($uid);

            $this->dispatch(new PreDocumentRetrievedEvent($index, $id));
            $document = $index->getDocument($id);
            $this->dispatch(new PostDocumentRetrievedEvent($index, $document));

            return array_key_exists(self::MODEL, $document) ? $this->resultBuilder->build($document) : $document;
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('The document cannot be retrieved, error: "%s"', $throwable->getMessage()));

            throw new RuntimeException($throwable->getMessage(), 0, $throwable);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDocuments(string $uid, array $options = []): array
    {
        $documentsQuery = new DocumentsQuery();
        foreach ($options as $key => $option) {
            if (!in_array($key, ['offset', 'limit', 'fields', 'filter'])) {
                throw new InvalidArgumentException(sprintf('The option "%s" is not a valid one.', $option));
            }

            $propertySetter = sprintf('set%s', ucfirst($key));
            $documentsQuery->{$propertySetter}($option);
        }

        try {
            $index = $this->client->index($uid);

            $documents = $index->getDocuments($documentsQuery);

            $results = $documents->getResults();

            return array_map(
                fn (array $document) => array_key_exists(self::MODEL, $document) ? $this->resultBuilder->build(
                    $document
                ) : $document,
                $results
            );
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('The documents cannot be retrieved, error: "%s"', $throwable->getMessage()));

            throw new RuntimeException($throwable->getMessage(), 0, $throwable);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateDocument(string $uid, array $documentUpdate, string $primaryKey = null): void
    {
        try {
            $index = $this->client->index($uid);

            $this->dispatch(new PreDocumentUpdateEvent($index, $documentUpdate));
            $documentUpdateId = $index->updateDocuments([$documentUpdate], $primaryKey);
            $this->dispatch(new PostDocumentUpdateEvent($documentUpdateId[self::TASK_UID]));
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('The document cannot be updated, error: "%s"', $throwable->getMessage()));

            throw new RuntimeException($throwable->getMessage(), 0, $throwable);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeDocument(string $uid, $id): void
    {
        try {
            $index = $this->client->index($uid);

            $this->dispatch(new PreDocumentDeletionEvent($index, $index->getDocument($id)));
            $update = $index->deleteDocument($id);
            $this->dispatch(new PostDocumentDeletionEvent($update[self::TASK_UID]));
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('The document cannot be removed, error: "%s"', $throwable->getMessage()));

            throw new RuntimeException($throwable->getMessage(), 0, $throwable);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeSetOfDocuments(string $uid, array $ids): void
    {
        try {
            $index = $this->client->index($uid);

            $update = $index->deleteDocuments($ids);

            $this->logger->info('A set of documents has been removed', [
                'documents' => implode(', ', $ids),
                'task_uid' => $update[self::TASK_UID],
            ]);
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('The documents cannot be removed, error: "%s"', $throwable->getMessage()));

            throw new RuntimeException($throwable->getMessage(), 0, $throwable);
        }
    }

    public function removeDocuments(string $uid): void
    {
        try {
            $index = $this->client->index($uid);

            $index->deleteAllDocuments();
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf('The documents cannot be removed, error: "%s"', $throwable->getMessage()));

            throw new RuntimeException($throwable->getMessage(), 0, $throwable);
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
