<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Update;

use MeiliSearchBundle\src\Update\UpdateOrchestratorInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableUpdateOrchestrator implements UpdateOrchestratorInterface
{
    /**
     * @var UpdateOrchestratorInterface
     */
    private $updateOrchestrator;

    /**
     * @var array<int,Update>
     */
    private $retrievedUpdates = [];

    /**
     * {@inheritdoc}
     */
    public function getUpdate(string $uid, int $updateId): Update
    {
        $update = $this->updateOrchestrator->getUpdate($uid, $updateId);

        $this->retrievedUpdates[$updateId] = $update;

        return $update;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdates(string $uid): array
    {
        return $this->updateOrchestrator->getUpdates($uid);
    }

    /**
     * @return array<int,Update>
     */
    public function getRetrievedUpdates(): array
    {
        return $this->retrievedUpdates;
    }
}
