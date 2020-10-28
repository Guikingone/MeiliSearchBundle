<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event;

use MeiliSearchBundle\Event\PostSearchEvent;
use MeiliSearchBundle\Event\PreSearchEvent;
use MeiliSearchBundle\Event\SearchEventInterface;
use MeiliSearchBundle\Event\SearchEventList;
use MeiliSearchBundle\Search\SearchResultInterface;
use PHPUnit\Framework\TestCase;
use function count;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SearchEventListTest extends TestCase
{
    public function testEventsCanBeCounted(): void
    {
        $event = $this->createMock(SearchEventInterface::class);

        $list = new SearchEventList();

        $list->add($event);

        static::assertNotEmpty($list->getEvents());
        static::assertSame(1, $list->count());
    }

    public function testPreSearchEventCanBeRetrieved(): void
    {
        $result = $this->createMock(SearchResultInterface::class);

        $event = new PostSearchEvent($result);
        $secondEvent = $this->createMock(SearchEventInterface::class);

        $list = new SearchEventList();

        $list->add($event);
        $list->add($secondEvent);

        static::assertNotEmpty($list->getPostSearchEvents());
        static::assertSame(1, count($list->getPostSearchEvents()));
    }

    public function testIndexRemovedEventCanBeRetrieved(): void
    {
        $event = new PreSearchEvent([]);
        $secondEvent = $this->createMock(SearchEventInterface::class);

        $list = new SearchEventList();

        $list->add($event);
        $list->add($secondEvent);

        static::assertNotEmpty($list->getPreSearchEvents());
        static::assertSame(1, count($list->getPreSearchEvents()));
    }
}
