<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\EventSubscriber;

use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Document\DocumentEventListInterface;
use MeiliSearchBundle\Event\Document\PostDocumentCreationEvent;
use MeiliSearchBundle\Event\Document\PostDocumentDeletionEvent;
use MeiliSearchBundle\Event\Document\PostDocumentRetrievedEvent;
use MeiliSearchBundle\Event\Document\PostDocumentUpdateEvent;
use MeiliSearchBundle\Event\Document\PreDocumentCreationEvent;
use MeiliSearchBundle\Event\Document\PreDocumentDeletionEvent;
use MeiliSearchBundle\Event\Document\PreDocumentRetrievedEvent;
use MeiliSearchBundle\Event\Document\PreDocumentUpdateEvent;
use MeiliSearchBundle\EventSubscriber\DocumentEventSubscriber;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentEventSubscriberTest extends TestCase
{
    public function testSubscriberIsConfigured(): void
    {
        static::assertArrayHasKey(PostDocumentCreationEvent::class, DocumentEventSubscriber::getSubscribedEvents());
        static::assertArrayHasKey(PostDocumentDeletionEvent::class, DocumentEventSubscriber::getSubscribedEvents());
        static::assertArrayHasKey(PostDocumentUpdateEvent::class, DocumentEventSubscriber::getSubscribedEvents());
        static::assertArrayHasKey(PreDocumentCreationEvent::class, DocumentEventSubscriber::getSubscribedEvents());
        static::assertArrayHasKey(PreDocumentDeletionEvent::class, DocumentEventSubscriber::getSubscribedEvents());
        static::assertArrayHasKey(PreDocumentUpdateEvent::class, DocumentEventSubscriber::getSubscribedEvents());
    }

    public function testSubscriberCanListenOnPostDocumentCreation(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(self::equalTo('A document has been created'), [
            'index' => 'foo',
            'update' => 1,
        ]);

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $event = new PostDocumentCreationEvent($index, 1);

        $list = $this->createMock(DocumentEventListInterface::class);
        $list->expects(self::once())->method('add')->with($event);

        $subscriber = new DocumentEventSubscriber($list, $logger);
        $subscriber->onPostDocumentCreation($event);
    }

    public function testSubscriberCanListenOnPostDocumentDeletion(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(self::equalTo('A document has been deleted'), [
            'update' => 1,
        ]);

        $event = new PostDocumentDeletionEvent(1);

        $list = $this->createMock(DocumentEventListInterface::class);
        $list->expects(self::once())->method('add')->with($event);

        $subscriber = new DocumentEventSubscriber($list, $logger);
        $subscriber->onPostDocumentDeletion($event);
    }

    public function testSubscriberCanListenOnPostDocumentRetrieve(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(self::equalTo('A document has been retrieved'), [
            'index' => 'foo',
            'document' => [
                'id' => 1,
                'title' => 'foo',
            ],
        ]);

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $event = new PostDocumentRetrievedEvent($index, [
            'id' => 1,
            'title' => 'foo',
        ]);

        $list = $this->createMock(DocumentEventListInterface::class);
        $list->expects(self::once())->method('add')->with($event);

        $subscriber = new DocumentEventSubscriber($list, $logger);
        $subscriber->onPostDocumentRetrieved($event);
    }

    public function testSubscriberCanListenOnPostDocumentUpdate(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(self::equalTo('A document has been updated'), [
            'update' => 1,
        ]);

        $event = new PostDocumentUpdateEvent(1);

        $list = $this->createMock(DocumentEventListInterface::class);
        $list->expects(self::once())->method('add')->with($event);

        $subscriber = new DocumentEventSubscriber($list, $logger);
        $subscriber->onPostDocumentUpdate($event);
    }

    public function testSubscriberCanListenOnPreDocumentCreation(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(self::equalTo('A document is about to be created'), [
            'index' => 'bar',
            'document' => [
                'id' => 1,
                'title' => 'foo',
            ],
        ]);

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('bar');

        $event = new PreDocumentCreationEvent($index, [
            'id' => 1,
            'title' => 'foo',
        ]);

        $list = $this->createMock(DocumentEventListInterface::class);

        $subscriber = new DocumentEventSubscriber($list, $logger);
        $subscriber->onPreDocumentCreation($event);
    }

    public function testSubscriberCanListenOnPreDocumentDeletion(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(self::equalTo('A document is about to be deleted'), [
            'index' => 'bar',
            'document' => [
                'id' => 1,
                'title' => 'foo',
            ],
        ]);

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('bar');

        $event = new PreDocumentDeletionEvent($index, [
            'id' => 1,
            'title' => 'foo',
        ]);

        $list = $this->createMock(DocumentEventListInterface::class);

        $subscriber = new DocumentEventSubscriber($list, $logger);
        $subscriber->onPreDocumentDeletion($event);
    }

    public function testSubscriberCanListenOnPreDocumentRetrieve(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(self::equalTo('A document is about to be retrieved'), [
            'index' => 'foo',
            'document' => 1,
        ]);

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $event = new PreDocumentRetrievedEvent($index, 1);

        $list = $this->createMock(DocumentEventListInterface::class);

        $subscriber = new DocumentEventSubscriber($list, $logger);
        $subscriber->onPreDocumentRetrieved($event);
    }

    public function testSubscriberCanListenOnPreDocumentUpdate(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(self::equalTo('A document is about to be updated'), [
            'index' => 'bar',
            'document' => [
                'id' => 1,
                'title' => 'foo',
            ],
        ]);

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('bar');

        $event = new PreDocumentUpdateEvent($index, [
            'id' => 1,
            'title' => 'foo',
        ]);

        $list = $this->createMock(DocumentEventListInterface::class);

        $subscriber = new DocumentEventSubscriber($list, $logger);
        $subscriber->onPreDocumentUpdate($event);
    }
}
