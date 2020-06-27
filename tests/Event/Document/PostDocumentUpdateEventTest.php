<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event\Document;

use MeiliSearchBundle\Event\Document\PostDocumentUpdateEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PostDocumentUpdateEventTest extends TestCase
{
    public function testUpdateIdCanBeRetrieved(): void
    {
        $event = new PostDocumentUpdateEvent(1);

        static::assertSame(1, $event->getUpdate());
    }
}
