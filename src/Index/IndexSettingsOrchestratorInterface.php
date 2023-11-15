<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Index;

use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface IndexSettingsOrchestratorInterface
{
    /**
     *
     * @throws Throwable If an error occurs, it should be logged and thrown back.
     * @return array<string,array|null>
     */
    public function retrieveSettings(string $index): array;

    /**
     * @throws Throwable If an error occurs, it should be logged and thrown back.
     * @param array<string,array|null> $updatePayload
     */
    public function updateSettings(string $uid, array $updatePayload): void;

    /**
     * @throws Throwable If an error occurs, it should be logged and thrown back.
     */
    public function resetSettings(string $uid): void;
}
