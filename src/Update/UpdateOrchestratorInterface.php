<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Update;

use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface UpdateOrchestratorInterface
{
    /**
     *
     * @throws Throwable
     */
    public function getUpdate(string $index, int $updateId): UpdateInterface;

    /**
     *
     * @throws Throwable
     * @return array<int, UpdateInterface>
     */
    public function getUpdates(string $uid): array;
}
