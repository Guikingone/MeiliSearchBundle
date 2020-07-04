<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Metadata;

use MeiliSearchBundle\Metadata\IndexMetadata;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexMetadataTest extends TestCase
{
    public function testMetadataCanBeRetrieved(): void
    {
        $metadata = new IndexMetadata('foo', true, 'id');

        static::assertSame('foo', $metadata->getUid());
        static::assertTrue($metadata->isAsync());
        static::assertSame('id', $metadata->getPrimaryKey());
        static::assertEmpty($metadata->getRankingRules());
        static::assertEmpty($metadata->getStopWords());
        static::assertTrue($metadata->acceptNewFields());
        static::assertEmpty($metadata->getFacetedAttributes());
        static::assertEmpty($metadata->getSearchableAttributes());
        static::assertEmpty($metadata->getDisplayedAttributes());
        static::assertNull($metadata->getDistinctAttribute());
    }
}
