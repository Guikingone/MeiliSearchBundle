<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Bridge\Doctrine\Serializer;

use Doctrine\Common\Annotations\AnnotationReader;
use MeiliSearchBundle\Bridge\Doctrine\Annotation\Document;
use MeiliSearchBundle\Bridge\Doctrine\Annotation\Reader\DocumentReader;
use MeiliSearchBundle\Bridge\Doctrine\Serializer\DocumentNormalizer;
use MeiliSearchBundle\Exception\InvalidDocumentConfigurationException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentNormalizerTest extends TestCase
{
    public function testNormalizationIsSupported(): void
    {
        $reader = new DocumentReader(new AnnotationReader());
        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);

        $normalizer = new DocumentNormalizer($reader, new ObjectNormalizer(), $propertyAccessor);
        static::assertFalse($normalizer->supportsNormalization(new stdClass()));
        static::assertTrue($normalizer->supportsNormalization(new FooDocument()));
    }

    public function testDocumentCannotBeNormalizedWithoutPrimaryKey(): void
    {
        $reader = new DocumentReader(new AnnotationReader());
        $normalizer = new DocumentNormalizer($reader, new ObjectNormalizer(), PropertyAccess::createPropertyAccessor());

        static::expectException(InvalidDocumentConfigurationException::class);
        static::expectExceptionMessage('The configured primary key does not exist in the current object, given: "id"');
        $normalizer->normalize(new FooDocument());
    }

    public function testDocumentCanBeNormalized(): void
    {
        $reader = new DocumentReader(new AnnotationReader());
        $normalizer = new DocumentNormalizer($reader, new ObjectNormalizer(), PropertyAccess::createPropertyAccessor());

        $data = $normalizer->normalize(new BarDocument());
        static::assertArrayHasKey('id', $data);
    }
}

/**
 * @Document(index="foo", primaryKey="id")
 */
final class FooDocument
{
}

/**
 * @Document(index="foo", primaryKey="id")
 */
final class BarDocument
{
    /**
     * @var string
     */
    private $id;

    public function __construct()
    {
        $this->id = '1';
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }
}
