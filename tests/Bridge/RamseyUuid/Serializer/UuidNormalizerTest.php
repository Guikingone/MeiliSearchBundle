<?php

declare(strict_types=1);

namespace Bridge\RamseyUuid\Serializer;

use MeiliSearchBundle\Bridge\RamseyUuid\Serializer\UuidNormalizer;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use stdClass;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class UuidNormalizerTest extends TestCase
{
    public function testNormalizerCannotBeUsedOnWrongObject(): void
    {
        $normalizer = new UuidNormalizer();

        static::assertFalse($normalizer->supportsNormalization(new stdClass()));
    }

    public function testNormalizerCanBeUsedOnUuid(): void
    {
        $normalizer = new UuidNormalizer();

        static::assertTrue($normalizer->supportsNormalization(Uuid::uuid4()));
    }

    public function testNormalizerCanNormalizeUuid(): void
    {
        $normalizer = new UuidNormalizer();

        static::assertNotEmpty($normalizer->normalize(Uuid::uuid4()));
    }
}
