<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Index;

use MeiliSearchBundle\Index\SynonymsOrchestratorInterface;
use MeiliSearchBundle\Index\TraceableSynonymsOrchestrator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableSynonymsOrchestratorTest extends TestCase
{
    public function testOrchestratorCanGetSynonymsWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $orchestrator = $this->createMock(SynonymsOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('getSynonyms')->willReturn([
            'wolverine' => ['xmen', 'logan'],
        ]);

        $traceableOrchestrator = new TraceableSynonymsOrchestrator($orchestrator);
        $synonyms = $traceableOrchestrator->getSynonyms('foo');

        static::assertNotEmpty($synonyms);
        static::assertNotEmpty($traceableOrchestrator->getData()['fetchedSynonyms']);
        static::assertNotEmpty($traceableOrchestrator->getData()['fetchedSynonyms']['foo']);
    }

    public function testOrchestratorCanGetSynonymsWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $orchestrator = $this->createMock(SynonymsOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('getSynonyms')->willReturn([
            'wolverine' => ['xmen', 'logan'],
        ]);

        $traceableOrchestrator = new TraceableSynonymsOrchestrator($orchestrator, $logger);
        $synonyms = $traceableOrchestrator->getSynonyms('foo');

        static::assertNotEmpty($synonyms);
        static::assertNotEmpty($traceableOrchestrator->getData()['fetchedSynonyms']);
        static::assertNotEmpty($traceableOrchestrator->getData()['fetchedSynonyms']['foo']);
    }

    public function testOrchestratorCanUpdateSynonymsWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $orchestrator = $this->createMock(SynonymsOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('updateSynonyms');

        $traceableOrchestrator = new TraceableSynonymsOrchestrator($orchestrator);
        $traceableOrchestrator->updateSynonyms('foo', [
            'wolverine' => ['xmen'],
        ]);

        static::assertNotEmpty($traceableOrchestrator->getData()['updatedSynonyms']);
        static::assertNotEmpty($traceableOrchestrator->getData()['updatedSynonyms']['foo']);
    }

    public function testOrchestratorCanUpdateSynonymsWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $orchestrator = $this->createMock(SynonymsOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('updateSynonyms');

        $traceableOrchestrator = new TraceableSynonymsOrchestrator($orchestrator, $logger);
        $traceableOrchestrator->updateSynonyms('foo', [
            'wolverine' => ['xmen'],
        ]);

        static::assertNotEmpty($traceableOrchestrator->getData()['updatedSynonyms']);
        static::assertNotEmpty($traceableOrchestrator->getData()['updatedSynonyms']['foo']);
    }

    public function testOrchestratorCanResetSynonymsWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $orchestrator = $this->createMock(SynonymsOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('resetSynonyms');

        $traceableOrchestrator = new TraceableSynonymsOrchestrator($orchestrator);
        $traceableOrchestrator->resetSynonyms('foo');

        static::assertEmpty($traceableOrchestrator->getData()['fetchedSynonyms']);
        static::assertEmpty($traceableOrchestrator->getData()['updatedSynonyms']);
    }

    public function testOrchestratorCanResetSynonymsWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $orchestrator = $this->createMock(SynonymsOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('resetSynonyms');

        $traceableOrchestrator = new TraceableSynonymsOrchestrator($orchestrator, $logger);
        $traceableOrchestrator->resetSynonyms('foo');

        static::assertEmpty($traceableOrchestrator->getData()['fetchedSynonyms']);
        static::assertEmpty($traceableOrchestrator->getData()['updatedSynonyms']);
    }
}
