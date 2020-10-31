<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Dump;

use MeiliSearch\Client;
use MeiliSearchBundle\Dump\DumpOrchestrator;
use MeiliSearchBundle\Event\Dump\DumpCreatedEvent;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DumpOrchestratorTest extends TestCase
{
    public function testDumpCannotBeCreated(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('critical');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('createDump')
            ->willThrowException(new RuntimeException('An error occurred'))
        ;

        $orchestrator = new DumpOrchestrator($client, $eventDispatcher, $logger);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('An error occurred');
        $orchestrator->create();
    }

    public function testDumpCanBeCreatedWithoutEventDispatcher(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('critical');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('createDump')->willReturn([
            'uid' => '1',
            'status' => 'done',
        ]);

        $orchestrator = new DumpOrchestrator($client, null, $logger);
        $dump = $orchestrator->create();

        static::assertArrayHasKey('uid', $dump);
        static::assertArrayHasKey('status', $dump);
        static::assertSame([
            'uid' => '1',
            'status' => 'done',
        ], $dump);
    }

    public function testDumpCanBeCreated(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())->method('dispatch')->with(new DumpCreatedEvent('1'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('critical');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('createDump')->willReturn([
            'uid' => '1',
            'status' => 'done',
        ]);

        $orchestrator = new DumpOrchestrator($client, $eventDispatcher, $logger);
        $dump = $orchestrator->create();

        static::assertArrayHasKey('uid', $dump);
        static::assertArrayHasKey('status', $dump);
        static::assertSame([
            'uid' => '1',
            'status' => 'done',
        ], $dump);
    }

    public function testDumpStatusCannotBeRetrieved(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('critical');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getDumpStatus')->with(self::equalTo('foo'))
            ->willThrowException(new RuntimeException('An error occurred'))
        ;

        $orchestrator = new DumpOrchestrator($client, $eventDispatcher, $logger);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('An error occurred');
        $orchestrator->getStatus('foo');
    }

    public function testDumpStatusCanBeRetrieved(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('critical');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getDumpStatus')->with(self::equalTo('foo'))->willReturn([
            'uid' => '1',
            'status' => 'done',
        ]);

        $orchestrator = new DumpOrchestrator($client, $eventDispatcher, $logger);
        $status = $orchestrator->getStatus('foo');

        static::assertSame([
            'uid' => '1',
            'status' => 'done',
        ], $status);
    }
}
