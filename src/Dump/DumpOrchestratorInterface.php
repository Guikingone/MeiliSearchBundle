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
     * @return array<string, string>
     *
     * @throws Throwable
     */
    public function create(): array;

    /**
     * @return array<string, string>
     *
     * @throws Throwable
     */
    public function getStatus(string $dump): array;
}
