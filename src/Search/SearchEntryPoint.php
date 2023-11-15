<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Search;

use MeiliSearchBundle\Event\PostSearchEvent;
use MeiliSearchBundle\Event\PreSearchEvent;
use MeiliSearchBundle\Exception\RuntimeException;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Result\ResultBuilderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

use function sprintf;

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

    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly IndexOrchestratorInterface $indexOrchestrator,
        private readonly ?ResultBuilderInterface $resultBuilder = null,
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
        ?LoggerInterface $logger = null,
        private readonly ?string $prefix = null
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $index, string $query, array $options = []): SearchResultInterface
    {
        try {
            $index = $this->indexOrchestrator->getIndex(
                null === $this->prefix ? $index : sprintf('%s%s', $this->prefix, $index)
            );
        } catch (Throwable $throwable) {
            $this->logger->error('The search cannot occur as an error occurred when fetching the index', [
                self::INDEX => $index,
                self::ERROR => $throwable->getMessage(),
            ]);

            throw $throwable;
        }

        $this->dispatch(
            new PreSearchEvent([
                self::INDEX => $index,
                self::QUERY => $query,
                self::OPTIONS => $options,
            ])
        );

        $this->logger->info('A query has been made', [
            self::INDEX => $index,
            self::QUERY => $query,
        ]);

        try {
            /**
             * @var array{hits: array{int, array{mixed}}, offset: int, limit: int, nbHits: int, exhaustiveNbHits: bool, processingTimeMs: int, query: string} $result
             */
            $result = $index->search($query, $options);
        } catch (Throwable $throwable) {
            $this->logger->error('The query has failed', [
                self::ERROR => $throwable->getMessage(),
                self::QUERY => $query,
                self::OPTIONS => $options,
            ]);

            throw new RuntimeException($throwable->getMessage(), 0, $throwable);
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
            if (null === $this->resultBuilder) {
                continue;
            }
            if (!$this->resultBuilder->support($hit)) {
                continue;
            }
            $results[$key] = $this->resultBuilder->build($hit);
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
