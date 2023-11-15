<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event\Index;

use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Index\IndexCreatedEvent;
use MeiliSearchBundle\Event\Index\IndexEventInterface;
use MeiliSearchBundle\Event\Index\IndexEventList;
use MeiliSearchBundle\Event\Index\IndexRemovedEvent;
use MeiliSearchBundle\Event\Index\IndexRetrievedEvent;
use MeiliSearchBundle\Event\Index\PostSettingsUpdateEvent;
use MeiliSearchBundle\Event\Index\PreSettingsUpdateEvent;
use PHPUnit\Framework\TestCase;

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
        static::assertCount(1, $list);
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
        static::assertCount(1, $list->getIndexCreatedEvents());
        static::assertNotContains($indexRemovedEvent, $list->getIndexCreatedEvents());
        static::assertSame([$event], $list->getIndexCreatedEvents());
    }

    public function testIndexRemovedEventCanBeRetrieved(): void
    {
        $event = new IndexRemovedEvent('1');
        $secondEvent = $this->createMock(IndexEventInterface::class);

        $list = new IndexEventList();

        $list->add($event);
        $list->add($secondEvent);

        static::assertNotEmpty($list->getIndexRemovedEvents());
        static::assertNotContains($secondEvent, $list->getIndexRemovedEvents());
    }

    public function testIndexRetrievedEventCanBeRetrieved(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new IndexRetrievedEvent($index);
        $secondEvent = $this->createMock(IndexEventInterface::class);

        $list = new IndexEventList();

        $list->add($event);
        $list->add($secondEvent);

        static::assertNotEmpty($list->getIndexRetrievedEvents());
        static::assertNotContains($secondEvent, $list->getIndexRetrievedEvents());
    }

    public function testPostSettingsUpdateEventCanBeRetrieved(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new PostSettingsUpdateEvent($index, 1);
        $secondEvent = $this->createMock(IndexEventInterface::class);

        $list = new IndexEventList();

        $list->add($event);
        $list->add($secondEvent);

        static::assertNotEmpty($list->getPostSettingsUpdateEvents());
        static::assertNotContains($secondEvent, $list->getPostSettingsUpdateEvents());
    }

    public function testPreSettingsUpdateEventCanBeRetrieved(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new PreSettingsUpdateEvent($index, []);
        $secondEvent = $this->createMock(IndexEventInterface::class);

        $list = new IndexEventList();

        $list->add($event);
        $list->add($secondEvent);

        static::assertNotEmpty($list->getPreSettingsUpdateEvents());
        static::assertNotContains($secondEvent, $list->getPreSettingsUpdateEvents());
    }
}
