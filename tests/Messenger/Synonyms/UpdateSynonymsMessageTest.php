<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Messenger\Synonyms;

use MeiliSearchBundle\Messenger\Synonyms\UpdateSynonymsMessage;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class UpdateSynonymsMessageTest extends TestCase
{
    public function testMessageCanBeCreated(): void
    {
        $message = new UpdateSynonymsMessage('foo', [
            'logan' => ['xmen', 'wolverine'],
        ]);

        static::assertSame('foo', $message->getIndex());
        static::assertNotEmpty($message->getSynonyms());
        static::assertArrayHasKey('logan', $message->getSynonyms());
    }
}
