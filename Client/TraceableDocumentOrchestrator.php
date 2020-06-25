<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

use Psr\Log\LoggerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableDocumentOrchestrator implements DocumentOrchestratorInterface
{
    /**
     * @var DocumentOrchestratorInterface
     */
    private $documentOrchestrator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        DocumentOrchestratorInterface $documentOrchestrator,
        LoggerInterface $logger = null
    ) {
        $this->documentOrchestrator = $documentOrchestrator;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocument(string $uid, string $id): array
    {
        $document = $this->documentOrchestrator->getDocument($uid, $id);

        $this->logger->info('A document has been retrieved', ['document' => $id]);

        return $document;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocuments(string $uid, array $options = null): array
    {
        return $this->documentOrchestrator->getDocuments($uid, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function updateDocument(string $uid, array $documentUpdate, array $documentKey = null): void
    {
        $this->documentOrchestrator->updateDocument($uid, $documentUpdate, $documentKey);
    }

    /**
     * {@inheritdoc}
     */
    public function removeDocument(string $uid, string $id): void
    {
        $this->documentOrchestrator->removeDocument($uid, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function removeSetOfDocuments(string $uid, array $ids): void
    {
        $this->documentOrchestrator->removeSetOfDocuments($uid, $ids);
    }

    /**
     * {@inheritdoc}
     */
    public function removeDocuments(string $uid): void
    {
        $this->documentOrchestrator->removeDocuments($uid);

        $this->logger->info('All the documents have been deleted', ['index' => $uid]);
    }
}
