<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Index;

use MeiliSearchBundle\Event\Synonyms\PostResetSynonymsEvent;
use MeiliSearchBundle\Event\Synonyms\PostUpdateSynonymsEvent;
use MeiliSearchBundle\Event\Synonyms\PreResetSynonymsEvent;
use MeiliSearchBundle\Event\Synonyms\PreUpdateSynonymsEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SynonymsOrchestrator implements SynonymsOrchestratorInterface
{
    private const ERROR_LOG_KEY = 'error';

    private const INDEX = 'index';

    private const UPDATE_KEY = 'updateId';

    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly IndexOrchestratorInterface $indexOrchestrator,
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function getSynonyms(string $uid): array
    {
        try {
            $index = $this->indexOrchestrator->getIndex($uid);

            return $index->getSynonyms();
        } catch (Throwable $throwable) {
            $this->logger->error('An error occurred when trying to fetch the synonyms', [
                self::INDEX => $uid,
                self::ERROR_LOG_KEY => $throwable->getMessage(),
            ]);

            throw $throwable;
        }
    }

    /**
     * @param array<non-empty-string, array<int, non-empty-string>> $synonyms
     */
    public function updateSynonyms(string $uid, array $synonyms): void
    {
        try {
            $index = $this->indexOrchestrator->getIndex($uid);
            $this->dispatch(new PreUpdateSynonymsEvent($index, $synonyms));

            $update = $index->updateSynonyms($synonyms);
            $this->dispatch(new PostUpdateSynonymsEvent($index, $update[self::UPDATE_KEY]));
        } catch (Throwable $throwable) {
            $this->logger->error('An error occurred when trying to update the synonyms', [
                self::INDEX => $uid,
                self::ERROR_LOG_KEY => $throwable->getMessage(),
                'synonyms' => $synonyms,
            ]);

            throw $throwable;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resetSynonyms(string $uid): void
    {
        try {
            $index = $this->indexOrchestrator->getIndex($uid);
            $this->dispatch(new PreResetSynonymsEvent($index));

            $update = $index->resetSynonyms();
            $this->dispatch(new PostResetSynonymsEvent($index, $update[self::UPDATE_KEY]));
        } catch (Throwable $throwable) {
            $this->logger->error('An error occurred when trying to reset the synonyms', [
                self::INDEX => $uid,
                self::ERROR_LOG_KEY => $throwable->getMessage(),
            ]);

            throw $throwable;
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
