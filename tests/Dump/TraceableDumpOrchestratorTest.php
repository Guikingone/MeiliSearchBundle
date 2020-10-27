<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Dump;

use MeiliSearchBundle\Dump\DumpOrchestratorInterface;
use MeiliSearchBundle\Dump\TraceableDumpOrchestrator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableDumpOrchestratorTest extends TestCase
{
    public function testOrchestratorCannotCreateDumpWithException(): void
    {
        $orchestrator = $this->createMock(DumpOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('create')
            ->willThrowException(new RuntimeException('An error occurred'))
        ;

        $traceableDumpOrchestrator = new TraceableDumpOrchestrator($orchestrator);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('An error occurred');
        $traceableDumpOrchestrator->create();
    }

    public function testOrchestratorCanCreateDump(): void
    {
        $orchestrator = $this->createMock(DumpOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('create')->willReturn([
            'uid' => '1',
            'status' => 'done',
        ]);

        $traceableDumpOrchestrator = new TraceableDumpOrchestrator($orchestrator);

        $dump = $traceableDumpOrchestrator->create();

        static::assertArrayHasKey('uid', $dump);
        static::assertArrayHasKey('status', $dump);
        static::assertSame([
            'uid' => '1',
            'status' => 'done',
        ], $dump);
        static::assertArrayHasKey('createdDump', $traceableDumpOrchestrator->getData());
        static::assertNotEmpty($traceableDumpOrchestrator->getData()['createdDump']);
        static::assertArrayHasKey('uid', $traceableDumpOrchestrator->getData()['createdDump'][0]);
        static::assertArrayHasKey('status', $traceableDumpOrchestrator->getData()['createdDump'][0]);
    }

    public function testOrchestratorCannotRetrieveDumpStatusWithException(): void
    {
        $orchestrator = $this->createMock(DumpOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('getStatus')->with(self::equalTo('1'))
            ->willThrowException(new RuntimeException('An error occurred'))
        ;

        $traceableDumpOrchestrator = new TraceableDumpOrchestrator($orchestrator);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('An error occurred');
        $traceableDumpOrchestrator->getStatus('1');
    }

    public function testOrchestratorCanRetrieveDumpStatus(): void
    {
        $orchestrator = $this->createMock(DumpOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('getStatus')->with(self::equalTo('1'))->willReturn([
            'uid' => '1',
            'status' => 'done',
        ]);

        $traceableDumpOrchestrator = new TraceableDumpOrchestrator($orchestrator);

        $dump = $traceableDumpOrchestrator->getStatus('1');

        static::assertArrayHasKey('uid', $dump);
        static::assertArrayHasKey('status', $dump);
        static::assertSame([
            'uid' => '1',
            'status' => 'done',
        ], $dump);
        static::assertArrayHasKey('retrievedDump', $traceableDumpOrchestrator->getData());
        static::assertNotEmpty($traceableDumpOrchestrator->getData()['retrievedDump']);
        static::assertArrayHasKey('uid', $traceableDumpOrchestrator->getData()['retrievedDump'][0]);
        static::assertArrayHasKey('status', $traceableDumpOrchestrator->getData()['retrievedDump'][0]);
    }

    public function testOrchestratorCanCreateDumpAndReset(): void
    {
        $orchestrator = $this->createMock(DumpOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('create')->willReturn([
            'uid' => '1',
            'status' => 'done',
        ]);

        $traceableDumpOrchestrator = new TraceableDumpOrchestrator($orchestrator);

        $dump = $traceableDumpOrchestrator->create();

        static::assertArrayHasKey('uid', $dump);
        static::assertArrayHasKey('status', $dump);
        static::assertSame([
            'uid' => '1',
            'status' => 'done',
        ], $dump);
        static::assertArrayHasKey('createdDump', $traceableDumpOrchestrator->getData());
        static::assertNotEmpty($traceableDumpOrchestrator->getData()['createdDump']);
        static::assertArrayHasKey('uid', $traceableDumpOrchestrator->getData()['createdDump'][0]);
        static::assertArrayHasKey('status', $traceableDumpOrchestrator->getData()['createdDump'][0]);

        $traceableDumpOrchestrator->reset();
        static::assertArrayHasKey('createdDump', $traceableDumpOrchestrator->getData());
        static::assertEmpty($traceableDumpOrchestrator->getData()['createdDump']);
    }

    public function testOrchestratorCanRetrieveDumpAndReset(): void
    {
        $orchestrator = $this->createMock(DumpOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('getStatus')->with(self::equalTo('1'))->willReturn([
            'uid' => '1',
            'status' => 'done',
        ]);

        $traceableDumpOrchestrator = new TraceableDumpOrchestrator($orchestrator);

        $dump = $traceableDumpOrchestrator->getStatus('1');

        static::assertArrayHasKey('uid', $dump);
        static::assertArrayHasKey('status', $dump);
        static::assertSame([
            'uid' => '1',
            'status' => 'done',
        ], $dump);
        static::assertArrayHasKey('retrievedDump', $traceableDumpOrchestrator->getData());
        static::assertArrayHasKey('retrievedDump', $traceableDumpOrchestrator->getData());
        static::assertNotEmpty($traceableDumpOrchestrator->getData()['retrievedDump']);
        static::assertArrayHasKey('uid', $traceableDumpOrchestrator->getData()['retrievedDump'][0]);
        static::assertArrayHasKey('status', $traceableDumpOrchestrator->getData()['retrievedDump'][0]);

        $traceableDumpOrchestrator->reset();
        static::assertArrayHasKey('retrievedDump', $traceableDumpOrchestrator->getData());
        static::assertEmpty($traceableDumpOrchestrator->getData()['retrievedDump']);
    }
}
