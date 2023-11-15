<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event\Index;

use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Index\PreSettingsUpdateEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PreSettingsUpdateEventTest extends TestCase
{
    public function testEventCanBeConfigured(): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new PreSettingsUpdateEvent($index, [
            'rankingRules' => [],
        ]);

        static::assertSame($index, $event->getIndex());
        static::assertArrayHasKey('rankingRules', $event->getUpdatePayload());
    }
}
