<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\EventSubscriber;

use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Index\IndexCreatedEvent;
use MeiliSearchBundle\Event\Index\IndexEventListInterface;
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

        $list = $this->createMock(IndexEventListInterface::class);
        $list->expects(self::once())->method('add')->with($event);

        $subscriber = new IndexEventSubscriber($list);
        $subscriber->onIndexCreatedEvent($event);
    }

    public function testSubscriberCanListenToIndexCreationWithLogger(): void
    {
        $index = $this->createMock(Indexes::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(self::equalTo('[MeiliSearch] An index has been created'), [
            'index' => $index,
            'configuration' => [],
        ]);

        $event = new IndexCreatedEvent([], $index);

        $list = $this->createMock(IndexEventListInterface::class);
        $list->expects(self::once())->method('add')->with($event);

        $subscriber = new IndexEventSubscriber($list, $logger);
        $subscriber->onIndexCreatedEvent($event);
    }

    public function testSubscriberCanListenToIndexDeletionWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $event = new IndexRemovedEvent('foo');

        $list = $this->createMock(IndexEventListInterface::class);
        $list->expects(self::once())->method('add')->with($event);

        $subscriber = new IndexEventSubscriber($list);
        $subscriber->onIndexRemovedEvent($event);
    }

    public function testSubscriberCanListenToIndexDeletionWithLogger(): void
    {
        $event = new IndexRemovedEvent('foo');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(self::equalTo('[MeiliSearch] An index has been removed'), [
            'index' => 'foo',
        ]);

        $list = $this->createMock(IndexEventListInterface::class);
        $list->expects(self::once())->method('add')->with($event);

        $subscriber = new IndexEventSubscriber($list, $logger);
        $subscriber->onIndexRemovedEvent($event);
    }

    public function testSubscriberCanListenToIndexRetrievedWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $event = new IndexRetrievedEvent($index);

        $list = $this->createMock(IndexEventListInterface::class);
        $list->expects(self::once())->method('add')->with($event);

        $subscriber = new IndexEventSubscriber($list);
        $subscriber->onIndexRetrievedEvent($event);
    }

    public function testSubscriberCanListenToIndexRetrievedWithLogger(): void
    {
        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(
            self::equalTo('[MeiliSearch] An index has been retrieved'),
            [
                'index' => 'foo',
            ]
        );

        $event = new IndexRetrievedEvent($index);

        $list = $this->createMock(IndexEventListInterface::class);
        $list->expects(self::once())->method('add')->with($event);

        $subscriber = new IndexEventSubscriber($list, $logger);
        $subscriber->onIndexRetrievedEvent($event);
    }
}
