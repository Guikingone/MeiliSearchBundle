<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Update;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableUpdateOrchestrator implements UpdateOrchestratorInterface
{
    private const INDEX_LOG_KEY = 'index';

    /**
     * @var UpdateOrchestratorInterface
     */
    private $updateOrchestrator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array<string,array>
     */
    private $data = [
        'retrievedUpdates' => [],
    ];

    public function __construct(
        UpdateOrchestratorInterface $updateOrchestrator,
        ?LoggerInterface $logger = null
    ) {
        $this->updateOrchestrator = $updateOrchestrator;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdate(string $index, int $updateId): UpdateInterface
    {
        $update = $this->updateOrchestrator->getUpdate($index, $updateId);

        $this->logger->info('An update as been retrieved', [
            self::INDEX_LOG_KEY => $index,
            'update' => $update,
        ]);

        $this->data['retrievedUpdates'][$index][] = $update;

        return $update;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdates(string $index): array
    {
        $updates = $this->updateOrchestrator->getUpdates($index);

        $this->logger->info('A set of updates has been retrieved', [
            self::INDEX_LOG_KEY => $index,
        ]);

        $this->data['retrievedUpdates'][$index] = $updates;

        return $updates;
    }

    /**
     * @return array<string,array>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
