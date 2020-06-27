<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Command;

use MeiliSearch\Client;
use MeiliSearch\Index;
use MeiliSearchBundle\Client\IndexOrchestrator;
use MeiliSearchBundle\Command\CreateIndexCommand;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class CreateIndexesCommandTest extends TestCase
{
    public function testCommandIsConfigured(): void
    {
        $client = $this->createMock(Client::class);

        $orchestrator = new IndexOrchestrator($client);

        $command = new CreateIndexCommand($orchestrator);

        static::assertSame('meili:create-index', $command->getName());
        static::assertNotEmpty($command->getDefinition());
    }

    public function testIndexCannotBeCreatedWithException(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('createIndex')->willThrowException(new \Exception('An error occurred'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $orchestrator = new IndexOrchestrator($client, $eventDispatcher, $logger);

        $command = new CreateIndexCommand($orchestrator);
        $application = new Application();
        $application->add($command);
        $tester = new CommandTester($application->get('meili:create-index'));
        $tester->execute([
            'uid' => 'foo',
        ]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString('The index cannot be created, error: "An error occurred"', $tester->getDisplay());
    }

    public function testIndexCanBeCreatedWithoutPrimaryKey(): void
    {
        $index = $this->createMock(Index::class);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('createIndex')->willReturn($index);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');
        $logger->expects(self::once())->method('info');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())->method('dispatch');

        $orchestrator = new IndexOrchestrator($client, $eventDispatcher, $logger);

        $command = new CreateIndexCommand($orchestrator);
        $application = new Application();
        $application->add($command);
        $tester = new CommandTester($application->get('meili:create-index'));
        $tester->execute([
            'uid' => 'foo',
        ]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('The "foo" index has been created', $tester->getDisplay());
    }

    public function testIndexCanBeCreatedWithPrimaryKey(): void
    {
        $index = $this->createMock(Index::class);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('createIndex')->willReturn($index);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');
        $logger->expects(self::once())->method('info');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())->method('dispatch');

        $orchestrator = new IndexOrchestrator($client, $eventDispatcher, $logger);

        $command = new CreateIndexCommand($orchestrator);
        $application = new Application();
        $application->add($command);
        $tester = new CommandTester($application->get('meili:create-index'));
        $tester->execute([
            'uid' => 'foo',
            '--primary_key' => 'bar',
        ]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('The "foo" index has been created', $tester->getDisplay());
    }

    public function testIndexCanBeCreatedWithPrimaryKeyShortcut(): void
    {
        $index = $this->createMock(Index::class);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('createIndex')->willReturn($index);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');
        $logger->expects(self::once())->method('info');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())->method('dispatch');

        $orchestrator = new IndexOrchestrator($client, $eventDispatcher, $logger);

        $command = new CreateIndexCommand($orchestrator);
        $application = new Application();
        $application->add($command);
        $tester = new CommandTester($application->get('meili:create-index'));
        $tester->execute([
            'uid' => 'foo',
            '-p' => 'bar',
        ]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('The "foo" index has been created', $tester->getDisplay());
    }
}
