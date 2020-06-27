<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\DataCollector;

use MeiliSearchBundle\Client\DocumentOrchestratorInterface;
use MeiliSearchBundle\Client\IndexOrchestratorInterface;
use MeiliSearchBundle\Client\InstanceProbeInterface;
use MeiliSearchBundle\Client\SearchEntryPointInterface;
use MeiliSearchBundle\Client\TraceableDocumentOrchestrator;
use MeiliSearchBundle\Client\TraceableIndexOrchestrator;
use MeiliSearchBundle\Client\TraceableSearchEntryPoint;
use MeiliSearchBundle\DataCollector\MeiliSearchBundleDataCollector;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchBundleDataCollectorTest extends TestCase
{
    public function testCollectorIsConfigured(): void
    {
        $probe = $this->createMock(InstanceProbeInterface::class);

        $indexOrchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $traceableIndexOrchestrator = new TraceableIndexOrchestrator($indexOrchestrator);

        $documentOrchestrator = $this->createMock(DocumentOrchestratorInterface::class);
        $traceableDocumentOrchestrator = new TraceableDocumentOrchestrator($documentOrchestrator);

        $searchEntryPoint = $this->createMock(SearchEntryPointInterface::class);
        $traceableSearchEntryPoint = new TraceableSearchEntryPoint($searchEntryPoint);

        $collector = new MeiliSearchBundleDataCollector($probe, $traceableIndexOrchestrator, $traceableDocumentOrchestrator, $traceableSearchEntryPoint);
        static::assertSame('meili', $collector->getName());
    }

    public function testCollectorCanRetrieveSystemInformations(): void
    {
        $probe = $this->createMock(InstanceProbeInterface::class);
        $probe->expects(self::once())->method('getSystemInformations')->willReturn([
            "memoryUsage" => "56.3 %",
            "processorUsage" => [
                "0.0 %",
                "25.0 %",
                "4.5 %",
                "20.7 %",
                "4.0 %",
                "18.1 %",
                "3.7 %",
                "14.8 %",
                "3.4 %",
            ],
            "global" => [
                "totalMemory" => "17.18 GB",
                "usedMemory" => "9.67 GB",
                "totalSwap" => "4.29 GB",
                "usedSwap" => "2.58 GB",
                "inputData" => "29.82 GB",
                "outputData" => "4.22 GB"
            ],
            "process" => [
                "memory" => "5.2 MB",
                "cpu" => "0.0 %"
            ],
        ]);

        $indexOrchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $traceableIndexOrchestrator = new TraceableIndexOrchestrator($indexOrchestrator);

        $documentOrchestrator = $this->createMock(DocumentOrchestratorInterface::class);
        $traceableDocumentOrchestrator = new TraceableDocumentOrchestrator($documentOrchestrator);

        $searchEntryPoint = $this->createMock(SearchEntryPointInterface::class);
        $traceableSearchEntryPoint = new TraceableSearchEntryPoint($searchEntryPoint);

        $collector = new MeiliSearchBundleDataCollector($probe, $traceableIndexOrchestrator, $traceableDocumentOrchestrator, $traceableSearchEntryPoint);

        $collector->lateCollect();
        static::assertNotEmpty($collector->getSystemInformations());
    }

    public function testCollectorCanCollect(): void
    {
    }

    public function testCollectorCanCollectAndReset(): void
    {
    }
}
