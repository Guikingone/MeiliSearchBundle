<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\EventSubscriber;

use MeiliSearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Index\IndexCreatedEvent;
use MeiliSearchBundle\Event\Index\IndexRemovedEvent;
use MeiliSearchBundle\Event\Index\IndexRetrievedEvent;
use MeiliSearchBundle\EventSubscriber\IndexEventSubscriber;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexEventSubscriberTest extends TestCase
{
    public function testSubscriberIsConfigured(): void
    {
        static::assertArrayHasKey(IndexCreatedEvent::class, IndexEventSubscriber::getSubscribedEvents());
        static::assertArrayHasKey(IndexRemovedEvent::class, IndexEventSubscriber::getSubscribedEvents());
        static::assertArrayHasKey(IndexRetrievedEvent::class, IndexEventSubscriber::getSubscribedEvents());
    }

    public function testSubscriberCanListenToIndexCreationWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $index = $this->createMock(Indexes::class);

        $event = new IndexCreatedEvent([], $index);

        $subscriber = new IndexEventSubscriber();
        $subscriber->onIndexCreatedEvent($event);
    }

    public function testSubscriberCanListenToIndexCreationWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $index = $this->createMock(Indexes::class);

        $event = new IndexCreatedEvent([], $index);

        $subscriber = new IndexEventSubscriber($logger);
        $subscriber->onIndexCreatedEvent($event);
    }

    public function testSubscriberCanListenToIndexDeletionWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $event = new IndexRemovedEvent('foo');

        $subscriber = new IndexEventSubscriber();
        $subscriber->onIndexRemovedEvent($event);
    }

    public function testSubscriberCanListenToIndexDeletionWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $event = new IndexRemovedEvent('foo');

        $subscriber = new IndexEventSubscriber($logger);
        $subscriber->onIndexRemovedEvent($event);
    }

    public function testSubscriberCanListenToIndexRetrievedWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $event = new IndexRetrievedEvent($index);

        $subscriber = new IndexEventSubscriber();
        $subscriber->onIndexRetrievedEvent($event);
    }

    public function testSubscriberCanListenToIndexRetrievedWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $event = new IndexRetrievedEvent($index);

        $subscriber = new IndexEventSubscriber($logger);
        $subscriber->onIndexRetrievedEvent($event);
    }
}
