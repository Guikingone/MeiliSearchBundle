<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Dump;

use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface DumpOrchestratorInterface
{
    /**
     * @throws Throwable
     * @return array<string, string>
     *
     */
    public function create(): array;
}
