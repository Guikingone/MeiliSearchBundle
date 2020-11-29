<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Document;

use MeiliSearchBundle\Document\DocumentMetadata;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentMetadataTest extends TestCase
{
    public function testMetadataCanBeCreated(): void
    {
        $metadata = new DocumentMetadata('foo', 'object');

        static::assertSame('foo', $metadata->getIndex());
        static::assertSame('object', $metadata->getType());

        $metadata = new DocumentMetadata('foo');

        static::assertSame('foo', $metadata->getIndex());
        static::assertSame('array', $metadata->getType());
    }
}
