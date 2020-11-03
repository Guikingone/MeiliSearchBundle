<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Settings;

use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface SettingsEntryPointInterface
{
    /**
     * @throws Throwable
     */
    public function getSettings(string $index): Settings;
}
