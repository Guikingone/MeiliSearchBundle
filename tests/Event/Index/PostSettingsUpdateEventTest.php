<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event\Index;

use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Index\PostSettingsUpdateEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PostSettingsUpdateEventTest extends TestCase
{
    public function testEventCanBeConfigured(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new PostSettingsUpdateEvent($index, 1);

        static::assertSame($index, $event->getIndex());
        static::assertSame(1, $event->getUpdate());
    }
}
