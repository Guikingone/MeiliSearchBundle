<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

use MeiliSearchBundle\Event\PostSearchEvent;
use MeiliSearchBundle\Event\PreSearchEvent;
use MeiliSearchBundle\Exception\RuntimeException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SearchEntryPoint
{
    /**
     * @var EventDispatcherInterface|null
     */
    private $eventDispatcher;

    /**
     * @var IndexOrchestratorInterface
     */
    private $indexOrchestrator;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    public function __construct(
        IndexOrchestratorInterface $indexOrchestrator,
        ?EventDispatcherInterface $eventDispatcher = null,
        ?LoggerInterface $logger = null
    ) {
        $this->indexOrchestrator = $indexOrchestrator;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $index, string $query, array $options = null): array
    {
        $index = $this->indexOrchestrator->getIndex($index);

        $this->dispatch(new PreSearchEvent([
            'index' => $index,
            'query' => $query,
            'options' => $options,
        ]));

        $this->logInfo('A query has been made', array_merge($options ?? [], ['index' => $index, 'query' => $query]));

        try {
            $result = $index->search($query, $options);
        } catch (Throwable $exception) {
            $this->logError('The query has failed', ['error' => $exception->getMessage()]);
            throw new RuntimeException($exception->getMessage());
        }

        $this->dispatch(new PostSearchEvent([
            'hits_count' => $result['nbHits'],
            'processing_time' => sprintf('%s milliseconds', $result['processingTimeMs']),
            'query' => $query,
        ]));

        return $result;
    }

    private function dispatch(Event $event): void
    {
        if (null === $this->eventDispatcher) {
            return;
        }

        $this->eventDispatcher->dispatch($event);
    }

    private function logError(string $message, array $context = []): void
    {
        if (null === $this->logger) {
            return;
        }

        $this->logger->error($message, $context);
    }

    private function logInfo(string $message, array $context = []): void
    {
        if (null === $this->logger) {
            return;
        }

        $this->logger->info($message, $context);
    }
}
