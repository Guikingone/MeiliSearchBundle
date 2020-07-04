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
     * @param string $index
     *
     * @return array<string,array|null>
     *
     * @throws Throwable If an error occurs, it should be logged and thrown back.
     */
    public function retrieveSettings(string $index): array;

    /**
     * @param string                   $uid
     * @param array<string,array|null> $updatePayload
     *
     * @throws Throwable If an error occurs, it should be logged and thrown back.
     */
    public function updateSettings(string $uid, array $updatePayload): void;

    /**
     * @param string $uid
     *
     * @throws Throwable If an error occurs, it should be logged and thrown back.
     */
    public function resetSettings(string $uid): void;
}
