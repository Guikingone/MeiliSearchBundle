<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\DependencyInjection;

use MeiliSearchBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ConfigurationTest extends TestCase
{
    public function testConfigurationIsDefined(): void
    {
        $configuration = (new Processor())->processConfiguration(new Configuration(), [
            'meili_search' => [
                'apiKey' => 'test',
                'indexes' => [
                    'foo' => [
                        'primaryKey' => 'id',
                    ],
                    'bar' => [
                        'primaryKey' => 'title',
                        'displayedAttributes' => ['id', 'title'],
                    ],
                ],
            ],
        ]);

        static::assertArrayHasKey('host', $configuration);
        static::assertArrayHasKey('apiKey', $configuration);
        static::assertSame('http://127.0.0.1', $configuration['host']);
        static::assertSame('test', $configuration['apiKey']);
        static::assertNull($configuration['prefix']);
        static::assertArrayHasKey('indexes', $configuration);
        static::assertCount(2, $configuration['indexes']);

        static::assertNotNull($configuration['indexes']['foo']['primaryKey']);
        static::assertSame('id', $configuration['indexes']['foo']['primaryKey']);
        static::assertFalse($configuration['indexes']['foo']['async']);
        static::assertNotEmpty($configuration['indexes']['foo']['rankingRules']);
        static::assertSame(['typo', 'words', 'proximity', 'attribute', 'wordsPosition', 'exactness'], $configuration['indexes']['foo']['rankingRules']);
        static::assertEmpty($configuration['indexes']['foo']['stopWords']);
        static::assertNull($configuration['indexes']['foo']['distinctAttribute']);
        static::assertEmpty($configuration['indexes']['foo']['facetedAttributes']);
        static::assertEmpty($configuration['indexes']['foo']['searchableAttributes']);
        static::assertEmpty($configuration['indexes']['foo']['displayedAttributes']);
        static::assertEmpty($configuration['indexes']['foo']['synonyms']);

        static::assertNotNull($configuration['indexes']['bar']['primaryKey']);
        static::assertSame('title', $configuration['indexes']['bar']['primaryKey']);
        static::assertFalse($configuration['indexes']['bar']['async']);
        static::assertNotEmpty($configuration['indexes']['bar']['rankingRules']);
        static::assertSame(['typo', 'words', 'proximity', 'attribute', 'wordsPosition', 'exactness'], $configuration['indexes']['bar']['rankingRules']);
        static::assertEmpty($configuration['indexes']['bar']['stopWords']);
        static::assertNull($configuration['indexes']['bar']['distinctAttribute']);
        static::assertEmpty($configuration['indexes']['bar']['facetedAttributes']);
        static::assertEmpty($configuration['indexes']['bar']['searchableAttributes']);
        static::assertNotEmpty($configuration['indexes']['bar']['displayedAttributes']);
        static::assertEmpty($configuration['indexes']['bar']['synonyms']);
    }

    public function testConfigurationCanDefinePrefix(): void
    {
        $configuration = (new Processor())->processConfiguration(new Configuration(), [
            'meili_search' => [
                'apiKey' => 'test',
                'prefix' => 'bar',
            ],
        ]);

        static::assertArrayHasKey('prefix', $configuration);
        static::assertSame('bar', $configuration['prefix']);
    }

    public function testConfigurationDoesNotEnableCacheByDefault(): void
    {
        $configuration = (new Processor())->processConfiguration(new Configuration(), [
            'meili_search' => [
                'apiKey' => 'test',
                'cache' => [],
            ],
        ]);

        static::assertArrayHasKey('cache', $configuration);
        static::assertArrayHasKey('enabled', $configuration['cache']);
        static::assertSame(false, $configuration['cache']['enabled']);
        static::assertArrayHasKey('pool', $configuration['cache']);
        static::assertSame('app', $configuration['cache']['pool']);
    }

    public function testConfigurationCanEnableCache(): void
    {
        $configuration = (new Processor())->processConfiguration(new Configuration(), [
            'meili_search' => [
                'apiKey' => 'test',
                'cache' => [
                    'enabled' => true,
                ],
            ],
        ]);

        static::assertArrayHasKey('cache', $configuration);
        static::assertArrayHasKey('enabled', $configuration['cache']);
        static::assertSame(true, $configuration['cache']['enabled']);
        static::assertArrayHasKey('pool', $configuration['cache']);
        static::assertSame('app', $configuration['cache']['pool']);
    }

    public function testConfigurationCanOverrideCachePool(): void
    {
        $configuration = (new Processor())->processConfiguration(new Configuration(), [
            'meili_search' => [
                'apiKey' => 'test',
                'cache' => [
                    'enabled' => true,
                    'pool' => 'system',
                ],
                'indexes' => [
                    'foo' => [
                        'primaryKey' => 'id',
                    ],
                    'bar' => [
                        'primaryKey' => 'title',
                        'displayedAttributes' => ['id', 'title'],
                    ],
                ],
            ],
        ]);

        static::assertArrayHasKey('cache', $configuration);
        static::assertArrayHasKey('enabled', $configuration['cache']);
        static::assertSame(true, $configuration['cache']['enabled']);
        static::assertArrayHasKey('pool', $configuration['cache']);
        static::assertSame('system', $configuration['cache']['pool']);
    }

    public function testConfigurationCanDefineSynonyms(): void
    {
        $configuration = (new Processor())->processConfiguration(new Configuration(), [
            'meili_search' => [
                'apiKey' => 'test',
                'indexes' => [
                    'foo' => [
                        'primaryKey' => 'id',
                        'synonyms' => [
                            'bar' => [
                                'values' => ['id', 'title']
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        static::assertArrayHasKey('indexes', $configuration);
        static::assertCount(1, $configuration['indexes']);
        static::assertNotNull($configuration['indexes']['foo']['primaryKey']);
        static::assertSame('id', $configuration['indexes']['foo']['primaryKey']);
        static::assertNotEmpty($configuration['indexes']['foo']['synonyms']);
        static::assertArrayHasKey('bar', $configuration['indexes']['foo']['synonyms']);
        static::assertContainsEquals('id', $configuration['indexes']['foo']['synonyms']['bar']['values']);
        static::assertContainsEquals('title', $configuration['indexes']['foo']['synonyms']['bar']['values']);
    }
}
