<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Twig;

use MeiliSearchBundle\Search\SearchEntryPointInterface;
use MeiliSearchBundle\Search\SearchResultInterface;
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

    public function __construct(SearchEntryPointInterface $searchEntryPoint)
    {
        $this->searchEntryPoint = $searchEntryPoint;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('search', ['search', $this]),
        ];
    }

    /**
     * @param string               $index
     * @param string               $query
     * @param array<string,mixed>  $options
     *
     * @return SearchResultInterface
     *
     * {@see SearchEntryPointInterface::search()}
     */
    public function search(string $index, string $query, array $options = []): SearchResultInterface
    {
        return $this->searchEntryPoint->search($index, $query, $options);
    }
}
