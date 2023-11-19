<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event;

use MeiliSearchBundle\Event\PostSearchEvent;
use MeiliSearchBundle\Event\PreSearchEvent;
use MeiliSearchBundle\Event\SearchEventInterface;
use MeiliSearchBundle\Event\SearchEventList;
use MeiliSearchBundle\Search\SearchResultInterface;
use PHPUnit\Framework\TestCase;

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
        static::assertCount(1, $list);
    }

    public function testPostSearchEventCanBeRetrieved(): void
    {
        $result = $this->createMock(SearchResultInterface::class);

        $event = new PostSearchEvent($result);
        $secondEvent = $this->createMock(SearchEventInterface::class);

        $list = new SearchEventList();

        $list->add($event);
        $list->add($secondEvent);

        static::assertNotEmpty($list->getPostSearchEvents());
        static::assertCount(1, $list->getPostSearchEvents());
    }

    public function testPreSearchEventCanBeRetrieved(): void
    {
        $event = new PreSearchEvent([]);
        $secondEvent = $this->createMock(SearchEventInterface::class);

        $list = new SearchEventList();

        $list->add($event);
        $list->add($secondEvent);

        static::assertNotEmpty($list->getPreSearchEvents());
        static::assertCount(1, $list->getPreSearchEvents());
    }
}
