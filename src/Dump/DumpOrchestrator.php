<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Dump;

use Meilisearch\Client;
use MeiliSearchBundle\Event\Dump\DumpCreatedEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DumpOrchestrator implements DumpOrchestratorInterface
{
    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly Client $client,
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function create(): array
    {
        try {
            $dump = $this->client->createDump();

            $this->dispatch(new DumpCreatedEvent($dump['uid']));

            return $dump;
        } catch (Throwable $throwable) {
            $this->logger->critical('An error occurred when trying to create a new dump', [
                'error' => $throwable->getMessage(),
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
