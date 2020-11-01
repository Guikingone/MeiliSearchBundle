<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Dump;

use Throwable;
use MeiliSearch\Client;
use MeiliSearchBundle\Event\Dump\DumpCreatedEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DumpOrchestrator implements DumpOrchestratorInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var EventDispatcherInterface|null
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Client $client,
        ?EventDispatcherInterface $eventDispatcher = null,
        ?LoggerInterface $logger = null
    ) {
        $this->client = $client;
        $this->eventDispatcher = $eventDispatcher;
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
            $this->logger->critical(sprintf('An error occurred when trying to create a new dump'), [
                'error' => $throwable->getMessage(),
            ]);

            throw $throwable;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(string $uid): array
    {
        try {
            return $this->client->getDumpStatus($uid);
        } catch (Throwable $throwable) {
            $this->logger->critical(sprintf('An error occurred when trying to fetch the dump status'), [
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
