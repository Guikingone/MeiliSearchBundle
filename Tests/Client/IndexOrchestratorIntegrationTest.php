<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Tests\Client;

use MeiliSearch\Client;
use MeiliSearch\Index;
use MeiliSearchBundle\Client\IndexOrchestrator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexOrchestratorIntegrationTest extends TestCase
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
        $this->client = new Client('http://meili:7700');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->client->deleteAllIndexes();
    }

    public function testAllIndexCanBeRetrieved(): void
    {
        $this->client->createIndex('foo');

        $orchestrator = new IndexOrchestrator($this->client);

        static::assertInstanceOf(Index::class, $orchestrator->getIndex('foo'));
    }

    public function testIndexCanBeDeleted(): void
    {
        $this->client->createIndex('foo');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');
        $logger->expects(self::once())->method('info');

        $orchestrator = new IndexOrchestrator($this->client, null, $logger);
        $orchestrator->removeIndex('foo');
    }
}
