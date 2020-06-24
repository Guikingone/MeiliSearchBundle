<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableSearchEntryPoint implements SearchEntryPointInterface
{
    /**
     * @var SearchEntryPointInterface
     */
    private $searchEntryPoint;

    /**
     * @var array<string, array>
     */
    private $search = [];

    public function __construct(SearchEntryPointInterface $searchEntryPoint)
    {
        $this->searchEntryPoint = $searchEntryPoint;
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $index, string $query, array $options = null): Search
    {
        $result = $this->searchEntryPoint->search($index, $query, $options);

        $this->search[$index] = [
            'query' => $result->getQuery(),
            'options' => $options,
        ];

        return $result;
    }
}
