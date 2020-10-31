<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Index;

use MeiliSearch\Client;
use MeiliSearch\Endpoints\Indexes;
use MeiliSearchBundle\Index\IndexOrchestrator;
use MeiliSearchBundle\Exception\RuntimeException as MeiliSeachBundleRuntimeException;
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
        $client->expects(self::once())->method('createIndex')->willThrowException(new ClientException(new MockResponse([], ['http_code' => 400])));

        $orchestrator = new IndexOrchestrator($client, $eventDispatcher, $logger);

        static::expectException(MeiliSeachBundleRuntimeException::class);
        $orchestrator->addIndex('test', 'test');
    }

    public function testIndexCanBeAdded(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::never())->method('getUid')->willReturn('test');
        $index->expects(self::never())->method('getPrimaryKey')->willReturn('test');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('createIndex')->willReturn($index);

        $orchestrator = new IndexOrchestrator($client, $eventDispatcher, $logger);
        $orchestrator->addIndex('test', 'test');
    }

    public function testIndexesCannotBeRetrievedWithException(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getAllIndexes')->willThrowException(new RuntimeException('An error occurred'));

        $orchestrator = new IndexOrchestrator($client, null, $logger);

        static::expectException(RuntimeException::class);
        $orchestrator->getIndexes();
    }

    public function testIndexCannotBeRetrievedWithException(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->with(self::equalTo('foo'))->willThrowException(new RuntimeException('An error occurred'));

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
        $client->expects(self::once())->method('getIndex')->with(self::equalTo('foo'))->willReturn($indexes);

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
            "updatedAt" => "2019-11-20T10:16:42.761858Z"
        ]);
        $secondIndexes = $this->createMock(Indexes::class);
        $secondIndexes->expects(self::never())->method('show')->willReturn([
            "uid" => "movie_reviews",
            "primaryKey" => null,
            "createdAt" => "2019-11-20T09:40:33.711324Z",
            "updatedAt" => "2019-11-20T10:16:42.761858Z"
        ]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getAllIndexes')->willReturn([
            $firstIndexes,
            $secondIndexes,
        ]);

        $orchestrator = new IndexOrchestrator($client);

        static::assertNotEmpty($orchestrator->getIndexes());
    }

    public function testIndexCannotBeDeletedWithException(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('deleteIndex')->willThrowException(new RuntimeException('An error occurred'));

        $orchestrator = new IndexOrchestrator($client, null, $logger);

        static::expectException(RuntimeException::class);
        $orchestrator->removeIndex('test');
    }

    public function testIndexCanBeDeleted(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');
        $logger->expects(self::once())->method('info');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('deleteIndex');

        $orchestrator = new IndexOrchestrator($client, null, $logger);
        $orchestrator->removeIndex('test');
    }
}
