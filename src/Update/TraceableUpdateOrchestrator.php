<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Update;

use MeiliSearchBundle\DataCollector\TraceableDataCollectorInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableUpdateOrchestrator implements UpdateOrchestratorInterface, TraceableDataCollectorInterface
{
    private const UPDATE_DATA_KEY = 'retrievedUpdates';

    /**
     * @var UpdateOrchestratorInterface
     */
    private $updateOrchestrator;

    /**
     * @var array<string,array>
     */
    private $data = [
        self::UPDATE_DATA_KEY => [],
    ];

    public function __construct(UpdateOrchestratorInterface $updateOrchestrator)
    {
        $this->updateOrchestrator = $updateOrchestrator;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdate(string $index, int $updateId): UpdateInterface
    {
        $update = $this->updateOrchestrator->getUpdate($index, $updateId);

        $this->data[self::UPDATE_DATA_KEY][$index][] = $update;

        return $update;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdates(string $index): array
    {
        $updates = $this->updateOrchestrator->getUpdates($index);

        $this->data[self::UPDATE_DATA_KEY][$index] = $updates;

        return $updates;
    }

    /**
     * @return array<string,array>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->data = [
            self::UPDATE_DATA_KEY => [],
        ];
    }
}
