<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Health;

use Meilisearch\Client;
use MeiliSearchBundle\Health\HealthEntryPoint;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class HealthEntryPointTest extends TestCase
{
    public function testInstanceIsNotUp(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('stats')->willReturn([
            'databaseSize' => 102400,
            'lastUpdate' => null,
            'indexes' => [],
        ]);

        $entryPoint = new HealthEntryPoint($client);

        static::assertFalse($entryPoint->isUp());
    }

    public function testInstanceIsUp(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('stats')->willReturn([
            'databaseSize' => 102400,
            'lastUpdate' => null,
            'indexes' => [
                'bar' => [
                    'numberOfDocuments' => 0,
                    'isIndexing' => false,
                    'fieldsDistribution' => [],
                ],
                'foo' => [
                    'numberOfDocuments' => 0,
                    'isIndexing' => false,
                    'fieldsDistribution' => [],
                ],
            ],
        ]);

        $entryPoint = new HealthEntryPoint($client);

        static::assertTrue($entryPoint->isUp());
    }
}
