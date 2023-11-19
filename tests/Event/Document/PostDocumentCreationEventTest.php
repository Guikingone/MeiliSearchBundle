<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event\Document;

use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Document\PostDocumentCreationEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PostDocumentCreationEventTest extends TestCase
{
    public function testEventAllowToRetrieveInformations(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new PostDocumentCreationEvent($index, 1);

        static::assertSame($index, $event->getIndex());
        static::assertSame(1, $event->getUpdate());
    }
}
