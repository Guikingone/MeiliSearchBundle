<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Messenger\Document;

use MeiliSearchBundle\Messenger\Document\AddDocumentMessage;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class AddDocumentMessageTest extends TestCase
{
    public function testMessageCanBeConfigured(): void
    {
        $message = new AddDocumentMessage('foo', [
            'id' => 1,
            'key' => 'bar',
        ], 'key', Bar::class);

        static::assertSame('foo', $message->getIndex());
        static::assertArrayHasKey('id', $message->getDocument());
        static::assertArrayHasKey('key', $message->getDocument());
        static::assertSame('key', $message->getPrimaryKey());
        static::assertSame(Bar::class, $message->getModel());
    }
}

final class Bar
{
    public int $id;

    public string $key;
}
