<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event\Dump;

use MeiliSearchBundle\Event\Dump\DumpCreatedEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DumpCreatedEventTest extends TestCase
{
    public function testUidCanBeRetrieved(): void
    {
        $event = new DumpCreatedEvent('123');

        static::assertSame('123', $event->getUid());
    }
}
