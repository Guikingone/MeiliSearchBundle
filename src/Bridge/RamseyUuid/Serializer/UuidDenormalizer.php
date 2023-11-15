<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Bridge\RamseyUuid\Serializer;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function is_a;
use function is_string;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class UuidDenormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $type, string $format = null, array $context = []): UuidInterface
    {
        return Uuid::fromString($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, string $format = null): bool
    {
        return (is_string($data) && Uuid::isValid($data)) && is_a($type, UuidInterface::class, true);
    }
}
