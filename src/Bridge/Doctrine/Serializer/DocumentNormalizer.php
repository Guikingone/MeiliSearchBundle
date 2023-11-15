<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Bridge\Doctrine\Serializer;

use MeiliSearchBundle\Bridge\Doctrine\Attribute\Document;
use MeiliSearchBundle\Bridge\Doctrine\Attribute\Reader\DocumentReaderInterface;
use MeiliSearchBundle\Exception\InvalidDocumentConfigurationException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly DocumentReaderInterface $documentReader,
        private readonly ObjectNormalizer $objectNormalizer,
        private readonly PropertyAccessorInterface $propertyAccessor
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var Document $configuration */
        $configuration = $this->documentReader->getConfiguration($object);
        $primaryKey = $configuration->getPrimaryKey();
        if (null === $primaryKey) {
            return $this->objectNormalizer->normalize($object, $format, $context);
        }
        if ($this->propertyAccessor->isReadable($object, $primaryKey)) {
            return $this->objectNormalizer->normalize($object, $format, $context);
        }
        throw new InvalidDocumentConfigurationException(
            sprintf(
                'The configured primary key does not exist in the current object, given: "%s"',
                $primaryKey
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $this->documentReader->isDocument($data);
    }
}
