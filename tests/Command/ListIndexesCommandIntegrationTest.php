<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Tests\Command;

use MeiliSearch\Client;
use MeiliSearchBundle\Client\IndexOrchestrator;
use MeiliSearchBundle\Command\ListIndexesCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ListIndexesCommandIntegrationTest extends TestCase
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
        $this->client->deleteAllIndexes();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->client->deleteAllIndexes();
    }

    public function testCommandCannotListEmptyIndexes(): void
    {
        $orchestrator = new IndexOrchestrator($this->client);

        $command = new ListIndexesCommand($orchestrator);
        $application = new Application();
        $application->add($command);
        $tester = new CommandTester($application->get('meili:list-indexes'));
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('No indexes found, please ensure that indexes have been created', $tester->getDisplay());
    }

    public function testCommandCanListIndexes(): void
    {
        $this->client->createIndex(['uid' => 'test', 'primaryKey' => 'test_test']);

        $orchestrator = new IndexOrchestrator($this->client);

        $command = new ListIndexesCommand($orchestrator);
        $application = new Application();
        $application->add($command);
        $tester = new CommandTester($application->get('meili:list-indexes'));
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('The following indexes have been found:', $tester->getDisplay());
        static::assertStringContainsString('Uid', $tester->getDisplay());
        static::assertStringContainsString('PrimaryKey', $tester->getDisplay());
        static::assertStringContainsString('CreatedAt', $tester->getDisplay());
        static::assertStringContainsString('UpdatedAt', $tester->getDisplay());

        static::assertStringContainsString('test', $tester->getDisplay());
        static::assertStringContainsString('test_test', $tester->getDisplay());
    }
}
