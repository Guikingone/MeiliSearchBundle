<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\DataCollector;

use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Index\IndexSettingsOrchestratorInterface;
use MeiliSearchBundle\Index\SynonymsOrchestratorInterface;
use MeiliSearchBundle\Index\TraceableIndexSettingsOrchestrator;
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

        $indexSettingsOrchestrator = $this->createMock(IndexSettingsOrchestratorInterface::class);
        $traceableIndexSettingsOrchestrator = new TraceableIndexSettingsOrchestrator($indexSettingsOrchestrator);

        $documentOrchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($documentOrchestrator);

        $searchEntryPoint = $this->createMock(SearchEntryPointInterface::class);
        $traceableSearchEntryPoint = new TraceableSearchEntryPoint($searchEntryPoint);

        $synonymsOrchestrator = $this->createMock(SynonymsOrchestratorInterface::class);
        $traceableSynonymsOrchestrator = new TraceableSynonymsOrchestrator($synonymsOrchestrator);

        $collector = new MeiliSearchBundleDataCollector(
            $traceableIndexOrchestrator,
            $traceableIndexSettingsOrchestrator,
            $traceableDocumentOrchestrator,
            $traceableSearchEntryPoint,
            $traceableSynonymsOrchestrator
        );

        static::assertSame('meilisearch', $collector->getName());
    }

    public function testCollectorCanCollect(): void
    {
        $indexOrchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $traceableIndexOrchestrator = new TraceableIndexOrchestrator($indexOrchestrator);
        $traceableIndexOrchestrator->addIndex('foo', 'id');
        $traceableIndexOrchestrator->removeIndex('bar');
        $traceableIndexOrchestrator->getIndex('bar');

        $indexSettingsOrchestrator = $this->createMock(IndexSettingsOrchestratorInterface::class);
        $indexSettingsOrchestrator->expects(self::once())->method('resetSettings');
        $traceableIndexSettingsOrchestrator = new TraceableIndexSettingsOrchestrator($indexSettingsOrchestrator);
        $traceableIndexSettingsOrchestrator->resetSettings('foo');

        $documentOrchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($documentOrchestrator);

        $searchEntryPoint = $this->createMock(SearchEntryPointInterface::class);
        $traceableSearchEntryPoint = new TraceableSearchEntryPoint($searchEntryPoint);
        $traceableSearchEntryPoint->search('foo', 'bar');

        $synonymsOrchestrator = $this->createMock(SynonymsOrchestratorInterface::class);
        $synonymsOrchestrator->expects(self::once())->method('getSynonyms')->willReturn([
            'foo' => ['bar', 'foo'],
        ]);

        $traceableSynonymsOrchestrator = new TraceableSynonymsOrchestrator($synonymsOrchestrator);
        $traceableSynonymsOrchestrator->getSynonyms('foo');

        $collector = new MeiliSearchBundleDataCollector(
            $traceableIndexOrchestrator,
            $traceableIndexSettingsOrchestrator,
            $traceableDocumentOrchestrator,
            $traceableSearchEntryPoint,
            $traceableSynonymsOrchestrator
        );
        $collector->lateCollect();

        static::assertNotEmpty($collector->getIndexes()['data']['createdIndexes']);
        static::assertNotEmpty($collector->getIndexes()['data']['fetchedIndexes']);
        static::assertNotEmpty($collector->getIndexes()['data']['deletedIndexes']);
        static::assertSame(1, $collector->getQueries()['count']);
    }

    public function testCollectorCanCollectQueries(): void
    {
        $indexOrchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $traceableIndexOrchestrator = new TraceableIndexOrchestrator($indexOrchestrator);

        $indexSettingsOrchestrator = $this->createMock(IndexSettingsOrchestratorInterface::class);
        $traceableIndexSettingsOrchestrator = new TraceableIndexSettingsOrchestrator($indexSettingsOrchestrator);

        $documentOrchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($documentOrchestrator);

        $searchEntryPoint = $this->createMock(SearchEntryPointInterface::class);
        $traceableSearchEntryPoint = new TraceableSearchEntryPoint($searchEntryPoint);
        $traceableSearchEntryPoint->search('foo', 'bar');

        $synonymsOrchestrator = $this->createMock(SynonymsOrchestratorInterface::class);
        $traceableSynonymsOrchestrator = new TraceableSynonymsOrchestrator($synonymsOrchestrator);

        $collector = new MeiliSearchBundleDataCollector(
            $traceableIndexOrchestrator,
            $traceableIndexSettingsOrchestrator,
            $traceableDocumentOrchestrator,
            $traceableSearchEntryPoint,
            $traceableSynonymsOrchestrator
        );
        $collector->lateCollect();

        static::assertSame(1, $collector->getQueries()['count']);
    }

    public function testCollectorCanCollectDocuments(): void
    {
        $indexOrchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $traceableIndexOrchestrator = new TraceableIndexOrchestrator($indexOrchestrator);

        $indexSettingsOrchestrator = $this->createMock(IndexSettingsOrchestratorInterface::class);
        $traceableIndexSettingsOrchestrator = new TraceableIndexSettingsOrchestrator($indexSettingsOrchestrator);

        $documentOrchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $documentOrchestrator->expects(self::once())->method('getDocuments')->willReturn([
            'id' => 25684,
            'title' => "American Ninja 5",
            'poster' => "https://image.tmdb.org/t/p/w1280/iuAQVI4mvjI83wnirpD8GVNRVuY.jpg",
            'overview' => "When a scientists daughter is kidnapped, American Ninja, attempts to find her, but this time he teams up with a youngster he has trained in the ways of the ninja.",
            'release_date' => "1993-01-01",
        ]);
        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($documentOrchestrator);
        $traceableDocumentOrchestrator->getDocuments('foo');

        $searchEntryPoint = $this->createMock(SearchEntryPointInterface::class);
        $traceableSearchEntryPoint = new TraceableSearchEntryPoint($searchEntryPoint);

        $synonymsOrchestrator = $this->createMock(SynonymsOrchestratorInterface::class);

        $traceableSynonymsOrchestrator = new TraceableSynonymsOrchestrator($synonymsOrchestrator);
        $traceableSynonymsOrchestrator->getSynonyms('foo');

        $collector = new MeiliSearchBundleDataCollector(
            $traceableIndexOrchestrator,
            $traceableIndexSettingsOrchestrator,
            $traceableDocumentOrchestrator,
            $traceableSearchEntryPoint,
            $traceableSynonymsOrchestrator
        );
        $collector->lateCollect();

        static::assertNotEmpty($collector->getDocuments());
        static::assertArrayHasKey('retrievedDocuments', $collector->getDocuments());
    }

    public function testCollectorCanCollectAndReset(): void
    {
        $indexOrchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $traceableIndexOrchestrator = new TraceableIndexOrchestrator($indexOrchestrator);
        $traceableIndexOrchestrator->addIndex('foo', 'id');
        $traceableIndexOrchestrator->removeIndex('bar');
        $traceableIndexOrchestrator->getIndex('bar');

        $indexSettingsOrchestrator = $this->createMock(IndexSettingsOrchestratorInterface::class);
        $indexSettingsOrchestrator->expects(self::once())->method('resetSettings');
        $traceableIndexSettingsOrchestrator = new TraceableIndexSettingsOrchestrator($indexSettingsOrchestrator);
        $traceableIndexSettingsOrchestrator->resetSettings('foo');

        $documentOrchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $documentOrchestrator->expects(self::once())->method('getDocuments')->willReturn([
            'id' => 25684,
            'title' => "American Ninja 5",
            'poster' => "https://image.tmdb.org/t/p/w1280/iuAQVI4mvjI83wnirpD8GVNRVuY.jpg",
            'overview' => "When a scientists daughter is kidnapped, American Ninja, attempts to find her, but this time he teams up with a youngster he has trained in the ways of the ninja.",
            'release_date' => "1993-01-01",
        ]);
        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($documentOrchestrator);
        $traceableDocumentOrchestrator->getDocuments('foo');

        $searchEntryPoint = $this->createMock(SearchEntryPointInterface::class);
        $traceableSearchEntryPoint = new TraceableSearchEntryPoint($searchEntryPoint);
        $traceableSearchEntryPoint->search('foo', 'bar');

        $synonymsOrchestrator = $this->createMock(SynonymsOrchestratorInterface::class);
        $synonymsOrchestrator->expects(self::once())->method('getSynonyms')->willReturn([
            'foo' => ['bar', 'foo'],
        ]);

        $traceableSynonymsOrchestrator = new TraceableSynonymsOrchestrator($synonymsOrchestrator);
        $traceableSynonymsOrchestrator->getSynonyms('foo');

        $collector = new MeiliSearchBundleDataCollector(
            $traceableIndexOrchestrator,
            $traceableIndexSettingsOrchestrator,
            $traceableDocumentOrchestrator,
            $traceableSearchEntryPoint,
            $traceableSynonymsOrchestrator
        );
        $collector->lateCollect();

        static::assertNotEmpty($collector->getIndexes()['data']['createdIndexes']);
        static::assertNotEmpty($collector->getIndexes()['data']['fetchedIndexes']);
        static::assertNotEmpty($collector->getIndexes()['data']['deletedIndexes']);
        static::assertNotEmpty($collector->getQueries());
        static::assertSame(1, $collector->getQueries()['count']);
        static::assertNotEmpty($collector->getDocuments());
        static::assertNotEmpty($collector->getSynonyms());
        static::assertNotEmpty($collector->getSettings());

        $collector->reset();

        static::assertEmpty($collector->getIndexes()['data']);
        static::assertSame(0, $collector->getIndexes()['count']);
        static::assertEmpty($collector->getQueries()['data']);
        static::assertSame(0, $collector->getQueries()['count']);
        static::assertEmpty($collector->getDocuments());
        static::assertEmpty($collector->getSynonyms());
        static::assertEmpty($collector->getSettings());
    }
}
