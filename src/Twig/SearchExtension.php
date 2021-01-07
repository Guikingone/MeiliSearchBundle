<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Twig;

use MeiliSearchBundle\Search\ScopedSearchEntryPoint;
use MeiliSearchBundle\Search\SearchEntryPointInterface;
use MeiliSearchBundle\Search\SearchResultInterface;
use RuntimeException;
use Twig\Extension\AbstractExtension;
use Twig\Extension\RuntimeExtensionInterface;
use Twig\TwigFunction;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SearchExtension extends AbstractExtension implements RuntimeExtensionInterface
{
    /**
     * @var SearchEntryPointInterface
     */
    private $searchEntryPoint;

    /**
     * @var ScopedSearchEntryPoint|null
     */
    private $scopedEntryPoint;

    public function __construct(
        SearchEntryPointInterface $searchEntryPoint,
        ?ScopedSearchEntryPoint $scopedEntryPoint = null
    ) {
        $this->searchEntryPoint = $searchEntryPoint;
        $this->scopedEntryPoint = $scopedEntryPoint;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('search', [$this, 'search']),
            new TwigFunction('scoped_search', [$this, 'scopedSearch']),
        ];
    }

    /**
     * @param string                $index
     * @param string                $query
     * @param array<string, mixed>  $options
     *
     * @return SearchResultInterface<string, mixed>
     *
     * {@see SearchEntryPointInterface::search()}
     */
    public function search(string $index, string $query, array $options = []): SearchResultInterface
    {
        return $this->searchEntryPoint->search($index, $query, $options);
    }

    /**
     * @param string               $index
     * @param string               $query
     * @param array<string, mixed> $options
     *
     * @return SearchResultInterface<string, mixed>
     *
     * {@see SearchEntryPointInterface::search()}
     */
    public function scopedSearch(string $index, string $query, array $options = []): SearchResultInterface
    {
        if (null === $this->scopedEntryPoint) {
            throw new RuntimeException('The "scoped_indexes" key must be enabled to use the scoped search, more info in the documentation');
        }

        return $this->scopedEntryPoint->search($index, $query, $options);
    }
}
