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
        static::assertArrayHasKey('distinctAttribute', $message->getConfiguration());
        static::assertArrayHasKey('facetedAttributes', $message->getConfiguration());
        static::assertArrayHasKey('searchableAttributes', $message->getConfiguration());
        static::assertArrayHasKey('displayedAttributes', $message->getConfiguration());
        static::assertArrayHasKey('rankingRules', $message->getConfiguration());
        static::assertArrayHasKey('stopWords', $message->getConfiguration());
        static::assertArrayHasKey('synonyms', $message->getConfiguration());
    }

    public function testIndexDataCanBeRetrieved(): void
    {
        $message = new AddIndexMessage('foo', 'id');

        static::assertSame('foo', $message->getUid());
        static::assertSame('id', $message->getPrimaryKey());
    }
}
