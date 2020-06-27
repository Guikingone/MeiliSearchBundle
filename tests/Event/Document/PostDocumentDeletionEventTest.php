<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event\Document;

use MeiliSearchBundle\Event\Document\PostDocumentDeletionEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PostDocumentDeletionEventTest extends TestCase
{
    public function testUpdateIdCanBeRetrieved(): void
    {
        $event = new PostDocumentDeletionEvent(1);

        static::assertSame(1, $event->getUpdate());
    }
}
