<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event\Index;

use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Index\IndexCreatedEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexCreatedEventTest extends TestCase
{
    public function testEventAllowToRetrieveInformations(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new IndexCreatedEvent(['primaryKey' => 'id'], $index);

        static::assertSame($index, $event->getIndex());
        static::assertArrayHasKey('primaryKey', $event->getConfig());
    }
}
