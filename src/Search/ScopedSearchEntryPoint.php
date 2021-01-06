<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Search;

use InvalidArgumentException;
use RuntimeException;
use function array_key_exists;
use function count;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ScopedSearchEntryPoint implements SearchEntryPointInterface
{
    /**
     * @var array<string, <int, string>>
     */
    private $scopedIndexes;

    /**
     * @var SearchEntryPointInterface
     */
    private $searchEntryPoint;

    /**
     * @param array<string, <int, string>> $scopedIndexes
     * @param SearchEntryPointInterface    $searchEntryPoint
     */
    public function __construct(
        array $scopedIndexes,
        SearchEntryPointInterface $searchEntryPoint
    ) {
        $this->scopedIndexes = $scopedIndexes;
        $this->searchEntryPoint = $searchEntryPoint;
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $index, string $query, array $options = []): SearchResultInterface
    {
        if (!array_key_exists($index, $this->scopedIndexes)) {
            throw new InvalidArgumentException('The desired index is not available');
        }

        foreach ($this->scopedIndexes[$index] as $usedIndex) {
            $result = $this->searchEntryPoint->search($usedIndex, $query, $options);

            if (0 === count($result->getHits())) {
                continue;
            }

            return $result;
        }

        throw new RuntimeException('No result can be found');
    }
}
