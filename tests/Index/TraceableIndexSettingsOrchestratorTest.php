<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Index;

use MeiliSearchBundle\Index\IndexSettingsOrchestratorInterface;
use MeiliSearchBundle\Index\TraceableIndexSettingsOrchestrator;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableIndexSettingsOrchestratorTest extends TestCase
{
    public function testSettingsCanBeUpdated(): void
    {
        $orchestrator = $this->createMock(IndexSettingsOrchestratorInterface::class);

        $traceableOrchestrator = new TraceableIndexSettingsOrchestrator($orchestrator);
        $traceableOrchestrator->updateSettings('foo', []);

        static::assertNotEmpty($traceableOrchestrator->getData()['updatedSettings']);
        static::assertArrayHasKey('foo', $traceableOrchestrator->getData()['updatedSettings']);
    }

    public function testSettingsCanBeRetrieved(): void
    {
        $orchestrator = $this->createMock(IndexSettingsOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('retrieveSettings')->willReturn([
            'synonyms' => [],
        ]);

        $traceableOrchestrator = new TraceableIndexSettingsOrchestrator($orchestrator);
        $settings = $traceableOrchestrator->retrieveSettings('foo');

        static::assertArrayHasKey('synonyms', $settings);
        static::assertNotEmpty($traceableOrchestrator->getData()['retrievedSettings']);
        static::assertArrayHasKey('foo', $traceableOrchestrator->getData()['retrievedSettings']);
    }

    public function testSettingsCanBeReset(): void
    {
        $orchestrator = $this->createMock(IndexSettingsOrchestratorInterface::class);

        $traceableOrchestrator = new TraceableIndexSettingsOrchestrator($orchestrator);
        $traceableOrchestrator->resetSettings('foo');

        static::assertNotEmpty($traceableOrchestrator->getData()['resetSettings']);
        static::assertContainsEquals('foo', $traceableOrchestrator->getData()['resetSettings']);
    }

    public function testDataCanBeReset(): void
    {
        $orchestrator = $this->createMock(IndexSettingsOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('retrieveSettings')->willReturn([
            'synonyms' => [],
        ]);

        $traceableOrchestrator = new TraceableIndexSettingsOrchestrator($orchestrator);
        $traceableOrchestrator->updateSettings('foo', []);
        $traceableOrchestrator->retrieveSettings('foo');
        $traceableOrchestrator->resetSettings('foo');

        static::assertNotEmpty($traceableOrchestrator->getData()['updatedSettings']);
        static::assertNotEmpty($traceableOrchestrator->getData()['retrievedSettings']);
        static::assertNotEmpty($traceableOrchestrator->getData()['resetSettings']);

        $traceableOrchestrator->reset();

        static::assertEmpty($traceableOrchestrator->getData()['updatedSettings']);
        static::assertEmpty($traceableOrchestrator->getData()['retrievedSettings']);
        static::assertEmpty($traceableOrchestrator->getData()['resetSettings']);
    }
}
