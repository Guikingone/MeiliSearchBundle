<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event\Index;

use MeiliSearchBundle\Event\Index\IndexRemovedEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexRemovedEventTest extends TestCase
{
    public function testIndexCanBeRetrieved(): void
    {
        $event = new IndexRemovedEvent('foo');

        static::assertSame('foo', $event->getUid());
    }
}
