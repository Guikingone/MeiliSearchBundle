<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event\Synonyms;

use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Synonyms\PostUpdateSynonymsEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PostUpdateSynonymsEventTest extends TestCase
{
    public function testEventIsConfigured(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new PostUpdateSynonymsEvent($index, 1);

        static::assertSame($index, $event->getIndex());
        static::assertSame(1, $event->getUpdate());
    }
}
