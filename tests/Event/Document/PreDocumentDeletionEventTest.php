<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event\Document;

use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Document\PreDocumentDeletionEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PreDocumentDeletionEventTest extends TestCase
{
    public function testEventAllowToRetrieveInformations(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new PreDocumentDeletionEvent($index, [
            'id' => 'foo',
            'key' => 'bar',
        ]);

        static::assertSame($index, $event->getIndex());
        static::assertArrayHasKey('id', $event->getDocument());
        static::assertArrayHasKey('key', $event->getDocument());
    }
}
