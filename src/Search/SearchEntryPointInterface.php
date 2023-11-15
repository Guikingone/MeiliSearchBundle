<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Search;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface SearchEntryPointInterface
{
    /**
     * The core entrypoint for searching in documents.
     *
     * @param array<string, mixed> $options
     *
     * @return SearchResultInterface<string, mixed>
     */
    public function search(string $index, string $query, array $options = []): SearchResultInterface;
}
