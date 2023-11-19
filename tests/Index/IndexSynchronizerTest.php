<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Index;

use MeiliSearchBundle\Health\HealthEntryPointInterface;
use MeiliSearchBundle\Index\IndexListInterface;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Index\IndexSynchronizer;
use MeiliSearchBundle\Metadata\IndexMetadata;
use MeiliSearchBundle\Metadata\IndexMetadataRegistryInterface;
use MeiliSearchBundle\Settings\SettingsEntryPointInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexSynchronizerTest extends TestCase
{
    public function testSynchronizerCannotCreateIndexesWithSynchronizedInstance(): void
    {
        $settingsEntryPoint = $this->createMock(SettingsEntryPointInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(
            self::equalTo(
                'The indexes cannot be created as the instance and the local storage are already synchronized'
            )
        );

        $list = $this->createMock(IndexListInterface::class);
        $list->expects(self::once())->method('count')->willReturn(2);

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('getIndexes')->willReturn($list);
        $orchestrator->expects(self::never())->method('addIndex');

        $metadataRegistry = $this->createMock(IndexMetadataRegistryInterface::class);
        $metadataRegistry->expects(self::once())->method('count')->willReturn(2);
        $metadataRegistry->expects(self::never())->method('add');

        $entryPoint = $this->createMock(HealthEntryPointInterface::class);
        $entryPoint->expects(self::once())->method('isUp')->willReturn(true);

        $synchronizer = new IndexSynchronizer(
            $orchestrator,
            $metadataRegistry,
            $entryPoint,
            $settingsEntryPoint,
            $logger
        );
        $synchronizer->createIndexes([]);
    }

    public function testSynchronizerCannotCreateIndexesWithIndexCreationException(): void
    {
        $settingsEntryPoint = $this->createMock(SettingsEntryPointInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');
        $logger->expects(self::once())->method('critical')->with(
            self::equalTo('An error occurred when creating the indexes'),
            [
                'error' => 'An error occurred',
            ]
        );

        $list = $this->createMock(IndexListInterface::class);
        $list->expects(self::once())->method('count')->willReturn(2);

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::exactly(2))->method('getIndexes')->willReturn($list);
        $orchestrator->expects(self::once())->method('addIndex')->willThrowException(
            new RuntimeException('An error occurred')
        );

        $metadataRegistry = $this->createMock(IndexMetadataRegistryInterface::class);
        $metadataRegistry->expects(self::once())->method('count')->willReturn(1);
        $metadataRegistry->expects(self::once())->method('add');
        $metadataRegistry->expects(self::once())->method('toArray')->willReturn([
            'foo' => new IndexMetadata('foo', false, 'id', [], [], null, [], [], [], []),
        ]);

        $entryPoint = $this->createMock(HealthEntryPointInterface::class);
        $entryPoint->expects(self::once())->method('isUp')->willReturn(true);

        $synchronizer = new IndexSynchronizer(
            $orchestrator,
            $metadataRegistry,
            $entryPoint,
            $settingsEntryPoint,
            $logger
        );

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('An error occurred');
        static::expectExceptionCode(0);
        $synchronizer->createIndexes([
            'foo' => [
                'primaryKey' => 'id',
            ],
        ]);
    }

    public function testSynchronizerCanCreateIndexes(): void
    {
        $settingsEntryPoint = $this->createMock(SettingsEntryPointInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');
        $logger->expects(self::never())->method('critical');

        $list = $this->createMock(IndexListInterface::class);
        $list->expects(self::once())->method('has')->with(self::equalTo('foo'))->willReturn(false);
        $list->expects(self::once())->method('count')->willReturn(2);

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::exactly(2))->method('getIndexes')->willReturn($list);
        $orchestrator->expects(self::once())->method('addIndex')->with(
            self::equalTo('foo'),
            self::equalTo('id'),
            self::equalTo([
                'primaryKey' => 'id',
                'rankingRules' => [],
                'stopWords' => [],
                'distinctAttribute' => null,
                'facetedAttributes' => [],
                'searchableAttributes' => [],
                'displayedAttributes' => [],
                'synonyms' => [],
            ])
        );

        $metadataRegistry = $this->createMock(IndexMetadataRegistryInterface::class);
        $metadataRegistry->expects(self::once())->method('count')->willReturn(1);
        $metadataRegistry->expects(self::once())->method('add')
            ->with(
                self::equalTo('foo'),
                new IndexMetadata('foo', false, 'id', [], [], null, [], [], [], [])
            );
        $metadataRegistry->expects(self::once())->method('toArray')->willReturn([
            'foo' => new IndexMetadata('foo', false, 'id', [], [], null, [], [], [], []),
        ]);

        $entryPoint = $this->createMock(HealthEntryPointInterface::class);
        $entryPoint->expects(self::once())->method('isUp')->willReturn(true);

        $synchronizer = new IndexSynchronizer(
            $orchestrator,
            $metadataRegistry,
            $entryPoint,
            $settingsEntryPoint,
            $logger
        );

        $synchronizer->createIndexes([
            'foo' => [
                'primaryKey' => 'id',
            ],
        ]);
    }

    public function testSynchronizerCanCreateIndexesWithPrefix(): void
    {
        $settingsEntryPoint = $this->createMock(SettingsEntryPointInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');
        $logger->expects(self::never())->method('critical');

        $list = $this->createMock(IndexListInterface::class);
        $list->expects(self::once())->method('has')->with(self::equalTo('_ms_foo'))->willReturn(false);
        $list->expects(self::once())->method('count')->willReturn(2);

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::exactly(2))->method('getIndexes')->willReturn($list);
        $orchestrator->expects(self::once())->method('addIndex')->with(
            self::equalTo('_ms_foo'),
            self::equalTo('id'),
            self::equalTo([
                'primaryKey' => 'id',
                'rankingRules' => [],
                'stopWords' => [],
                'distinctAttribute' => null,
                'facetedAttributes' => [],
                'searchableAttributes' => [],
                'displayedAttributes' => [],
                'synonyms' => [],
            ])
        );

        $metadataRegistry = $this->createMock(IndexMetadataRegistryInterface::class);
        $metadataRegistry->expects(self::once())->method('count')->willReturn(1);
        $metadataRegistry->expects(self::once())->method('add')
            ->with(
                self::equalTo('_ms_foo'),
                new IndexMetadata('_ms_foo', false, 'id', [], [], null, [], [], [], [])
            );
        $metadataRegistry->expects(self::once())->method('toArray')->willReturn([
            'foo' => new IndexMetadata('_ms_foo', false, 'id', [], [], null, [], [], [], []),
        ]);

        $entryPoint = $this->createMock(HealthEntryPointInterface::class);
        $entryPoint->expects(self::once())->method('isUp')->willReturn(true);

        $synchronizer = new IndexSynchronizer(
            $orchestrator,
            $metadataRegistry,
            $entryPoint,
            $settingsEntryPoint,
            $logger
        );

        $synchronizer->createIndexes([
            'foo' => [
                'primaryKey' => 'id',
            ],
        ], '_ms_');
    }

    public function testSynchronizerCannotUpdateIndexesWithException(): void
    {
        $entryPoint = $this->createMock(HealthEntryPointInterface::class);
        $settingsEntryPoint = $this->createMock(SettingsEntryPointInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('critical')->with(
            self::equalTo('An error occurred when updating the indexes'),
            self::equalTo([
                'error' => 'An error occurred',
            ])
        );

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::never())->method('getIndexes');
        $orchestrator->expects(self::once())->method('update')->with(
            self::equalTo('_ms_foo'),
            self::equalTo([
                'primaryKey' => 'id',
                'rankingRules' => [],
                'stopWords' => [],
                'distinctAttribute' => null,
                'facetedAttributes' => [],
                'searchableAttributes' => [],
                'displayedAttributes' => [],
                'synonyms' => [],
            ])
        )->willThrowException(new RuntimeException('An error occurred'));

        $metadataRegistry = $this->createMock(IndexMetadataRegistryInterface::class);
        $metadataRegistry->expects(self::never())->method('count');
        $metadataRegistry->expects(self::once())->method('override')
            ->with(
                self::equalTo('_ms_foo'),
                new IndexMetadata('_ms_foo', false, 'id', [], [], null, [], [], [], [])
            );
        $metadataRegistry->expects(self::once())->method('toArray')->willReturn([
            'foo' => new IndexMetadata('_ms_foo', false, 'id', [], [], null, [], [], [], []),
        ]);

        $synchronizer = new IndexSynchronizer(
            $orchestrator,
            $metadataRegistry,
            $entryPoint,
            $settingsEntryPoint,
            $logger
        );

        static::expectException(RuntimeException::class);
        static::expectExceptionCode(0);
        static::expectExceptionMessage('An error occurred');
        $synchronizer->updateIndexes([
            'foo' => [
                'primaryKey' => 'id',
            ],
        ], '_ms_');
    }

    public function testSynchronizerCanUpdateIndexesWithPrefix(): void
    {
        $entryPoint = $this->createMock(HealthEntryPointInterface::class);
        $settingsEntryPoint = $this->createMock(SettingsEntryPointInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('critical');

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::never())->method('getIndexes');
        $orchestrator->expects(self::once())->method('update')->with(
            self::equalTo('_ms_foo'),
            self::equalTo([
                'primaryKey' => 'id',
                'rankingRules' => [],
                'stopWords' => [],
                'distinctAttribute' => null,
                'facetedAttributes' => [],
                'searchableAttributes' => [],
                'displayedAttributes' => [],
                'synonyms' => [],
            ])
        );

        $metadataRegistry = $this->createMock(IndexMetadataRegistryInterface::class);
        $metadataRegistry->expects(self::never())->method('count');
        $metadataRegistry->expects(self::once())->method('override')
            ->with(
                self::equalTo('_ms_foo'),
                new IndexMetadata('_ms_foo', false, 'id', [], [], null, [], [], [], [])
            );
        $metadataRegistry->expects(self::once())->method('toArray')->willReturn([
            'foo' => new IndexMetadata('_ms_foo', false, 'id', [], [], null, [], [], [], []),
        ]);

        $synchronizer = new IndexSynchronizer(
            $orchestrator,
            $metadataRegistry,
            $entryPoint,
            $settingsEntryPoint,
            $logger
        );

        $synchronizer->updateIndexes([
            'foo' => [
                'primaryKey' => 'id',
            ],
        ], '_ms_');
    }

    public function testSynchronizerCanUpdateIndexes(): void
    {
        $entryPoint = $this->createMock(HealthEntryPointInterface::class);
        $settingsEntryPoint = $this->createMock(SettingsEntryPointInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('critical');

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::never())->method('getIndexes');
        $orchestrator->expects(self::once())->method('update')->with(
            self::equalTo('foo'),
            self::equalTo([
                'primaryKey' => 'id',
                'rankingRules' => [],
                'stopWords' => [],
                'distinctAttribute' => null,
                'facetedAttributes' => [],
                'searchableAttributes' => [],
                'displayedAttributes' => [],
                'synonyms' => [],
            ])
        );

        $metadataRegistry = $this->createMock(IndexMetadataRegistryInterface::class);
        $metadataRegistry->expects(self::never())->method('count');
        $metadataRegistry->expects(self::once())->method('override')
            ->with(
                self::equalTo('foo'),
                new IndexMetadata('foo', false, 'id', [], [], null, [], [], [], [])
            );
        $metadataRegistry->expects(self::once())->method('toArray')->willReturn([
            'foo' => new IndexMetadata('foo', false, 'id', [], [], null, [], [], [], []),
        ]);

        $synchronizer = new IndexSynchronizer(
            $orchestrator,
            $metadataRegistry,
            $entryPoint,
            $settingsEntryPoint,
            $logger
        );

        $synchronizer->updateIndexes([
            'foo' => [
                'primaryKey' => 'id',
            ],
        ]);
    }

    public function testSynchronizerCannotDropIndexWithException(): void
    {
        $settingsEntryPoint = $this->createMock(SettingsEntryPointInterface::class);
        $entryPoint = $this->createMock(HealthEntryPointInterface::class);

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('removeIndex')->with(self::equalTo('foo'))->willThrowException(
            new RuntimeException('An error occurred')
        );

        $metadataRegistry = $this->createMock(IndexMetadataRegistryInterface::class);
        $metadataRegistry->expects(self::never())->method('remove')->with(self::equalTo('foo'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('critical')->with(self::equalTo('The index cannot be dropped'), [
            'index' => 'foo',
            'error' => 'An error occurred',
        ]);

        $synchronizer = new IndexSynchronizer(
            $orchestrator,
            $metadataRegistry,
            $entryPoint,
            $settingsEntryPoint,
            $logger
        );

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('An error occurred');
        static::expectExceptionCode(0);
        $synchronizer->dropIndex('foo');
    }

    public function testSynchronizerCanDropIndex(): void
    {
        $settingsEntryPoint = $this->createMock(SettingsEntryPointInterface::class);
        $entryPoint = $this->createMock(HealthEntryPointInterface::class);

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('removeIndex')->with(self::equalTo('foo'));

        $metadataRegistry = $this->createMock(IndexMetadataRegistryInterface::class);
        $metadataRegistry->expects(self::once())->method('remove')->with('foo');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('critical');

        $synchronizer = new IndexSynchronizer(
            $orchestrator,
            $metadataRegistry,
            $entryPoint,
            $settingsEntryPoint,
            $logger
        );
        $synchronizer->dropIndex('foo');
    }

    public function testSynchronizerCanDetermineInvalidSynchronizationStatusViaHealthEntryPoint(): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $metadataRegistry = $this->createMock(IndexMetadataRegistryInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $settingsEntryPoint = $this->createMock(SettingsEntryPointInterface::class);

        $entryPoint = $this->createMock(HealthEntryPointInterface::class);
        $entryPoint->expects(self::once())->method('isUp')->willReturn(false);

        $synchronizer = new IndexSynchronizer(
            $orchestrator,
            $metadataRegistry,
            $entryPoint,
            $settingsEntryPoint,
            $logger
        );

        static::assertFalse($synchronizer->isSynchronized());
    }

    public function testSynchronizerCanDetermineInvalidSynchronizationStatusViaIndexMetadataRegistryCount(): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $settingsEntryPoint = $this->createMock(SettingsEntryPointInterface::class);

        $metadataRegistry = $this->createMock(IndexMetadataRegistryInterface::class);
        $metadataRegistry->expects(self::once())->method('count')->willReturn(0);

        $entryPoint = $this->createMock(HealthEntryPointInterface::class);
        $entryPoint->expects(self::once())->method('isUp')->willReturn(true);

        $synchronizer = new IndexSynchronizer(
            $orchestrator,
            $metadataRegistry,
            $entryPoint,
            $settingsEntryPoint,
            $logger
        );

        static::assertFalse($synchronizer->isSynchronized());
    }

    public function testSynchronizerCanDetermineInvalidSynchronizationStatusViaIndexOrchestratorCount(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $settingsEntryPoint = $this->createMock(SettingsEntryPointInterface::class);

        $list = $this->createMock(IndexListInterface::class);
        $list->expects(self::once())->method('count')->willReturn(2);

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('getIndexes')->willReturn($list);

        $metadataRegistry = $this->createMock(IndexMetadataRegistryInterface::class);
        $metadataRegistry->expects(self::once())->method('count')->willReturn(1);

        $entryPoint = $this->createMock(HealthEntryPointInterface::class);
        $entryPoint->expects(self::once())->method('isUp')->willReturn(true);

        $synchronizer = new IndexSynchronizer(
            $orchestrator,
            $metadataRegistry,
            $entryPoint,
            $settingsEntryPoint,
            $logger
        );

        static::assertFalse($synchronizer->isSynchronized());
    }

    public function testSynchronizerCanDetermineValidSynchronizationStatusViaIndexOrchestratorCount(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $settingsEntryPoint = $this->createMock(SettingsEntryPointInterface::class);

        $list = $this->createMock(IndexListInterface::class);
        $list->expects(self::once())->method('count')->willReturn(2);

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('getIndexes')->willReturn($list);

        $metadataRegistry = $this->createMock(IndexMetadataRegistryInterface::class);
        $metadataRegistry->expects(self::once())->method('count')->willReturn(2);

        $entryPoint = $this->createMock(HealthEntryPointInterface::class);
        $entryPoint->expects(self::once())->method('isUp')->willReturn(true);

        $synchronizer = new IndexSynchronizer(
            $orchestrator,
            $metadataRegistry,
            $entryPoint,
            $settingsEntryPoint,
            $logger
        );

        static::assertTrue($synchronizer->isSynchronized());
    }
}
