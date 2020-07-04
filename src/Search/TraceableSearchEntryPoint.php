<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Search;

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
     * @var array<string,array>
     */
    private $data = [];

    public function __construct(SearchEntryPointInterface $searchEntryPoint)
    {
        $this->searchEntryPoint = $searchEntryPoint;
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $index, string $query, array $options = []): SearchResultInterface
    {
        $result = $this->searchEntryPoint->search($index, $query, $options);

        $this->data[$index][] = [
            'index' => $index,
            'query' => $result->getQuery(),
            'options' => $options,
            'result' => $result->toArray(),
        ];

        return $result;
    }

    /**
     * @return array<string,array>
     */
    public function getSearch(): array
    {
        return $this->data;
    }
}
