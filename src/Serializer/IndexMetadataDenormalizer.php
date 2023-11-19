<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Serializer;

use MeiliSearchBundle\Metadata\IndexMetadata;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use function is_array;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexMetadataDenormalizer implements DenormalizerInterface
{
    public function __construct(private readonly ObjectNormalizer $objectNormalizer)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return $this->objectNormalizer->denormalize($data, $type, $format, [
            AbstractNormalizer::DEFAULT_CONSTRUCTOR_ARGUMENTS => [
                'uid' => $data['uid'],
                'async' => $data['async'],
                'primaryKey' => $data['primaryKey'],
                'rankingRules' => $data['rankingRules'],
                'stopWords' => $data['stopWords'],
                'distinctAttribute' => $data['distinctAttribute'],
                'facetedAttributes' => $data['facetedAttributes'],
                'searchableAttributes' => $data['searchableAttributes'],
                'displayedAttributes' => $data['displayedAttributes'],
                'synonyms' => $data['synonyms'],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return is_array($data) && IndexMetadata::class === $type;
    }
}
