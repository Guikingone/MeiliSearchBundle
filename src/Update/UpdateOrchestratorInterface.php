<?php

declare(strict_types=1);

namespace MeiliSearchBundle\src\Update;

use MeiliSearchBundle\Update\Update;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface UpdateOrchestratorInterface
{
    public function getUpdate(string $uid, int $updateId): Update;

    public function getUpdates(string $uid): array;
}
