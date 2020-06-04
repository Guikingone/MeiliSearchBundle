<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableSearchEntryPoint implements SearchEntryPointInterface
{
    /**
     * @var SearchEntryPoint
     */
    private $searchEntryPoint;

    /**
     * @var array<string, array>
     */
    private $search = [];

    public function __construct(SearchEntryPoint $searchEntryPoint)
    {
        $this->searchEntryPoint = $searchEntryPoint;
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $index, string $query, array $options = null): array
    {
        $result = $this->searchEntryPoint->search($index, $query, $options);

        $this->search[$index] = [
            'query' => $query,
            'options' => $options,
        ];

        return $result;
    }
}
