<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\DataCollector;

use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Index\SynonymsOrchestratorInterface;
use MeiliSearchBundle\Index\TraceableSynonymsOrchestrator;
use MeiliSearchBundle\Search\SearchEntryPointInterface;
use MeiliSearchBundle\Document\TraceableDocumentEntryPoint;
use MeiliSearchBundle\Index\TraceableIndexOrchestrator;
use MeiliSearchBundle\Search\TraceableSearchEntryPoint;
use MeiliSearchBundle\DataCollector\MeiliSearchBundleDataCollector;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchBundleDataCollectorTest extends TestCase
{
    public function testCollectorIsConfigured(): void
    {
        $indexOrchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $traceableIndexOrchestrator = new TraceableIndexOrchestrator($indexOrchestrator);

        $documentOrchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($documentOrchestrator);

        $searchEntryPoint = $this->createMock(SearchEntryPointInterface::class);
        $traceableSearchEntryPoint = new TraceableSearchEntryPoint($searchEntryPoint);

        $synonymsOrchestrator = $this->createMock(SynonymsOrchestratorInterface::class);
        $traceableSynonymsOrchestrator = new TraceableSynonymsOrchestrator($synonymsOrchestrator);

        $collector = new MeiliSearchBundleDataCollector(
            $traceableIndexOrchestrator,
            $traceableDocumentOrchestrator,
            $traceableSearchEntryPoint,
            $traceableSynonymsOrchestrator
        );

        static::assertSame('meili_search', $collector->getName());
    }

    public function testCollectorCanCollect(): void
    {
        $indexOrchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $traceableIndexOrchestrator = new TraceableIndexOrchestrator($indexOrchestrator);
        $traceableIndexOrchestrator->addIndex('foo', 'id');
        $traceableIndexOrchestrator->removeIndex('bar');
        $traceableIndexOrchestrator->getIndex('bar');

        $documentOrchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($documentOrchestrator);

        $searchEntryPoint = $this->createMock(SearchEntryPointInterface::class);
        $traceableSearchEntryPoint = new TraceableSearchEntryPoint($searchEntryPoint);
        $traceableSearchEntryPoint->search('foo', 'q=bar');

        $synonymsOrchestrator = $this->createMock(SynonymsOrchestratorInterface::class);
        $synonymsOrchestrator->expects(self::once())->method('getSynonyms')->willReturn([
            'foo' => ['bar', 'foo'],
        ]);

        $traceableSynonymsOrchestrator = new TraceableSynonymsOrchestrator($synonymsOrchestrator);
        $traceableSynonymsOrchestrator->getSynonyms('foo');

        $collector = new MeiliSearchBundleDataCollector(
            $traceableIndexOrchestrator,
            $traceableDocumentOrchestrator,
            $traceableSearchEntryPoint,
            $traceableSynonymsOrchestrator
        );
        $collector->lateCollect();

        static::assertNotEmpty($collector->getCreatedIndexes());
        static::assertNotEmpty($collector->getDeletedIndexes());
        static::assertNotEmpty($collector->getFetchedIndexes());
        static::assertSame(1, $collector->getQueriesCount());
    }

    public function testCollectorCanCollectAndReset(): void
    {
        $indexOrchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $traceableIndexOrchestrator = new TraceableIndexOrchestrator($indexOrchestrator);
        $traceableIndexOrchestrator->addIndex('foo', 'id');
        $traceableIndexOrchestrator->removeIndex('bar');
        $traceableIndexOrchestrator->getIndex('bar');

        $documentOrchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($documentOrchestrator);

        $searchEntryPoint = $this->createMock(SearchEntryPointInterface::class);
        $traceableSearchEntryPoint = new TraceableSearchEntryPoint($searchEntryPoint);
        $traceableSearchEntryPoint->search('foo', 'q=bar');

        $synonymsOrchestrator = $this->createMock(SynonymsOrchestratorInterface::class);
        $synonymsOrchestrator->expects(self::once())->method('getSynonyms')->willReturn([
            'foo' => ['bar', 'foo'],
        ]);

        $traceableSynonymsOrchestrator = new TraceableSynonymsOrchestrator($synonymsOrchestrator);
        $traceableSynonymsOrchestrator->getSynonyms('foo');

        $collector = new MeiliSearchBundleDataCollector(
            $traceableIndexOrchestrator,
            $traceableDocumentOrchestrator,
            $traceableSearchEntryPoint,
            $traceableSynonymsOrchestrator
        );
        $collector->lateCollect();

        static::assertNotEmpty($collector->getCreatedIndexes());
        static::assertNotEmpty($collector->getDeletedIndexes());
        static::assertNotEmpty($collector->getFetchedIndexes());
        static::assertSame(1, $collector->getQueriesCount());
        static::assertNotEmpty($collector->getSynonyms());

        $collector->reset();

        static::assertEmpty($collector->getCreatedIndexes());
        static::assertEmpty($collector->getDeletedIndexes());
        static::assertEmpty($collector->getFetchedIndexes());
        static::assertSame(0, $collector->getQueriesCount());
        static::assertEmpty($collector->getSynonyms());
    }
}
