<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Serializer;

use MeiliSearchBundle\Metadata\IndexMetadata;
use MeiliSearchBundle\Metadata\IndexMetadataInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use function array_merge;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexMetadataDenormalizer implements DenormalizerInterface
{
    /**
     * @var ObjectNormalizer
     */
    private $objectNormalizer;

    public function __construct(ObjectNormalizer $objectNormalizer)
    {
        $this->objectNormalizer = $objectNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return $this->objectNormalizer->denormalize($data, $type, $format, array_merge($context, [
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
        ]));
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return is_array($data) && (IndexMetadata::class === $type || IndexMetadataInterface::class === $type);
    }
}
