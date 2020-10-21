<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Bridge\Doctrine\Serializer;

use MeiliSearchBundle\Bridge\Doctrine\Annotation\Reader\DocumentReaderInterface;
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
    /**
     * @var DocumentReaderInterface
     */
    private $documentReader;

    /**
     * @var ObjectNormalizer
     */
    private $objectNormalizer;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    public function __construct(
        DocumentReaderInterface $documentReader,
        ObjectNormalizer $objectNormalizer,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->documentReader = $documentReader;
        $this->objectNormalizer = $objectNormalizer;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $configuration = $this->documentReader->getConfiguration($object);
        $primaryKey = $configuration->getPrimaryKey();

        if (null !== $primaryKey && !$this->propertyAccessor->isReadable($object, $primaryKey)) {
            throw new InvalidDocumentConfigurationException(sprintf(
                'The configured primary key does not exist in the current object, given: "%s"',
                $primaryKey
            ));
        }

        return $this->objectNormalizer->normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $this->documentReader->isDocument($data);
    }
}
