<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event\Document;

use MeiliSearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Document\DocumentEventInterface;
use MeiliSearchBundle\Event\Document\DocumentEventList;
use MeiliSearchBundle\Event\Document\PostDocumentCreationEvent;
use MeiliSearchBundle\Event\Document\PostDocumentDeletionEvent;
use MeiliSearchBundle\Event\Document\PostDocumentRetrievedEvent;
use MeiliSearchBundle\Event\Document\PostDocumentUpdateEvent;
use PHPUnit\Framework\TestCase;
use function count;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentEventListTest extends TestCase
{
    public function testEventsCanBeCounted(): void
    {
        $event = $this->createMock(DocumentEventInterface::class);

        $list = new DocumentEventList();

        $list->add($event);

        static::assertNotEmpty($list->getEvents());
        static::assertSame(1, $list->count());
    }

    public function testPostDocumentCreationEventCanBeRetrieved(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new PostDocumentCreationEvent($index, 1);
        $secondEvent = $this->createMock(DocumentEventInterface::class);

        $list = new DocumentEventList();

        $list->add($event);
        $list->add($secondEvent);

        static::assertNotEmpty($list->getPostDocumentCreationEvent());
        static::assertSame(1, count($list->getPostDocumentCreationEvent()));
    }

    public function testPostDocumentDeletionEventCanBeRetrieved(): void
    {
        $event = new PostDocumentDeletionEvent(1);
        $secondEvent = $this->createMock(DocumentEventInterface::class);

        $list = new DocumentEventList();

        $list->add($event);
        $list->add($secondEvent);

        static::assertNotEmpty($list->getPostDocumentDeletionEvent());
        static::assertSame(1, count($list->getPostDocumentDeletionEvent()));
    }

    public function testPostDocumentRetrievedEventCanBeRetrieved(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new PostDocumentRetrievedEvent($index, []);
        $secondEvent = $this->createMock(DocumentEventInterface::class);

        $list = new DocumentEventList();

        $list->add($event);
        $list->add($secondEvent);

        static::assertNotEmpty($list->getPostDocumentRetrievedEvent());
        static::assertSame(1, count($list->getPostDocumentRetrievedEvent()));
    }

    public function testPostDocumentUpdateEventCanBeRetrieved(): void
    {
        $event = new PostDocumentUpdateEvent(1);
        $secondEvent = $this->createMock(DocumentEventInterface::class);

        $list = new DocumentEventList();

        $list->add($event);
        $list->add($secondEvent);

        static::assertNotEmpty($list->getPostDocumentUpdateEvent());
        static::assertSame(1, count($list->getPostDocumentUpdateEvent()));
    }
}
