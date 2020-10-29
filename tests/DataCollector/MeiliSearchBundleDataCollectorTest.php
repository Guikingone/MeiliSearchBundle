<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\DataCollector;

use MeiliSearchBundle\Event\Document\DocumentEventListInterface;
use MeiliSearchBundle\Event\Index\IndexEventListInterface;
use MeiliSearchBundle\Event\SearchEventListInterface;
use MeiliSearchBundle\Index\SynonymsOrchestratorInterface;
use MeiliSearchBundle\Index\TraceableSynonymsOrchestrator;
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
        $documentList = $this->createMock(DocumentEventListInterface::class);
        $list = $this->createMock(IndexEventListInterface::class);
        $searchList = $this->createMock(SearchEventListInterface::class);

        $synonymsOrchestrator = $this->createMock(SynonymsOrchestratorInterface::class);
        $traceableSynonymsOrchestrator = new TraceableSynonymsOrchestrator($synonymsOrchestrator);

        $updateOrchestrator = $this->createMock(UpdateOrchestratorInterface::class);
        $traceableUpdateOrchestrator = new TraceableUpdateOrchestrator($updateOrchestrator);

        $collector = new MeiliSearchBundleDataCollector(
            $list,
            $documentList,
            $searchList,
            $traceableSynonymsOrchestrator,
            $traceableUpdateOrchestrator
        );

        static::assertSame('meilisearch', $collector->getName());
    }
}
