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
        $update = Update::create('processed', 1, [], 0.0, '2020-08-01', '2020-08-01');

        static::assertSame('processed', $update->getStatus());
        static::assertSame(1, $update->getUpdateId());
        static::assertEmpty($update->getType());
        static::assertSame(0.0, $update->getDuration());
        static::assertSame('2020-08-01', $update->getEnqueuedAt());
        static::assertSame('2020-08-01', $update->getProcessedAt());
    }
}
