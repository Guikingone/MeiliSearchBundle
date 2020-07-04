<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Messenger\Synonyms;

use MeiliSearchBundle\Messenger\Synonyms\ResetSynonymsMessage;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ResetSynonymsMessageTest extends TestCase
{
    public function testMessageIsConfigured(): void
    {
        $message = new ResetSynonymsMessage('foo');

        static::assertSame('foo', $message->getIndex());
    }
}
