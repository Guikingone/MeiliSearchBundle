<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Index;

use Exception;
use MeiliSearch\Endpoints\Indexes;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Index\SynonymsOrchestrator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SynonymsOrchestratorTest extends TestCase
{
    public function testSynonymsCannotBeReturnedWithException(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $indexOrchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $indexOrchestrator->expects(self::once())->method('getIndex')->willThrowException(new Exception('An error occurred'));

        $orchestrator = new SynonymsOrchestrator($indexOrchestrator, $eventDispatcher, $logger);

        static::expectException(Exception::class);
        $orchestrator->getSynonyms('foo');
    }

    public function testSynonymsCanBeReturnedWhenEmpty(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getSynonyms')->willReturn([]);

        $indexOrchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $indexOrchestrator->expects(self::once())->method('getIndex')->willReturn($index);

        $orchestrator = new SynonymsOrchestrator($indexOrchestrator, $eventDispatcher, $logger);

        static::assertEmpty($orchestrator->getSynonyms('foo'));
    }

    public function testSynonymsCanBeReturned(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getSynonyms')->willReturn([
            'logan' => ['wolverine', 'xmen'],
        ]);

        $indexOrchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $indexOrchestrator->expects(self::once())->method('getIndex')->willReturn($index);

        $orchestrator = new SynonymsOrchestrator($indexOrchestrator, $eventDispatcher, $logger);

        static::assertArrayHasKey('logan', $orchestrator->getSynonyms('foo'));
    }

    public function testSynonymsCannotBeResetWithException(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $indexOrchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $indexOrchestrator->expects(self::once())->method('getIndex')->willThrowException(new Exception('An error occurred'));

        $orchestrator = new SynonymsOrchestrator($indexOrchestrator, $eventDispatcher, $logger);

        static::expectException(Exception::class);
        $orchestrator->resetSynonyms('foo');
    }

    public function testSynonymsCanBeReset(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::exactly(2))->method('dispatch');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('resetSynonyms')->willReturn([
            'updateId' => 1,
        ]);

        $indexOrchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $indexOrchestrator->expects(self::once())->method('getIndex')->willReturn($index);

        $orchestrator = new SynonymsOrchestrator($indexOrchestrator, $eventDispatcher, $logger);
        $orchestrator->resetSynonyms('foo');
    }
}
