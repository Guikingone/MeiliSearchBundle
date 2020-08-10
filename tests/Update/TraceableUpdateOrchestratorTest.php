<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Update;

use MeiliSearchBundle\Update\TraceableUpdateOrchestrator;
use MeiliSearchBundle\Update\UpdateInterface;
use MeiliSearchBundle\Update\UpdateOrchestratorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableUpdateOrchestratorTest extends TestCase
{
    public function testOrchestratorCanGetUpdate(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $orchestrator = $this->createMock(UpdateOrchestratorInterface::class);

        $traceableOrchestrator = new TraceableUpdateOrchestrator($orchestrator);
        $traceableOrchestrator->getUpdate('foo', 1);

        static::assertNotEmpty($traceableOrchestrator->getData());
        static::assertNotEmpty($traceableOrchestrator->getData()['retrievedUpdates']);
        static::assertNotEmpty($traceableOrchestrator->getData()['retrievedUpdates']['foo']);
    }


    public function testOrchestratorCanGetUpdates(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $update = $this->createMock(UpdateInterface::class);

        $orchestrator = $this->createMock(UpdateOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('getUpdates')->willReturn([
            $update,
        ]);

        $traceableOrchestrator = new TraceableUpdateOrchestrator($orchestrator);
        $traceableOrchestrator->getUpdates('foo');

        static::assertNotEmpty($traceableOrchestrator->getData());
        static::assertNotEmpty($traceableOrchestrator->getData()['retrievedUpdates']);
        static::assertNotEmpty($traceableOrchestrator->getData()['retrievedUpdates']['foo']);
    }
}
