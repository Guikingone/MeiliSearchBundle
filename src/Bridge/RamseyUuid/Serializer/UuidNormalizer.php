<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Bridge\RamseyUuid\Serializer;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class UuidNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return $object->toString();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof UuidInterface;
    }
}
