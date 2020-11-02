<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Serializer;

use MeiliSearchBundle\Metadata\IndexMetadata;
use MeiliSearchBundle\Metadata\IndexMetadataInterface;
use MeiliSearchBundle\Serializer\IndexMetadataDenormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexMetadataDenormalizerTest extends TestCase
{
    public function testDenormalizerSupport(): void
    {
        $denormalizer = new IndexMetadataDenormalizer(new ObjectNormalizer());

        static::assertFalse($denormalizer->supportsDenormalization('', IndexMetadata::class));
        static::assertTrue($denormalizer->supportsDenormalization([], IndexMetadata::class));
        static::assertTrue($denormalizer->supportsDenormalization([], IndexMetadataInterface::class));
    }

    public function testDenormalizerCanDenormalize(): void
    {
        $denormalizer = new IndexMetadataDenormalizer(new ObjectNormalizer());

        $data = $denormalizer->denormalize([
            'uid' => 'foo',
            'async' => false,
            'primaryKey' => 'id',
            'rankingRules' => [],
            'stopWords' => [],
            'distinctAttribute' => null,
            'facetedAttributes' => [],
            'searchableAttributes' => [],
            'displayedAttributes' => [],
            'synonyms' => [],
        ], IndexMetadata::class);

        static::assertInstanceOf(IndexMetadataInterface::class, $data);
    }
}
