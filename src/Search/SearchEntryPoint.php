<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Search;

use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Event\PostSearchEvent;
use MeiliSearchBundle\Event\PreSearchEvent;
use MeiliSearchBundle\Exception\RuntimeException;
use MeiliSearchBundle\Result\ResultBuilderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;
use function array_merge;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SearchEntryPoint implements SearchEntryPointInterface
{
    private const INDEX = 'index';
    private const ERROR = 'error';
    private const HITS = 'hits';
    private const QUERY = 'query';
    private const OPTIONS = 'options';

    /**
     * @var EventDispatcherInterface|null
     */
    private $eventDispatcher;

    /**
     * @var IndexOrchestratorInterface
     */
    private $indexOrchestrator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ResultBuilderInterface|null
     */
    private $resultBuilder;

    /**
     * @var string|null
     */
    private $prefix;

    public function __construct(
        IndexOrchestratorInterface $indexOrchestrator,
        ?ResultBuilderInterface $resultBuilder = null,
        ?EventDispatcherInterface $eventDispatcher = null,
        ?LoggerInterface $logger = null,
        ?string $prefix = null
    ) {
        $this->indexOrchestrator = $indexOrchestrator;
        $this->resultBuilder = $resultBuilder;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger ?: new NullLogger();
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $index, string $query, array $options = []): SearchResultInterface
    {
        try {
            $index = $this->indexOrchestrator->getIndex(null === $this->prefix ? $index : sprintf('%s%s', $this->prefix, $index));
        } catch (Throwable $throwable) {
            $this->logger->error('The search cannot occur as an error occurred when fetching the index', [
                self::INDEX => $index,
                self::ERROR => $throwable->getMessage(),
            ]);

            throw $throwable;
        }

        $this->dispatch(new PreSearchEvent([
            self::INDEX => $index,
            self::QUERY => $query,
            self::OPTIONS => $options,
        ]));

        $this->logger->info('A query has been made', array_merge($options, [
            self::INDEX => $index,
            self::QUERY => $query,
        ]));

        try {
            $result = $index->search($query, $options);
        } catch (Throwable $throwable) {
            $this->logger->error('The query has failed', [
                self::ERROR => $throwable->getMessage(),
                self::QUERY => $query,
                self::OPTIONS => $options,
            ]);

            throw new RuntimeException($throwable->getMessage());
        }

        $this->buildModels($result[self::HITS]);

        $searchResult = SearchResult::create(
            $result[self::HITS],
            $result['offset'],
            $result['limit'],
            $result['nbHits'],
            $result['exhaustiveNbHits'],
            $result['processingTimeMs'],
            $result[self::QUERY]
        );

        $this->dispatch(new PostSearchEvent($searchResult));

        return $searchResult;
    }

    private function buildModels(array &$results): void
    {
        foreach ($results as $key => $hit) {
            if (null !== $this->resultBuilder && $this->resultBuilder->support($hit)) {
                $results[$key] = $this->resultBuilder->build($hit);
            }
        }
    }

    private function dispatch(Event $event): void
    {
        if (null === $this->eventDispatcher) {
            return;
        }

        $this->eventDispatcher->dispatch($event);
    }
}
