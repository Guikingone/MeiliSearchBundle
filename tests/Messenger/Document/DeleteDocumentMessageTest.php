<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Messenger\Document;

use MeiliSearchBundle\Messenger\Document\DeleteDocumentMessage;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DeleteDocumentMessageTest extends TestCase
{
    public function testMessageCanBeConfigured(): void
    {
        $message = new DeleteDocumentMessage('foo', 1);

        static::assertSame('foo', $message->getIndex());
        static::assertSame(1, $message->getDocumentIdentifier());
    }
}
