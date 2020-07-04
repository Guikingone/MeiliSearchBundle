<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Messenger;

use MeiliSearchBundle\Messenger\DeleteIndexMessage;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DeleteIndexMessageTest extends TestCase
{
    public function testIndexCanBePassed(): void
    {
        $message = new DeleteIndexMessage('foo');

        static::assertSame('foo', $message->getIndex());
    }
}
