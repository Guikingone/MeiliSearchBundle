<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event\Synonyms;

use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Synonyms\PreResetSynonymsEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PreResetSynonymsEventTest extends TestCase
{
    public function testEventIsConfigured(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new PreResetSynonymsEvent($index);
        static::assertSame($index, $event->getIndex());
    }
}
