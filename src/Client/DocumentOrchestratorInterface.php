<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface DocumentOrchestratorInterface
{
    public function addDocument(string $uid, array $document, string $primaryKey = null): void;

    public function getDocument(string $uid, string $id): array;

    public function getDocuments(string $uid, array $options = null): array;

    public function updateDocument(string $uid, array $documentUpdate, array $documentKey = null): void;

    public function removeDocument(string $uid, string $id): void;

    public function removeSetOfDocuments(string $uid, array $ids): void;

    public function removeDocuments(string $uid): void;
}
