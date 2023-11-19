<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Search;

use MeiliSearchBundle\Exception\RuntimeException;
use SplObjectStorage;
use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class FallbackSearchEntrypoint implements SearchEntryPointInterface
{
    private readonly SplObjectStorage $failedSearchEntryPoints;

    /**
     * @param SearchEntryPointInterface[]|iterable $searchEntryPoints
     */
    public function __construct(private readonly iterable $searchEntryPoints)
    {
        $this->failedSearchEntryPoints = new SplObjectStorage();
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $index, string $query, array $options = []): SearchResultInterface
    {
        foreach ($this->searchEntryPoints as $searchEntryPoint) {
            if ($this->failedSearchEntryPoints->contains($searchEntryPoint)) {
                continue;
            }

            try {
                return $searchEntryPoint->search($index, $query, $options);
            } catch (Throwable) {
                $this->failedSearchEntryPoints->attach($searchEntryPoint);

                continue;
            }
        }

        throw new RuntimeException('No search entrypoint is able to perform the search');
    }
}
