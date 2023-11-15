<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Metadata;

use MeiliSearchBundle\Exception\InvalidArgumentException;
use MeiliSearchBundle\Metadata\IndexMetadata;
use MeiliSearchBundle\Metadata\IndexMetadataRegistry;
use MeiliSearchBundle\Serializer\IndexMetadataDenormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexMetadataRegistryTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->serializer = new Serializer([
            new IndexMetadataDenormalizer(new ObjectNormalizer()),
            new JsonSerializableNormalizer(),
            new ObjectNormalizer(),
        ], [
            new JsonEncoder(),
        ]);

        $this->filesystem->remove(__DIR__ . '/assets/_ms_bundle_');
    }

    public function testConfigurationCanBeAdded(): void
    {
        $registry = new IndexMetadataRegistry($this->filesystem, $this->serializer, __DIR__ . '/assets');
        $registry->add('foo', new IndexMetadata('foo'));

        static::assertSame('foo', $registry->get('foo')->getUid());
        static::assertArrayHasKey('foo', $registry->toArray());
    }

    public function testConfigurationCannotBeAddedThenUpdated(): void
    {
        $registry = new IndexMetadataRegistry($this->filesystem, $this->serializer, __DIR__ . '/assets');
        $registry->add('foo', new IndexMetadata('foo'));

        static::assertSame('foo', $registry->get('foo')->getUid());
        static::assertArrayHasKey('foo', $registry->toArray());

        $registry->add('foo', new IndexMetadata('bar'));

        static::assertSame('bar', $registry->get('foo')->getUid());
    }

    public function testConfigurationCanBeCreatedDuringOverrideIfNotSet(): void
    {
        $registry = new IndexMetadataRegistry($this->filesystem, $this->serializer, __DIR__ . '/assets');

        $registry->override('foo', new IndexMetadata('bar'));
        static::assertSame('bar', $registry->get('foo')->getUid());
    }

    public function testConfigurationCanBeOverridden(): void
    {
        $registry = new IndexMetadataRegistry($this->filesystem, $this->serializer, __DIR__ . '/assets');
        $registry->add('foo', new IndexMetadata('foo'));

        static::assertSame('foo', $registry->get('foo')->getUid());

        $registry->override('foo', new IndexMetadata('bar'));
        static::assertSame('bar', $registry->get('foo')->getUid());
    }

    public function testMetadataCannotBeRemovedIfNotSet(): void
    {
        $registry = new IndexMetadataRegistry($this->filesystem, $this->serializer, __DIR__ . '/assets');

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('The desired index does not exist');
        $registry->remove('foo');
    }

    public function testMetadataCanBeRemoved(): void
    {
        $registry = new IndexMetadataRegistry($this->filesystem, $this->serializer, __DIR__ . '/assets');
        $registry->add('foo', new IndexMetadata('foo'));

        static::assertSame('foo', $registry->get('foo')->getUid());
        static::assertArrayHasKey('foo', $registry->toArray());

        $registry->remove('foo');

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('The desired index does not exist');
        $registry->get('foo');
    }

    public function testConfigurationCanBeClearedWhenEmpty(): void
    {
        $registry = new IndexMetadataRegistry($this->filesystem, $this->serializer, __DIR__ . '/assets');

        $registry->clear();
        static::assertEmpty($registry->toArray());
    }

    public function testConfigurationCanBeCleared(): void
    {
        $registry = new IndexMetadataRegistry($this->filesystem, $this->serializer, __DIR__ . '/assets');
        $registry->add('foo', new IndexMetadata('foo'));

        static::assertSame('foo', $registry->get('foo')->getUid());
        static::assertArrayHasKey('foo', $registry->toArray());

        $registry->clear();

        static::assertEmpty($registry->toArray());
    }
}
