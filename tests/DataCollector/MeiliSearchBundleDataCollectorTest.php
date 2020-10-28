<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\DataCollector;

use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Event\Index\IndexEventListInterface;
use MeiliSearchBundle\Event\SearchEventListInterface;
use MeiliSearchBundle\Index\SynonymsOrchestratorInterface;
use MeiliSearchBundle\Index\TraceableSynonymsOrchestrator;
use MeiliSearchBundle\Document\TraceableDocumentEntryPoint;
use MeiliSearchBundle\DataCollector\MeiliSearchBundleDataCollector;
use MeiliSearchBundle\Update\TraceableUpdateOrchestrator;
use MeiliSearchBundle\Update\UpdateOrchestratorInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchBundleDataCollectorTest extends TestCase
{
    public function testCollectorIsConfigured(): void
    {
        $list = $this->createMock(IndexEventListInterface::class);
        $searchList = $this->createMock(SearchEventListInterface::class);

        $documentOrchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($documentOrchestrator);

        $synonymsOrchestrator = $this->createMock(SynonymsOrchestratorInterface::class);
        $traceableSynonymsOrchestrator = new TraceableSynonymsOrchestrator($synonymsOrchestrator);

        $updateOrchestrator = $this->createMock(UpdateOrchestratorInterface::class);
        $traceableUpdateOrchestrator = new TraceableUpdateOrchestrator($updateOrchestrator);

        $collector = new MeiliSearchBundleDataCollector(
            $list,
            $traceableDocumentOrchestrator,
            $searchList,
            $traceableSynonymsOrchestrator,
            $traceableUpdateOrchestrator
        );

        static::assertSame('meilisearch', $collector->getName());
    }
}
