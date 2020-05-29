<?php

namespace MeiliSearchBundle\Tests\Command;

use MeiliSearchBundle\Client\MeiliClient;
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
     * @var MeiliClient|null
     */
    private $client;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->client = new MeiliClient('http://meili:7700');
        $this->client->deleteIndexes();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->client->deleteIndexes();
        $this->client = null;
    }

    public function testCommandCannotListEmptyIndexes(): void
    {
        $command = new ListIndexesCommand($this->client);
        $application = new Application();
        $application->add($command);
        $tester = new CommandTester($application->get('meili:list-indexes'));
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('No indexes found, please ensure that indexes have been created', $tester->getDisplay());
    }

    public function testCommandCanListIndexes(): void
    {
        $this->client->createIndex('test', 'test_test');

        $command = new ListIndexesCommand($this->client);
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
