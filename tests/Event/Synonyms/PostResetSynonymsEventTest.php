<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event\Synonyms;

use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Synonyms\PostResetSynonymsEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PostResetSynonymsEventTest extends TestCase
{
    public function testEventIsConfigured(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new PostResetSynonymsEvent($index, 1);

        static::assertSame($index, $event->getIndex());
        static::assertSame(1, $event->getUpdate());
    }
}
