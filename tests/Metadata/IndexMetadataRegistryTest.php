<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Metadata;

use MeiliSearchBundle\Exception\InvalidArgumentException;
use MeiliSearchBundle\Metadata\IndexMetadata;
use MeiliSearchBundle\Metadata\IndexMetadataRegistry;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexMetadataRegistryTest extends TestCase
{
    public function testConfigurationCannotBeAddedWhenExisting(): void
    {
        $registry = new IndexMetadataRegistry();
        $registry->add('foo', new IndexMetadata('foo'));

        static::assertSame('foo', $registry->get('foo')->getUid());
        static::assertArrayHasKey('foo', $registry->toArray());

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(sprintf('This index is already configured, please consider using "%s::override()"', get_class($registry)));
        $registry->add('foo', new IndexMetadata('foo'));
    }

    public function testConfigurationCanBeAdded(): void
    {
        $registry = new IndexMetadataRegistry();
        $registry->add('foo', new IndexMetadata('foo'));

        static::assertSame('foo', $registry->get('foo')->getUid());
        static::assertArrayHasKey('foo', $registry->toArray());
    }

    public function testConfigurationCanBeOverridden(): void
    {
        $registry = new IndexMetadataRegistry();
        $registry->add('foo', new IndexMetadata('foo'));

        static::assertSame('foo', $registry->get('foo')->getUid());

        $registry->override('foo', new IndexMetadata('bar'));
        static::assertSame('bar', $registry->get('foo')->getUid());
    }

    public function testConfigurationCanBeRemoved(): void
    {
        $registry = new IndexMetadataRegistry();
        $registry->add('foo', new IndexMetadata('foo'));

        static::assertSame('foo', $registry->get('foo')->getUid());
        static::assertArrayHasKey('foo', $registry->toArray());

        $registry->remove('foo');

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('The desired index does not exist');
        $registry->get('foo');
    }

    public function testConfigurationCanBeCleared(): void
    {
        $registry = new IndexMetadataRegistry();
        $registry->add('foo', new IndexMetadata('foo'));

        static::assertSame('foo', $registry->get('foo')->getUid());
        static::assertArrayHasKey('foo', $registry->toArray());

        $registry->clear();

        static::assertEmpty($registry->toArray());
    }
}
