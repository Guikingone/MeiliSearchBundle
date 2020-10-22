<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Command;

use Exception;
use MeiliSearchBundle\Command\WarmIndexesCommand;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Metadata\IndexMetadata;
use MeiliSearchBundle\Metadata\IndexMetadataRegistry;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class WarmIndexesCommandTest extends TestCase
{
    public function testCommandIsConfigured(): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);

        $command = new WarmIndexesCommand([], new IndexMetadataRegistry(), $orchestrator);

        static::assertSame('meili:warm-indexes', $command->getName());
        static::assertSame('Allow to warm the indexes defined in the configuration', $command->getDescription());
    }

    public function testCommandCannotWarmEmptyIndexes(): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);

        $command = new WarmIndexesCommand([], new IndexMetadataRegistry(), $orchestrator);
        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString('No indexes found, please define at least a single index', $tester->getDisplay());
    }

    public function testCommandCannotWarmAsyncIndexWithoutMessageBus(): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);

        $command = new WarmIndexesCommand([
            'foo' => [
                'primaryKey' => 'id',
                'async' => true,
                'synonyms' => [],
            ]
        ], new IndexMetadataRegistry(), $orchestrator);
        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString('The "async" attribute cannot be used when Messenger is not installed', $tester->getDisplay());
        static::assertStringContainsString('Consider using "composer require symfony/messenger"', $tester->getDisplay());
    }

    public function testCommandCanWarmAsyncIndexWithMessageBus(): void
    {
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects(self::once())->method('dispatch')->willReturn(Envelope::wrap(new stdClass()));

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);

        $command = new WarmIndexesCommand([
            'foo' => [
                'primaryKey' => 'id',
                'async' => true,
                'synonyms' => [],
            ]
        ], new IndexMetadataRegistry(), $orchestrator, $messageBus);
        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('The indexes has been warmed, feel free to query them!', $tester->getDisplay());
    }

    public function testCommandCannotWarmIndexesWithException(): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('addIndex')->willThrowException(new Exception('An error occurred'));

        $command = new WarmIndexesCommand([
            'foo' => [
                'primaryKey' => 'id',
                'synonyms' => [],
            ]
        ], new IndexMetadataRegistry(), $orchestrator);
        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString('The indexes cannot be warmed!', $tester->getDisplay());
        static::assertStringContainsString('Error: "An error occurred"', $tester->getDisplay());
    }

    public function testCommandCanWarmIndexesWithPrefix(): void
    {
        $registry = new IndexMetadataRegistry();

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('addIndex');

        $command = new WarmIndexesCommand([
            'foo' => [
                'primaryKey' => 'id',
                'synonyms' => [],
            ]
        ], $registry, $orchestrator, null, 'foo');
        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('The indexes has been warmed, feel free to query them!', $tester->getDisplay());
        static::assertNotEmpty($registry->toArray());
        static::assertInstanceOf(IndexMetadata::class, $registry->get('foo_foo'));
    }

    public function testCommandCanWarmIndexes(): void
    {
        $registry = new IndexMetadataRegistry();

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('addIndex');

        $command = new WarmIndexesCommand([
            'foo' => [
                'primaryKey' => 'id',
                'synonyms' => [],
            ]
        ], $registry, $orchestrator);
        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('The indexes has been warmed, feel free to query them!', $tester->getDisplay());
        static::assertNotEmpty($registry->toArray());
        static::assertInstanceOf(IndexMetadata::class, $registry->get('foo'));
    }
}

final class IndexesMessageBus implements MessageBusInterface
{
    public function dispatch($message, array $stamps = []): Envelope
    {
        return new Envelope($message, $stamps);
    }
}
