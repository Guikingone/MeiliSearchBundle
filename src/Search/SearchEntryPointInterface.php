<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Search;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface SearchEntryPointInterface
{
    /**
     * @param string              $index
     * @param string              $query
     * @param array<string,mixed> $options
     *
     * @return SearchResultInterface
     */
    public function search(string $index, string $query, array $options = []): SearchResultInterface;
}
