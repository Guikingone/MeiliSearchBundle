<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DataCollector;

use MeiliSearchBundle\Event\Document\DocumentEventList;
use MeiliSearchBundle\Event\Document\DocumentEventListInterface;
use MeiliSearchBundle\Event\Index\IndexEventList;
use MeiliSearchBundle\Event\Index\IndexEventListInterface;
use MeiliSearchBundle\Event\SearchEventList;
use MeiliSearchBundle\Event\SearchEventListInterface;
use MeiliSearchBundle\Index\TraceableSynonymsOrchestrator;
use MeiliSearchBundle\Update\TraceableUpdateOrchestrator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;
use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchBundleDataCollector extends DataCollector implements LateDataCollectorInterface
{
    public const NAME = 'meilisearch';
    private const DOCUMENTS = 'documents';
    private const INDEXES = 'indexes';
    private const QUERIES = 'queries';
    private const SETTINGS = 'settings';
    private const SYNONYMS = 'synonyms';
    private const UPDATES = 'updates';

    /**
     * @var IndexEventListInterface
     */
    private $indexEventList;

    /**
     * @var DocumentEventListInterface
     */
    private $documentEventList;

    /**
     * @var SearchEventListInterface
     */
    private $searchEventList;

    /**
     * @var TraceableSynonymsOrchestrator
     */
    private $synonymsOrchestrator;

    /**
     * @var TraceableUpdateOrchestrator
     */
    private $updateOrchestrator;

    public function __construct(
        IndexEventListInterface $indexEventList,
        DocumentEventListInterface $documentEventList,
        SearchEventListInterface $searchEventList,
        TraceableSynonymsOrchestrator $synonymsOrchestrator,
        TraceableUpdateOrchestrator $updateOrchestrator
    ) {
        $this->indexEventList = $indexEventList;
        $this->documentEventList = $documentEventList;
        $this->searchEventList = $searchEventList;
        $this->synonymsOrchestrator = $synonymsOrchestrator;
        $this->updateOrchestrator = $updateOrchestrator;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, Throwable $exception = null): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function lateCollect(): void
    {
        $this->data[self::INDEXES] = $this->indexEventList;
        $this->data[self::QUERIES] = $this->searchEventList;
        $this->data[self::DOCUMENTS] = $this->documentEventList;
        $this->data[self::SETTINGS] = array_merge($this->indexEventList->getPreSettingsUpdateEvents(), $this->indexEventList->getPostSettingsUpdateEvents());
        $this->data[self::SYNONYMS] = $this->synonymsOrchestrator->getData();
        $this->data[self::UPDATES] = $this->updateOrchestrator->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->data[self::INDEXES] = new IndexEventList();
        $this->data[self::QUERIES] = new SearchEventList();
        $this->data[self::DOCUMENTS] = new DocumentEventList();
        $this->data[self::SETTINGS] = [];
        $this->data[self::SYNONYMS] = new IndexEventList();
        $this->data[self::UPDATES] = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    public function getDocuments(): DocumentEventListInterface
    {
        return $this->data[self::DOCUMENTS];
    }

    public function getIndexes(): IndexEventListInterface
    {
        return $this->data[self::INDEXES];
    }

    public function getSearches(): SearchEventListInterface
    {
        return $this->data[self::QUERIES];
    }

    /**
     * @return array<string, array>
     */
    public function getSynonyms(): array
    {
        return $this->data[self::SYNONYMS];
    }

    /**
     * @return array<string, array>
     */
    public function getSettings(): array
    {
        return $this->data[self::SETTINGS];
    }

    /**
     * @return array<string, array>
     */
    public function getUpdates(): array
    {
        return $this->data[self::UPDATES];
    }
}
