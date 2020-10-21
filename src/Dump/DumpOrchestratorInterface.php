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
     */
    public function create(): void;

    /**
     * @throws Throwable
     */
    public function getStatus(string $dump): array;
}