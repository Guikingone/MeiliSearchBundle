<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Update;

use MeiliSearchBundle\Update\Update;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class UpdateTest extends TestCase
{
    public function testUpdatesInformationsCanBeRetrieved(): void
    {
        $update = Update::create(
            uid: 1,
            indexUid: 'movies',
            status: 'processed',
            type: 'indexCreation',
            canceledBy: null,
            details: [
                'receivedDocuments' => 1,
                'indexedDocuments' => 2,
            ],
            error: null,
            duration: 'PT0.000400211S',
            enqueuedAt: '2023-11-11T22:00:00.00000003Z',
            startedAt: '2023-11-11T22:00:01.00000003Z',
            finishedAt: '2023-11-11T22:00:02.00000003Z',
        );

        static::assertSame(1, $update->getUid());
        static::assertSame('movies', $update->getIndexUid());
        static::assertSame('processed', $update->getStatus());
        static::assertSame('indexCreation', $update->getType());
        static::assertNull($update->getCanceledBy());
        static::assertIsArray($update->getDetails());
        static::assertArrayHasKey('receivedDocuments', $update->getDetails());
        static::assertArrayHasKey('indexedDocuments', $update->getDetails());
        static::assertNull($update->getError());
        static::assertIsString($update->getDuration());
        static::assertSame('PT0.000400211S', $update->getDuration());
        static::assertSame('2023-11-11T22:00:00.00000003Z', $update->getEnqueuedAt());
        static::assertSame('2023-11-11T22:00:01.00000003Z', $update->getStartedAt());
        static::assertSame('2023-11-11T22:00:02.00000003Z', $update->getFinishedAt());
    }
}
