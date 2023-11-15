<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event\Synonyms;

use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Synonyms\PreUpdateSynonymsEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PreUpdateSynonymsEventTest extends TestCase
{
    public function testEventIsConfigured(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new PreUpdateSynonymsEvent($index, [
            'logan' => ['wolverine', 'xmen'],
        ]);

        static::assertSame($index, $event->getIndex());
        static::assertArrayHasKey('logan', $event->getSynonyms());
    }
}
