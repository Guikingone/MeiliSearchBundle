<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DataProvider;

use MeiliSearchBundle\Document\DocumentLoader;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface PriorityDataProviderInterface
{
    /**
     * Define a priority for this data provider, the providers are filtered using this method in the {@see DocumentLoader::load()}
     *
     * @return int
     */
    public function getPriority(): int;
}
