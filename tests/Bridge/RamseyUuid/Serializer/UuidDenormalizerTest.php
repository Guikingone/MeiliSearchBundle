<?php

declare(strict_types=1);

namespace Bridge\RamseyUuid\Serializer;

use MeiliSearchBundle\Bridge\RamseyUuid\Serializer\UuidDenormalizer;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class UuidDenormalizerTest extends TestCase
{
    public function testDenormalizerCannotSupportUuid(): void
    {
        $denormalizer = new UuidDenormalizer();

        static::assertFalse($denormalizer->supportsDenormalization(1, UuidInterface::class));
    }

    public function testDenormalizerCanSupportUuid(): void
    {
        $denormalizer = new UuidDenormalizer();

        static::assertTrue($denormalizer->supportsDenormalization((Uuid::uuid4())->toString(), UuidInterface::class));
    }

    public function testDenormalizerCanReturnUuid(): void
    {
        $denormalizer = new UuidDenormalizer();

        static::assertNotEmpty($denormalizer->denormalize((Uuid::uuid4())->toString(), UuidInterface::class));
    }
}
