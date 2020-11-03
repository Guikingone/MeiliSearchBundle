<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Health;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface HealthEntryPointInterface
{
    public function isUp(): bool;
}
