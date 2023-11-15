<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\DataProvider;

use MeiliSearchBundle\DataProvider\DocumentDataProviderInterface;
use MeiliSearchBundle\DataProvider\EmbeddedDocumentDataProviderInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class EmbeddedDocumentDataProviderTest extends TestCase
{
    public function testProviderIsConfigured(): void
    {
        $provider = new FooEmbeddedDataProvider();

        static::assertSame('foo', $provider->support());
        static::assertArrayHasKey('id', $provider->getDocument()[0]);
        static::assertSame(1, $provider->getDocument()[0]['id']);
        static::assertArrayHasKey('key', $provider->getDocument()[0]);
        static::assertSame('bar', $provider->getDocument()[0]['key']);
        static::assertArrayHasKey('id', $provider->getDocument()[1]);
        static::assertSame(2, $provider->getDocument()[1]['id']);
        static::assertArrayHasKey('key', $provider->getDocument()[1]);
        static::assertSame('foo', $provider->getDocument()[1]['key']);
    }
}

final class FooEmbeddedDataProvider implements EmbeddedDocumentDataProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function support(): string
    {
        return 'foo';
    }

    /**
     * {@inheritdoc}
     */
    public function getDocument(): array
    {
        return [
            [
                'id' => 1,
                'key' => 'bar',
            ],
            [

                'id' => 2,
                'key' => 'foo',
            ],
        ];
    }
}
