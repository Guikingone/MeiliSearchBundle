<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Document;

use MeiliSearchBundle\DataCollector\TraceableDataCollectorInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function get_class;
use function is_object;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableDocumentEntryPoint implements DocumentEntryPointInterface, TraceableDataCollectorInterface
{
    private const DOCUMENT = 'document';
    private const PRIMARY_KEY = 'primaryKey';
    private const INDEX = 'index';
    private const MODEL = 'model';

    private const DATA_ADDED_DOCUMENTS_KEY = 'addedDocuments';
    private const DATA_REMOVED_DOCUMENTS = 'removedDocuments';
    private const DATA_RETRIEVED_DOCUMENTS = 'retrievedDocuments';
    private const DATA_UPDATED_DOCUMENTS = 'updatedDocuments';

    /**
     * @var DocumentEntryPointInterface
     */
    private $documentOrchestrator;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @var array<string,array>
     */
    private $data = [
        self::DATA_ADDED_DOCUMENTS_KEY => [],
        self::DATA_REMOVED_DOCUMENTS => [],
        self::DATA_RETRIEVED_DOCUMENTS => [],
        self::DATA_UPDATED_DOCUMENTS => [],
    ];

    public function __construct(
        DocumentEntryPointInterface $documentOrchestrator,
        ?LoggerInterface $logger = null
    ) {
        $this->documentOrchestrator = $documentOrchestrator;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function addDocument(string $uid, array $document, string $primaryKey = null, string $model = null): void
    {
        $this->documentOrchestrator->addDocument($uid, $document, $primaryKey, $model);

        $this->data[self::DATA_ADDED_DOCUMENTS_KEY][$uid][] = [
            self::DOCUMENT => $document,
            self::PRIMARY_KEY => $primaryKey,
            self::MODEL => $model,
        ];
    }

    public function getDocument(string $uid, $id)
    {
        $document = $this->documentOrchestrator->getDocument($uid, $id);

        $this->data[self::DATA_RETRIEVED_DOCUMENTS][$uid][] = [
            self::DOCUMENT => is_object($document) ? get_class($document) : $document,
            'id' => $id,
        ];

        return $document;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocuments(string $uid, array $options = []): array
    {
        $documents = $this->documentOrchestrator->getDocuments($uid, $options);

        foreach ($documents as $document) {
            $this->data[self::DATA_RETRIEVED_DOCUMENTS][$uid][] = [
                self::DOCUMENT => is_object($document) ? get_class($document) : $document,
            ];
        }

        $this->logger->info('A set of documents has been retrieved', [
            self::INDEX => $uid,
            'options' => $options,
        ]);

        return $documents;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDocument(string $uid, array $documentUpdate, string $primaryKey = null): void
    {
        $this->documentOrchestrator->updateDocument($uid, $documentUpdate, $primaryKey);

        $this->data[self::DATA_UPDATED_DOCUMENTS][$uid][] = [
            self::DOCUMENT => $documentUpdate,
            self::INDEX => $uid,
            self::PRIMARY_KEY => $primaryKey,
        ];
    }

    public function removeDocument(string $uid, $id): void
    {
        $this->documentOrchestrator->removeDocument($uid, $id);

        $this->data[self::DATA_REMOVED_DOCUMENTS][$uid][] = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function removeSetOfDocuments(string $uid, array $ids): void
    {
        $this->documentOrchestrator->removeSetOfDocuments($uid, $ids);

        $this->data[self::DATA_REMOVED_DOCUMENTS][$uid][] = $ids;
    }

    public function removeDocuments(string $uid): void
    {
        $this->documentOrchestrator->removeDocuments($uid);

        $this->logger->info('All the documents have been deleted', [self::INDEX => $uid]);
    }

    /**
     * @return array<string,array>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->data = [
            self::DATA_ADDED_DOCUMENTS_KEY => [],
            self::DATA_REMOVED_DOCUMENTS => [],
            self::DATA_RETRIEVED_DOCUMENTS => [],
            self::DATA_UPDATED_DOCUMENTS => [],
        ];
    }
}
