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
     * {@inheritdoc}
     */
    public function getUpdate(string $uid, int $updateId): Update
    {
        return $this->updateOrchestrator->getUpdate($uid, $updateId);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdates(string $uid): array
    {
        return $this->updateOrchestrator->getUpdates($uid);
    }
}
