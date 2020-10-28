<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Document;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface DocumentEntryPointInterface
{
    /**
     * Add a single document in the desired index.
     *
     * @param string                         $uid
     * @param array<string, int|string|bool> $document
     * @param string|null                    $primaryKey
     * @param string|null                    $model
     */
    public function addDocument(string $uid, array $document, string $primaryKey = null, string $model = null): void;

    /**
     * Add multiple documents in the desired index.
     *
     * @param string                         $uid
     * @param array<string, int|string|bool> $documents
     * @param string|null                    $primaryKey
     */
    public function addDocuments(string $uid, array $documents, string $primaryKey = null): void;

    /**
     * @param string     $uid
     * @param string|int $id
     *
     * @return array|object As a document can be stored with a 'model' key, an object can be returned.
     */
    public function getDocument(string $uid, $id);

    /**
     * @param string              $uid
     * @param array<string,mixed> $options
     *
     * @return array<int, array|object> Can be both an array of arrays or an array of objects.
     */
    public function getDocuments(string $uid, array $options = []): array;

    /**
     * @param string                         $uid
     * @param array<string, int|string|bool> $documentUpdate
     * @param string|null                    $primaryKey
     */
    public function updateDocument(string $uid, array $documentUpdate, string $primaryKey = null): void;

    /**
     * @param string     $uid
     * @param string|int $id
     */
    public function removeDocument(string $uid, $id): void;

    /**
     * @param string          $uid
     * @param array<int, int> $ids
     */
    public function removeSetOfDocuments(string $uid, array $ids): void;

    public function removeDocuments(string $uid): void;
}
