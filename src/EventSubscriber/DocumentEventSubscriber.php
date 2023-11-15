<?php

declare(strict_types=1);

namespace MeiliSearchBundle\EventSubscriber;

use MeiliSearchBundle\Event\Document\DocumentEventListInterface;
use MeiliSearchBundle\Event\Document\PostDocumentCreationEvent;
use MeiliSearchBundle\Event\Document\PostDocumentDeletionEvent;
use MeiliSearchBundle\Event\Document\PostDocumentRetrievedEvent;
use MeiliSearchBundle\Event\Document\PostDocumentUpdateEvent;
use MeiliSearchBundle\Event\Document\PreDocumentCreationEvent;
use MeiliSearchBundle\Event\Document\PreDocumentDeletionEvent;
use MeiliSearchBundle\Event\Document\PreDocumentRetrievedEvent;
use MeiliSearchBundle\Event\Document\PreDocumentUpdateEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentEventSubscriber implements EventSubscriberInterface
{
    private const DOCUMENT_LOG_KEY = 'document';

    private const INDEX_LOG_KEY = 'index';

    private const UPDATE_LOG_KEY = 'update';

    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly DocumentEventListInterface $documentEventList,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    public function onPostDocumentCreation(PostDocumentCreationEvent $event): void
    {
        $this->documentEventList->add($event);

        $this->logger->info('A document has been created', [
            self::INDEX_LOG_KEY => $event->getIndex()->getUid(),
            self::UPDATE_LOG_KEY => $event->getUpdate(),
        ]);
    }

    public function onPostDocumentDeletion(PostDocumentDeletionEvent $event): void
    {
        $this->documentEventList->add($event);

        $this->logger->info('A document has been deleted', [
            self::UPDATE_LOG_KEY => $event->getUpdate(),
        ]);
    }

    public function onPostDocumentRetrieved(PostDocumentRetrievedEvent $event): void
    {
        $this->documentEventList->add($event);

        $this->logger->info('A document has been retrieved', [
            self::INDEX_LOG_KEY => $event->getIndex()->getUid(),
            self::DOCUMENT_LOG_KEY => $event->getDocument(),
        ]);
    }

    public function onPostDocumentUpdate(PostDocumentUpdateEvent $event): void
    {
        $this->documentEventList->add($event);

        $this->logger->info('A document has been updated', [
            self::UPDATE_LOG_KEY => $event->getUpdate(),
        ]);
    }

    public function onPreDocumentCreation(PreDocumentCreationEvent $event): void
    {
        $this->logger->info('A document is about to be created', [
            self::INDEX_LOG_KEY => $event->getIndex()->getUid(),
            self::DOCUMENT_LOG_KEY => $event->getDocument(),
        ]);
    }

    public function onPreDocumentDeletion(PreDocumentDeletionEvent $event): void
    {
        $this->logger->info('A document is about to be deleted', [
            self::INDEX_LOG_KEY => $event->getIndex()->getUid(),
            self::DOCUMENT_LOG_KEY => $event->getDocument(),
        ]);
    }

    public function onPreDocumentRetrieved(PreDocumentRetrievedEvent $event): void
    {
        $this->logger->info('A document is about to be retrieved', [
            self::INDEX_LOG_KEY => $event->getIndex()->getUid(),
            self::DOCUMENT_LOG_KEY => $event->getId(),
        ]);
    }

    public function onPreDocumentUpdate(PreDocumentUpdateEvent $event): void
    {
        $this->logger->info('A document is about to be updated', [
            self::INDEX_LOG_KEY => $event->getIndex()->getUid(),
            self::DOCUMENT_LOG_KEY => $event->getDocument(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PostDocumentCreationEvent::class => 'onPostDocumentCreation',
            PostDocumentDeletionEvent::class => 'onPostDocumentDeletion',
            PostDocumentRetrievedEvent::class => 'onPostDocumentRetrieved',
            PostDocumentUpdateEvent::class => 'onPostDocumentUpdate',
            PreDocumentCreationEvent::class => 'onPreDocumentCreation',
            PreDocumentDeletionEvent::class => 'onPreDocumentDeletion',
            PreDocumentRetrievedEvent::class => 'onPreDocumentRetrieved',
            PreDocumentUpdateEvent::class => 'onPreDocumentUpdate',
        ];
    }
}
