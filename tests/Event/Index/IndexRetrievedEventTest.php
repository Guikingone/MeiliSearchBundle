<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event\Index;

use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Index\IndexRetrievedEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexRetrievedEventTest extends TestCase
{
    public function testIndexCanBeRetrieved(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new IndexRetrievedEvent($index);

        static::assertSame($index, $event->getIndex());
    }
}
