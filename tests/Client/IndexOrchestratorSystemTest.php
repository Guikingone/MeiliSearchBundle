<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Client;

use MeiliSearch\Client;
use MeiliSearch\Index;
use MeiliSearchBundle\Client\IndexOrchestrator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexOrchestratorSystemTest extends TestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->client = new Client('http://meili:7700', 'masterKey');
    }

    public function testAllIndexesCanBeRetrieved(): void
    {
        $this->client->createIndex('foo');

        $orchestrator = new IndexOrchestrator($this->client);
        $indexes = $orchestrator->getIndexes();

        static::assertNotEmpty($indexes);
        static::assertArrayHasKey(0, $indexes);
        static::assertSame('foo', $indexes[0]['name']);
        static::assertSame('foo', $indexes[0]['uid']);

        $this->client->deleteAllIndexes();
    }

    public function testSingleIndexCanBeRetrieved(): void
    {
        $this->client->createIndex('foo');

        $orchestrator = new IndexOrchestrator($this->client);

        static::assertInstanceOf(Index::class, $orchestrator->getIndex('foo'));

        $this->client->deleteAllIndexes();
    }

    public function testAllIndexesCanBeRemoved(): void
    {
        $this->client->createIndex('foo');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');
        $logger->expects(self::once())->method('info');

        $orchestrator = new IndexOrchestrator($this->client, null, $logger);
        $orchestrator->removeIndexes();
    }

    public function testIndexCanBeDeleted(): void
    {
        $this->client->createIndex('foo');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');
        $logger->expects(self::once())->method('info');

        $orchestrator = new IndexOrchestrator($this->client, null, $logger);
        $orchestrator->removeIndex('foo');

        $this->client->deleteAllIndexes();
    }
}
