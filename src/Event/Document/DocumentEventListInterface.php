<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Document;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface DocumentEventListInterface
{
    public function add(DocumentEventInterface $event): void;

    /**
     * @return array<int, PostDocumentCreationEvent>
     */
    public function getPostDocumentCreationEvent(): array;

    /**
     * @return array<int, PostDocumentDeletionEvent>
     */
    public function getPostDocumentDeletionEvent(): array;

    /**
     * @return array<int, PostDocumentRetrievedEvent>
     */
    public function getPostDocumentRetrievedEvent(): array;

    /**
     * @return array<int, PostDocumentUpdateEvent>
     */
    public function getPostDocumentUpdateEvent(): array;

    /**
     * @return array<int, DocumentEventInterface>
     */
    public function getEvents(): array;
}
