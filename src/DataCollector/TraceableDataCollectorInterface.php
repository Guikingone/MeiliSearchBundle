<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DataCollector;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface TraceableDataCollectorInterface
{
    /**
     * Must reset the collected data
     */
    public function reset(): void;
}
