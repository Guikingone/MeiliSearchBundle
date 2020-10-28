<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event\Index;

use MeiliSearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Index\IndexCreatedEvent;
use MeiliSearchBundle\Event\Index\IndexEventInterface;
use MeiliSearchBundle\Event\Index\IndexEventList;
use MeiliSearchBundle\Event\Index\IndexRemovedEvent;
use MeiliSearchBundle\Event\Index\IndexRetrievedEvent;
use MeiliSearchBundle\Event\Index\PostSettingsUpdateEvent;
use MeiliSearchBundle\Event\Index\PreSettingsUpdateEvent;
use PHPUnit\Framework\TestCase;
use function count;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexEventListTest extends TestCase
{
    public function testEventsCanBeCounted(): void
    {
        $event = $this->createMock(IndexEventInterface::class);

        $list = new IndexEventList();

        $list->add($event);

        static::assertNotEmpty($list->getEvents());
        static::assertSame(1, $list->count());
    }

    public function testIndexCreatedEventCanBeRetrieved(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new IndexCreatedEvent([], $index);
        $indexRemovedEvent = new IndexRemovedEvent('1');

        $list = new IndexEventList();

        $list->add($event);
        $list->add($indexRemovedEvent);

        static::assertNotEmpty($list->getIndexCreatedEvents());
        static::assertSame(1, count($list->getIndexCreatedEvents()));
        static::assertNotContains($indexRemovedEvent, $list->getIndexCreatedEvents());
        static::assertSame([$event], $list->getIndexCreatedEvents());
    }

    public function testIndexRemovedEventCanBeRetrieved(): void
    {
        $event = new IndexRemovedEvent('1');

        $list = new IndexEventList();

        $list->add($event);

        static::assertNotEmpty($list->getIndexRemovedEvents());
    }

    public function testIndexRetrievedEventCanBeRetrieved(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new IndexRetrievedEvent($index);

        $list = new IndexEventList();

        $list->add($event);

        static::assertNotEmpty($list->getIndexRetrievedEvents());
    }

    public function testPostSettingsUpdateEventCanBeRetrieved(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new PostSettingsUpdateEvent($index, 1);

        $list = new IndexEventList();

        $list->add($event);

        static::assertNotEmpty($list->getPostSettingsUpdateEvents());
    }

    public function testPreSettingsUpdateEventCanBeRetrieved(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new PreSettingsUpdateEvent($index, []);

        $list = new IndexEventList();

        $list->add($event);

        static::assertNotEmpty($list->getPreSettingsUpdateEvents());
    }
}
