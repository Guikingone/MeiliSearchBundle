<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event\Document;

use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Document\PostDocumentRetrievedEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PostDocumentRetrievedEventTest extends TestCase
{
    public function testEventCanBeConfigured(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new PostDocumentRetrievedEvent($index, [
            'id' => 1,
            'title' => 'foo',
        ]);

        static::assertSame($index, $event->getIndex());
        static::assertNotEmpty($event->getDocument());
        static::assertArrayHasKey('id', $event->getDocument());
        static::assertArrayHasKey('title', $event->getDocument());
    }
}
