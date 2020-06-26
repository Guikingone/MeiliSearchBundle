<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface SearchEntryPointInterface
{
    public function search(string $index, string $query, array $options = null): SearchInterface;
}
