<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DataCollector;

use Countable;
use MeiliSearchBundle\Document\TraceableDocumentEntryPoint;
use MeiliSearchBundle\Index\TraceableIndexOrchestrator;
use MeiliSearchBundle\Index\TraceableSynonymsOrchestrator;
use MeiliSearchBundle\Search\TraceableSearchEntryPoint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;
use function count;
use function is_array;

/**s
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchBundleDataCollector extends DataCollector implements LateDataCollectorInterface
{
    private const NAME = 'meili_search';
    private const INDEXES = 'indexes';
    private const CREATED_INDEXES = 'created_indexes';
    private const DELETED_INDEXES = 'deleted_indexes';
    private const FETCHED_INDEXES = 'fetched_indexes';
    private const QUERIES = 'queries';
    private const SYNONYMS = 'synonyms';

    /**
     * @var TraceableIndexOrchestrator
     */
    private $indexOrchestrator;

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

    public function __construct(
        TraceableIndexOrchestrator $indexOrchestrator,
        TraceableDocumentEntryPoint $documentOrchestrator,
        TraceableSearchEntryPoint $searchEntryPoint,
        TraceableSynonymsOrchestrator $synonymsOrchestrator
    ) {
        $this->indexOrchestrator = $indexOrchestrator;
        $this->documentOrchestrator = $documentOrchestrator;
        $this->searchEntryPoint = $searchEntryPoint;
        $this->synonymsOrchestrator = $synonymsOrchestrator;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function lateCollect(): void
    {
        $this->data[self::INDEXES] = $this->indexOrchestrator->getIndexes();
        $this->data[self::CREATED_INDEXES] = $this->indexOrchestrator->getCreatedIndexes();
        $this->data[self::DELETED_INDEXES] = $this->indexOrchestrator->getDeletedIndexes();
        $this->data[self::FETCHED_INDEXES] = $this->indexOrchestrator->getFetchedIndexes();
        $this->data[self::QUERIES] = $this->searchEntryPoint->getSearch();
        $this->data[self::SYNONYMS] = $this->synonymsOrchestrator->getData();
    }

    public function reset(): void
    {
        $this->data[self::INDEXES] = [];
        $this->data[self::CREATED_INDEXES] = [];
        $this->data[self::DELETED_INDEXES] = [];
        $this->data[self::FETCHED_INDEXES] = [];
        $this->data[self::QUERIES] = [];
        $this->data[self::SYNONYMS] = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    public function getIndexes(): array
    {
        return $this->data[self::INDEXES];
    }

    public function getCreatedIndexes(): array
    {
        return $this->data[self::CREATED_INDEXES];
    }

    public function getDeletedIndexes(): array
    {
        return $this->data[self::DELETED_INDEXES];
    }

    public function getFetchedIndexes(): array
    {
        return $this->data[self::FETCHED_INDEXES];
    }

    public function getQueries(): array
    {
        return $this->data[self::QUERIES];
    }

    public function getQueriesCount(): int
    {
        return is_array($this->data[self::QUERIES]) || $this->data[self::QUERIES] instanceof Countable ? count($this->data[self::QUERIES]) : 0;
    }

    /**
     * @return array<string,array>
     */
    public function getSynonyms(): array
    {
        return $this->data[self::SYNONYMS];
    }
}
