<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Document;

use MeiliSearchBundle\DataCollector\TraceableDataCollectorInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableDocumentEntryPoint implements DocumentEntryPointInterface, TraceableDataCollectorInterface
{
    private const DOCUMENT = 'document';
    private const PRIMARY_KEY = 'primaryKey';
    private const INDEX = 'index';
    private const MODEL = 'model';

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
        'addedDocuments' => [],
        'removedDocuments' => [],
        'retrievedDocuments' => [],
        'updatedDocuments' => [],
    ];

    public function __construct(
        DocumentEntryPointInterface $documentOrchestrator,
        ?LoggerInterface $logger = null
    ) {
        $this->documentOrchestrator = $documentOrchestrator;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function addDocument(string $uid, array $document, string $primaryKey = null, string $model = null): void
    {
        $this->documentOrchestrator->addDocument($uid, $document, $primaryKey, $model);

        $this->data['addedDocuments'][$uid][] = [
            self::DOCUMENT => $document,
            self::PRIMARY_KEY => $primaryKey,
            self::MODEL => $model,
        ];

        $this->logInfo('A document has been added', [
            self::DOCUMENT => $document,
            self::INDEX => $uid,
            self::PRIMARY_KEY => $primaryKey,
            self::MODEL => $model,
        ]);
    }

    public function getDocument(string $uid, $id)
    {
        $document = $this->documentOrchestrator->getDocument($uid, $id);

        $this->data['retrievedDocuments'][$uid][] = [
            self::DOCUMENT => $document,
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

        $this->data['retrievedDocuments'][$uid][] = $documents;

        $this->logInfo('A set of documents has been retrieved', [
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

        $this->data['updatedDocuments'][$uid][] = [
            self::DOCUMENT => $documentUpdate,
            self::INDEX => $uid,
            self::PRIMARY_KEY => $primaryKey,
        ];

        $this->logInfo('A document has been updated', [
            self::DOCUMENT => $documentUpdate,
            self::INDEX => $uid,
            self::PRIMARY_KEY => $primaryKey,
        ]);
    }

    public function removeDocument(string $uid, $id): void
    {
        $this->documentOrchestrator->removeDocument($uid, $id);

        $this->data['removedDocuments'][$uid][] = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function removeSetOfDocuments(string $uid, array $ids): void
    {
        $this->documentOrchestrator->removeSetOfDocuments($uid, $ids);

        $this->data['removedDocuments'][$uid][] = $ids;
    }

    public function removeDocuments(string $uid): void
    {
        $this->documentOrchestrator->removeDocuments($uid);

        $this->logInfo('All the documents have been deleted', [self::INDEX => $uid]);
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
            'addedDocuments' => [],
            'removedDocuments' => [],
            'retrievedDocuments' => [],
            'updatedDocuments' => [],
        ];
    }

    private function logInfo(string $message, array $context = []): void
    {
        if (null === $this->logger) {
            return;
        }

        $this->logger->info($message, $context);
    }
}
