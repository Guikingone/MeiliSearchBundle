<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Tests\Client;

use MeiliSearch\Client;
use MeiliSearchBundle\Client\IndexOrchestrator;
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
        $logger->expects(self::once())->method('info');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('createIndex')->willReturn([
            'uid' => 'test',
            'primaryKey' => 'test',
        ]);

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

    public function testAllIndexesCanBeRetrieved(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getAllIndexes')->willReturn([
            [
                "uid" => "movies",
                "primaryKey" => "movie_id",
                "createdAt" => "2019-11-20T09:40:33.711324Z",
                "updatedAt" => "2019-11-20T10:16:42.761858Z"
            ],
            [
                "uid" => "movie_reviews",
                "primaryKey" => null,
                "createdAt" => "2019-11-20T09:40:33.711324Z",
                "updatedAt" => "2019-11-20T10:16:42.761858Z"
            ],
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
        $client->expects(self::once())->method('deleteIndex')->willReturn([]);

        $orchestrator = new IndexOrchestrator($client, null, $logger);
        $orchestrator->removeIndex('test');
    }
}
