<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableDocumentOrchestrator implements DocumentOrchestratorInterface
{
    /**
     * @var DocumentOrchestratorInterface
     */
    private $documentOrchestrator;

    public function __construct(DocumentOrchestratorInterface $documentOrchestrator)
    {
        $this->documentOrchestrator = $documentOrchestrator;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocument(string $uid, string $id): array
    {
        return $this->documentOrchestrator->getDocument($uid, $id);
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
    }
}
