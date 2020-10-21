<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Dump;

use MeiliSearch\Client;
use MeiliSearchBundle\Dump\DumpOrchestrator;
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
        $logger->expects(self::once())->method('critical')
            ->with(self::equalTo('An error occurred when trying to create a new dump'), [
                'error' => 'An error occurred',
                'trace' => '',
            ])
        ;

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('createDump')->willThrowException(new RuntimeException('An error occurred'));

        $orchestrator = new DumpOrchestrator($client, $eventDispatcher, $logger);
        $orchestrator->create();
    }

    public function testDumpCanBeCreated(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('critical');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('createDump')->willReturn([
            'uid' => 1,
        ]);

        $orchestrator = new DumpOrchestrator($client, $eventDispatcher, $logger);
        $orchestrator->create();
    }

    public function testDumpStatusCannotBeRetrieved(): void
    {

    }

    public function testDumpStatusCanBeRetrieved(): void
    {

    }
}