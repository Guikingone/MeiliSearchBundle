<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\DataProvider;

use MeiliSearchBundle\DataProvider\DocumentDataProviderInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentDataProviderTest extends TestCase
{
    public function testProviderIsConfigured(): void
    {
        $provider = new FooDataProvider();

        static::assertSame('foo', $provider->support());
        static::assertArrayHasKey('id', $provider->getDocument());
        static::assertSame(1, $provider->getDocument()['id']);
        static::assertArrayHasKey('key', $provider->getDocument());
        static::assertSame('bar', $provider->getDocument()['key']);
    }
}

final class FooDataProvider implements DocumentDataProviderInterface
{
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
            'id' => 1,
            'key' => 'bar',
        ];
    }
}
