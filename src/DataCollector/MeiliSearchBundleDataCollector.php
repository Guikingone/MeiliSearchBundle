<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DataCollector;

use MeiliSearchBundle\Document\TraceableDocumentEntryPoint;
use MeiliSearchBundle\Index\TraceableIndexOrchestrator;
use MeiliSearchBundle\Index\TraceableIndexSettingsOrchestrator;
use MeiliSearchBundle\Index\TraceableSynonymsOrchestrator;
use MeiliSearchBundle\Search\TraceableSearchEntryPoint;
use MeiliSearchBundle\Update\TraceableUpdateOrchestrator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;
use Throwable;
use function count;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchBundleDataCollector extends DataCollector implements LateDataCollectorInterface
{
    private const NAME = 'meilisearch';
    private const DOCUMENTS = 'documents';
    private const INDEXES = 'indexes';
    private const QUERIES = 'queries';
    private const SETTINGS = 'settings';
    private const SYNONYMS = 'synonyms';
    private const UPDATES = 'updates';
    private const COUNT = 'count';
    private const DATA = 'data';

    /**
     * @var TraceableIndexOrchestrator
     */
    private $indexOrchestrator;

    /**
     * @var TraceableIndexSettingsOrchestrator
     */
    private $indexSettingsOrchestrator;

    /**
     * @var TraceableDocumentEntryPoint
     */
    private $documentOrchestrator;

    /**
     * @var TraceableSearchEntryPoint
     */
    private $searchEntryPoint;

    /**
     * @var TraceableSynonymsOrchestrator
     */
    private $synonymsOrchestrator;

    /**
     * @var TraceableUpdateOrchestrator
     */
    private $updateOrchestrator;

    public function __construct(
        TraceableIndexOrchestrator $indexOrchestrator,
        TraceableIndexSettingsOrchestrator $indexSettingsOrchestrator,
        TraceableDocumentEntryPoint $documentOrchestrator,
        TraceableSearchEntryPoint $searchEntryPoint,
        TraceableSynonymsOrchestrator $synonymsOrchestrator,
        TraceableUpdateOrchestrator $updateOrchestrator
    ) {
        $this->indexOrchestrator = $indexOrchestrator;
        $this->indexSettingsOrchestrator = $indexSettingsOrchestrator;
        $this->documentOrchestrator = $documentOrchestrator;
        $this->searchEntryPoint = $searchEntryPoint;
        $this->synonymsOrchestrator = $synonymsOrchestrator;
        $this->updateOrchestrator = $updateOrchestrator;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, Throwable $exception = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function lateCollect(): void
    {
        $this->data[self::INDEXES] = [
            self::COUNT => count($this->indexOrchestrator->getIndexes()),
            self::DATA => $this->indexOrchestrator->getData(),
        ];
        $this->data[self::QUERIES] = [
            self::COUNT => count($this->searchEntryPoint->getData()),
            self::DATA => $this->searchEntryPoint->getData(),
        ];
        $this->data[self::DOCUMENTS] = $this->documentOrchestrator->getData();
        $this->data[self::SETTINGS] = $this->indexSettingsOrchestrator->getData();
        $this->data[self::SYNONYMS] = $this->synonymsOrchestrator->getData();
        $this->data[self::UPDATES] = $this->updateOrchestrator->getData();
    }

    public function reset(): void
    {
        $this->data[self::INDEXES] = [
            self::COUNT => 0,
            self::DATA => [],
        ];
        $this->data[self::QUERIES] = [
            self::COUNT => 0,
            self::DATA => [],
        ];
        $this->data[self::DOCUMENTS] = [];
        $this->data[self::SETTINGS] = [];
        $this->data[self::SYNONYMS] = [];
        $this->data[self::UPDATES] = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return array<string,array>
     */
    public function getDocuments(): array
    {
        return $this->data[self::DOCUMENTS];
    }

    /**
     * @return array<string,array|int>
     */
    public function getIndexes(): array
    {
        return $this->data[self::INDEXES];
    }

    /**
     * @return array<string,array|int>
     */
    public function getQueries(): array
    {
        return $this->data[self::QUERIES];
    }

    /**
     * @return array<string,array>
     */
    public function getSynonyms(): array
    {
        return $this->data[self::SYNONYMS];
    }

    /**
     * @return array<string,array>
     */
    public function getSettings(): array
    {
        return $this->data[self::SETTINGS];
    }

    /**
     * @return array<string,array>
     */
    public function getUpdates(): array
    {
        return $this->data[self::UPDATES];
    }
}
