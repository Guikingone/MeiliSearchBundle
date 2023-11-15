<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Index;

use Generator;
use Meilisearch\Client;
use Meilisearch\Contracts\IndexesResults;
use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Exception\RuntimeException as InternalRuntimeException;
use MeiliSearchBundle\Exception\RuntimeException as MeiliSeachBundleRuntimeException;
use MeiliSearchBundle\Index\IndexListInterface;
use MeiliSearchBundle\Index\IndexOrchestrator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexOrchestratorTest extends TestCase
{
    public function testIndexCannotBeAddedWithException(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('createIndex')->willThrowException(
            new ClientException(new MockResponse([], ['http_code' => 400]))
        );

        $orchestrator = new IndexOrchestrator($client, $eventDispatcher, $logger);

        static::expectException(MeiliSeachBundleRuntimeException::class);
        static::expectExceptionMessage('HTTP 400 returned for ""');
        static::expectExceptionCode(0);
        $orchestrator->addIndex('test', 'test');
    }

    public function testIndexCanBeAdded(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::exactly(2))->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::never())->method('getUid')->willReturn('test');
        $index->expects(self::never())->method('getPrimaryKey')->willReturn('test');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('createIndex')->with(self::equalTo('test'), [
            'primaryKey' => 'test',
        ])->willReturn([
            'taskUid' => 0,
        ]);

        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new IndexOrchestrator($client, $eventDispatcher, $logger);
        $orchestrator->addIndex('test', 'test');
    }

    /**
     * @param array<string, string|int|bool> $configuration
     * @dataProvider provideConfiguration
     */
    public function testIndexCanBeAddedWithConfiguration(array $configuration, string $method): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::exactly(2))->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::never())->method('getUid')->willReturn('test');
        $index->expects(self::never())->method('getPrimaryKey')->willReturn('test');
        $index->expects(self::once())->method($method);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('createIndex')->with(self::equalTo('test'), [
            'primaryKey' => 'test',
        ])->willReturn([
            'taskUid' => 0,
        ]);

        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new IndexOrchestrator($client, $eventDispatcher, $logger);
        $orchestrator->addIndex('test', 'test', $configuration);
    }

    public function testIndexCanBeAddedWithoutEventDispatcher(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::never())->method('getUid')->willReturn('test');
        $index->expects(self::never())->method('getPrimaryKey')->willReturn('test');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('createIndex')->willReturn([
            'taskUid' => 0,
        ]);

        $orchestrator = new IndexOrchestrator($client, null, $logger);
        $orchestrator->addIndex('test', 'test');
    }

    public function testIndexCannotBeUpdatedWithException(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::exactly(2))->method('error');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willThrowException(new RuntimeException('An error occurred'));

        $orchestrator = new IndexOrchestrator($client, $eventDispatcher, $logger);

        static::expectException(RuntimeException::class);
        static::expectExceptionCode(0);
        static::expectExceptionMessage('An error occurred');
        /* @phpstan-ignore-next-line */
        $orchestrator->update('foo', ['synonyms' => ['xmen' => ['wolverine']]]);
    }

    public function testIndexCannotBeUpdatedWithExceptionOnUpdate(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');
        $logger->expects(self::once())->method('error')->with(
            self::equalTo('The index cannot be updated, error: "An error occurred"')
        );

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('updateSynonyms')->willThrowException(
            new RuntimeException('An error occurred')
        );
        $index->expects(self::once())->method('getPrimaryKey')->willReturn('id');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new IndexOrchestrator($client, $eventDispatcher, $logger);

        static::expectException(InternalRuntimeException::class);
        static::expectExceptionMessage('An error occurred');
        static::expectExceptionCode(0);
        /* @phpstan-ignore-next-line */
        $orchestrator->update('test', ['synonyms' => ['xmen' => ['wolverine']]]);
    }

    public function testIndexCanBeUpdated(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('updateSynonyms');
        $index->expects(self::once())->method('getPrimaryKey')->willReturn('id');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new IndexOrchestrator($client, $eventDispatcher, $logger);
        /* @phpstan-ignore-next-line */
        $orchestrator->update('test', ['synonyms' => ['xmen' => ['wolverine']]]);
    }

    public function testIndexCanBeUpdatedWithoutPrimaryKey(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getPrimaryKey')->willReturn(null);
        $index->expects(self::once())->method('update')->with(['primaryKey' => 'id']);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new IndexOrchestrator($client, $eventDispatcher, $logger);
        $orchestrator->update('test', ['primaryKey' => 'id']);
    }

    /**
     * @param array<string, string|int|bool> $configuration
     * @dataProvider provideConfiguration
     */
    public function testIndexCanBeUpdatedWithConfiguration(array $configuration, string $method): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method($method);
        $index->expects(self::once())->method('getPrimaryKey')->willReturn('id');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new IndexOrchestrator($client, $eventDispatcher, $logger);
        $orchestrator->update('test', $configuration);
    }

    public function testIndexesCannotBeRetrievedWithException(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndexes')->willThrowException(
            new RuntimeException('An error occurred')
        );

        $orchestrator = new IndexOrchestrator($client, null, $logger);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('An error occurred');
        static::expectExceptionCode(0);
        $orchestrator->getIndexes();
    }

    public function testIndexCannotBeRetrievedWithException(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->with(self::equalTo('foo'))->willThrowException(
            new RuntimeException('An error occurred')
        );

        $orchestrator = new IndexOrchestrator($client, null, $logger);

        static::expectException(RuntimeException::class);
        $orchestrator->getIndex('foo');
    }

    public function testIndexCanBeRetrieved(): void
    {
        $indexes = $this->createMock(Indexes::class);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(self::equalTo('An index has been retrieved'), [
            'uid' => 'foo',
        ]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->with(self::equalTo('foo'))->willReturn($indexes);

        $orchestrator = new IndexOrchestrator($client, $eventDispatcher, $logger);

        $index = $orchestrator->getIndex('foo');

        static::assertSame($indexes, $index);
    }

    public function testAllIndexesCanBeRetrieved(): void
    {
        $firstIndexes = $this->createMock(Indexes::class);
        $firstIndexes->expects(self::never())->method('show')->willReturn([
            "uid" => "movies",
            "primaryKey" => "movie_id",
            "createdAt" => "2019-11-20T09:40:33.711324Z",
            "updatedAt" => "2019-11-20T10:16:42.761858Z",
        ]);
        $firstIndexes->expects(self::once())->method('getUid')->willReturn('foo');

        $secondIndexes = $this->createMock(Indexes::class);
        $secondIndexes->expects(self::never())->method('show')->willReturn([
            "uid" => "movie_reviews",
            "primaryKey" => null,
            "createdAt" => "2019-11-20T09:40:33.711324Z",
            "updatedAt" => "2019-11-20T10:16:42.761858Z",
        ]);
        $secondIndexes->expects(self::once())->method('getUid')->willReturn('bar');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndexes')->willReturn(
            new IndexesResults([
                'results' => [
                    $firstIndexes,
                    $secondIndexes,
                ],
                'offset' => 0,
                'limit' => 20,
            ])
        );

        $orchestrator = new IndexOrchestrator($client);

        $list = $orchestrator->getIndexes();

        static::assertInstanceOf(IndexListInterface::class, $list);
        static::assertNotEmpty($list);
        static::assertTrue($list->has('foo'));
        static::assertTrue($list->has('bar'));
    }

    public function testIndexCannotBeDeletedWithException(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('deleteIndex')->willThrowException(
            new RuntimeException('An error occurred')
        );

        $orchestrator = new IndexOrchestrator($client, null, $logger);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('An error occurred');
        static::expectExceptionCode(0);
        $orchestrator->removeIndex('test');
    }

    public function testIndexCanBeDeleted(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');
        $logger->expects(self::once())->method('info')->with(self::equalTo('An index has been deleted'), [
            'uid' => 'test',
        ]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('deleteIndex');

        $orchestrator = new IndexOrchestrator($client, null, $logger);
        $orchestrator->removeIndex('test');
    }

    public function provideConfiguration(): Generator
    {
        yield 'Displayed attributes' => [
            ['displayedAttributes' => ['id', 'title']],
            'updateDisplayedAttributes',
        ];
        yield 'Distinct attribute' => [
            ['distinctAttribute' => 'movie_id'],
            'updateDistinctAttribute',
        ];
        yield 'Attributes for faceting' => [
            ['facetedAttributes' => ['id', 'title']],
            'updateFaceting',
        ];
        yield 'Ranking rules' => [
            [
                'rankingRulesAttributes' => [
                    'typo',
                    'words',
                    'proximity',
                    'attribute',
                    'wordsPosition',
                    'exactness',
                    'asc(release_date)',
                    'desc(rank)',
                ],
            ],
            'updateRankingRules',
        ];
        yield 'Stop words' => [
            ['stopWords' => ['the', 'of', 'to']],
            'updateStopWords',
        ];
        yield 'Searchable attributes' => [
            ['searchableAttributes' => ['title', 'description', 'uid']],
            'updateSearchableAttributes',
        ];
    }
}
