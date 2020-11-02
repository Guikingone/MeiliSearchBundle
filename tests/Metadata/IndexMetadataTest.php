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
        $metadata = new IndexMetadata('foo', false, 'id');

        static::assertSame('foo', $metadata->getUid());
        static::assertFalse($metadata->isAsync());
        static::assertSame('id', $metadata->getPrimaryKey());
        static::assertEmpty($metadata->getRankingRules());
        static::assertEmpty($metadata->getStopWords());
        static::assertEmpty($metadata->getFacetedAttributes());
        static::assertEmpty($metadata->getSearchableAttributes());
        static::assertEmpty($metadata->getDisplayedAttributes());
        static::assertNull($metadata->getDistinctAttribute());

        static::assertArrayHasKey('primaryKey', $metadata->toArray());
        static::assertArrayHasKey('rankingRules', $metadata->toArray());
        static::assertArrayHasKey('stopWords', $metadata->toArray());
        static::assertArrayHasKey('distinctAttribute', $metadata->toArray());
        static::assertArrayHasKey('facetedAttributes', $metadata->toArray());
        static::assertArrayHasKey('searchableAttributes', $metadata->toArray());
        static::assertArrayHasKey('displayedAttributes', $metadata->toArray());
        static::assertArrayHasKey('synonyms', $metadata->toArray());

        $metadata = new IndexMetadata('foo', true, 'id');

        static::assertTrue($metadata->isAsync());
    }
}
