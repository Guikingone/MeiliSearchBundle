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
     * @param string $index
     * @param int    $updateId
     *
     * @return UpdateInterface
     *
     * @throws Throwable
     */
    public function getUpdate(string $index, int $updateId): UpdateInterface;

    /**
     * @param string $uid
     *
     * @return array<int,UpdateInterface>
     *
     * @throws Throwable
     */
    public function getUpdates(string $uid): array;
}
