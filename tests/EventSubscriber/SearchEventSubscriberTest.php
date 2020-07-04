<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\EventSubscriber;

use MeiliSearchBundle\Event\PostSearchEvent;
use MeiliSearchBundle\Event\PreSearchEvent;
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
        static::assertInstanceOf(MeiliSearchEventSubscriberInterface::class, new SearchEventSubscriber());
    }

    public function testSubscriberCanListenPostSearchWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $searchResult = $this->createMock(SearchResultInterface::class);
        $searchResult->expects(self::once())->method('toArray');

        $event = new PostSearchEvent($searchResult);

        $subscriber = new SearchEventSubscriber();
        $subscriber->onPostSearchEvent($event);
    }

    public function testSubscriberCanListenPostSearch(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $searchResult = $this->createMock(SearchResultInterface::class);
        $searchResult->expects(self::once())->method('toArray');

        $event = new PostSearchEvent($searchResult);

        $subscriber = new SearchEventSubscriber($logger);
        $subscriber->onPostSearchEvent($event);
    }

    public function testSubscriberCanListenPreSearchWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $event = new PreSearchEvent([]);

        $subscriber = new SearchEventSubscriber();
        $subscriber->onPreSearchEvent($event);
    }

    public function testSubscriberCanListenPreSearch(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $event = new PreSearchEvent([]);

        $subscriber = new SearchEventSubscriber($logger);
        $subscriber->onPreSearchEvent($event);
    }
}
