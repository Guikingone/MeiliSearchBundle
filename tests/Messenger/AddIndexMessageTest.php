<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Messenger;

use MeiliSearchBundle\Messenger\AddIndexMessage;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class AddIndexMessageTest extends TestCase
{
    public function testIndexDataCanBeRetrievedWithNullPrimaryKey(): void
    {
        $message = new AddIndexMessage('foo');

        static::assertSame('foo', $message->getUid());
        static::assertNull($message->getPrimaryKey());
    }

    public function testIndexDataCanBeRetrieved(): void
    {
        $message = new AddIndexMessage('foo', 'id');

        static::assertSame('foo', $message->getUid());
        static::assertSame('id', $message->getPrimaryKey());
    }
}
