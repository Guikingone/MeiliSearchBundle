<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Metadata;

use MeiliSearchBundle\Metadata\IndexMetadata;
use MeiliSearchBundle\Metadata\IndexMetadataRegistry;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexMetadataRegistryTest extends TestCase
{
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
}
