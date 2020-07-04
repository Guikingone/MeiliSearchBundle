<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Messenger\Document;

use MeiliSearchBundle\Messenger\Document\UpdateDocumentMessage;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class UpdateDocumentMessageTest extends TestCase
{
    public function testMessageCanBeConfigured(): void
    {
        $message = new UpdateDocumentMessage('foo', [
            'id' => 1,
            'title' => 'foo',
        ], 'id');

        static::assertSame('foo', $message->getIndex());
        static::assertArrayHasKey('id', $message->getDocumentUpdate());
        static::assertArrayHasKey('title', $message->getDocumentUpdate());
        static::assertSame('id', $message->getPrimaryKey());
    }
}
