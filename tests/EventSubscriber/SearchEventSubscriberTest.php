<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\EventSubscriber;

use MeiliSearchBundle\Event\PostSearchEvent;
use MeiliSearchBundle\Event\PreSearchEvent;
use MeiliSearchBundle\Event\SearchEventList;
use MeiliSearchBundle\Event\SearchEventListInterface;
use MeiliSearchBundle\EventSubscriber\MeiliSearchEventSubscriberInterface;
use MeiliSearchBundle\EventSubscriber\SearchEventSubscriber;
use MeiliSearchBundle\Search\SearchResultInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SearchEventSubscriberTest extends TestCase
{
    public function testSubscriberListenEvents(): void
    {
        static::assertArrayHasKey(PostSearchEvent::class, SearchEventSubscriber::getSubscribedEvents());
        static::assertArrayHasKey(PreSearchEvent::class, SearchEventSubscriber::getSubscribedEvents());
        static::assertInstanceOf(
            MeiliSearchEventSubscriberInterface::class,
            new SearchEventSubscriber(new SearchEventList())
        );
    }

    public function testSubscriberCanListenPostSearchWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $searchResult = $this->createMock(SearchResultInterface::class);
        $searchResult->expects(self::once())->method('toArray');

        $event = new PostSearchEvent($searchResult);

        $list = $this->createMock(SearchEventListInterface::class);
        $list->expects(self::once())->method('add')->with($event);

        $subscriber = new SearchEventSubscriber($list);
        $subscriber->onPostSearchEvent($event);
    }

    public function testSubscriberCanListenPostSearch(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(self::equalTo('[MeiliSearch] A search has been made'), [
            'results' => [],
        ]);

        $searchResult = $this->createMock(SearchResultInterface::class);
        $searchResult->expects(self::once())->method('toArray')->willReturn([]);

        $event = new PostSearchEvent($searchResult);

        $list = $this->createMock(SearchEventListInterface::class);
        $list->expects(self::once())->method('add')->with($event);

        $subscriber = new SearchEventSubscriber($list, $logger);
        $subscriber->onPostSearchEvent($event);
    }

    public function testSubscriberCanListenPreSearchWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $event = new PreSearchEvent([]);

        $list = $this->createMock(SearchEventListInterface::class);
        $list->expects(self::once())->method('add')->with($event);

        $subscriber = new SearchEventSubscriber($list);
        $subscriber->onPreSearchEvent($event);
    }

    public function testSubscriberCanListenPreSearch(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(
            self::equalTo('[MeiliSearch] A search is about to be made'),
            [
                'configuration' => [],
            ]
        );

        $event = new PreSearchEvent([]);

        $list = $this->createMock(SearchEventListInterface::class);
        $list->expects(self::once())->method('add')->with($event);

        $subscriber = new SearchEventSubscriber($list, $logger);
        $subscriber->onPreSearchEvent($event);
    }
}
