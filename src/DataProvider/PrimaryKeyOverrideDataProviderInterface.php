<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DataProvider;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface PrimaryKeyOverrideDataProviderInterface
{
    /**
     * Define the primary key used by the current document.
     */
    public function getPrimaryKey(): string;
}
