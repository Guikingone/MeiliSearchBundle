<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Search;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class NullSearchEntryPoint implements SearchEntryPointInterface
{
    /**
     * {@inheritdoc}
     */
    public function search(string $index, string $query, array $options = []): SearchResultInterface
    {
        return new SearchResult();
    }
}
