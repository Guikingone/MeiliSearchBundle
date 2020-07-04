<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\EventSubscriber;

use MeiliSearch\Endpoints\Indexes;
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

    public function testSubscriberCanListenOnPostDocumentCreationWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $event = new PostDocumentCreationEvent($index, 1);

        $subscriber = new DocumentEventSubscriber();
        $subscriber->onPostDocumentCreation($event);
    }

    public function testSubscriberCanListenOnPostDocumentCreation(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $event = new PostDocumentCreationEvent($index, 1);

        $subscriber = new DocumentEventSubscriber($logger);
        $subscriber->onPostDocumentCreation($event);
    }

    public function testSubscriberCanListenOnPostDocumentDeletionWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $event = new PostDocumentDeletionEvent(1);

        $subscriber = new DocumentEventSubscriber();
        $subscriber->onPostDocumentDeletion($event);
    }

    public function testSubscriberCanListenOnPostDocumentDeletion(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $event = new PostDocumentDeletionEvent(1);

        $subscriber = new DocumentEventSubscriber($logger);
        $subscriber->onPostDocumentDeletion($event);
    }

    public function testSubscriberCanListenOnPostDocumentRetrieveWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $event = new PostDocumentRetrievedEvent($index, [
            'id' => 1,
            'title' => 'foo',
        ]);

        $subscriber = new DocumentEventSubscriber();
        $subscriber->onPostDocumentRetrieved($event);
    }

    public function testSubscriberCanListenOnPostDocumentRetrieve(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $event = new PostDocumentRetrievedEvent($index, [
            'id' => 1,
            'title' => 'foo',
        ]);

        $subscriber = new DocumentEventSubscriber($logger);
        $subscriber->onPostDocumentRetrieved($event);
    }

    public function testSubscriberCanListenOnPostDocumentUpdateWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $event = new PostDocumentUpdateEvent(1);

        $subscriber = new DocumentEventSubscriber();
        $subscriber->onPostDocumentUpdate($event);
    }

    public function testSubscriberCanListenOnPostDocumentUpdate(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $event = new PostDocumentUpdateEvent(1);

        $subscriber = new DocumentEventSubscriber($logger);
        $subscriber->onPostDocumentUpdate($event);
    }

    public function testSubscriberCanListenOnPreDocumentCreationWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('bar');

        $event = new PreDocumentCreationEvent($index, [
            'id' => 1,
            'title' => 'foo',
        ]);

        $subscriber = new DocumentEventSubscriber();
        $subscriber->onPreDocumentCreation($event);
    }

    public function testSubscriberCanListenOnPreDocumentCreation(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('bar');

        $event = new PreDocumentCreationEvent($index, [
            'id' => 1,
            'title' => 'foo',
        ]);

        $subscriber = new DocumentEventSubscriber($logger);
        $subscriber->onPreDocumentCreation($event);
    }

    public function testSubscriberCanListenOnPreDocumentDeletionWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('bar');

        $event = new PreDocumentDeletionEvent($index, [
            'id' => 1,
            'title' => 'foo',
        ]);

        $subscriber = new DocumentEventSubscriber();
        $subscriber->onPreDocumentDeletion($event);
    }

    public function testSubscriberCanListenOnPreDocumentDeletion(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('bar');

        $event = new PreDocumentDeletionEvent($index, [
            'id' => 1,
            'title' => 'foo',
        ]);

        $subscriber = new DocumentEventSubscriber($logger);
        $subscriber->onPreDocumentDeletion($event);
    }

    public function testSubscriberCanListenOnPreDocumentRetrieveWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $event = new PreDocumentRetrievedEvent($index, 1);

        $subscriber = new DocumentEventSubscriber();
        $subscriber->onPreDocumentRetrieved($event);
    }

    public function testSubscriberCanListenOnPreDocumentRetrieve(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $event = new PreDocumentRetrievedEvent($index, 1);

        $subscriber = new DocumentEventSubscriber($logger);
        $subscriber->onPreDocumentRetrieved($event);
    }

    public function testSubscriberCanListenOnPreDocumentUpdateWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('bar');

        $event = new PreDocumentUpdateEvent($index, [
            'id' => 1,
            'title' => 'foo',
        ]);

        $subscriber = new DocumentEventSubscriber();
        $subscriber->onPreDocumentUpdate($event);
    }

    public function testSubscriberCanListenOnPreDocumentUpdate(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('bar');

        $event = new PreDocumentUpdateEvent($index, [
            'id' => 1,
            'title' => 'foo',
        ]);

        $subscriber = new DocumentEventSubscriber($logger);
        $subscriber->onPreDocumentUpdate($event);
    }
}
