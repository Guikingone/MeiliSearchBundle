<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface InstanceProbeInterface
{
    public function getSystemInformations(): array;
}
