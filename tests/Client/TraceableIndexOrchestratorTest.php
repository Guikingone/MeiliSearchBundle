<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Client;

use MeiliSearchBundle\Client\IndexOrchestratorInterface;
use MeiliSearchBundle\Client\TraceableIndexOrchestrator;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableIndexOrchestratorTest extends TestCase
{
    public function testIndexCanBeCreated(): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('addIndex');

        $traceableOrchestrator = new TraceableIndexOrchestrator($orchestrator);
        $traceableOrchestrator->addIndex('foo', 'bar');

        static::assertNotEmpty($traceableOrchestrator->getCreatedIndexes());
    }

    public function testIndexesCanBeRetrieved(): void
    {
    }

    public function testSingleIndexCanBeRetrieved(): void
    {
    }

    public function testIndexesCanBeRemoved(): void
    {
    }

    public function testSingleIndexCanBeRemoved(): void
    {
    }
}
