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
    public function __construct(private readonly SearchEntryPointInterface $searchEntryPoint)
    {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('search', [$this, 'search']),
        ];
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return SearchResultInterface<string, mixed>
     *
     * {@see SearchEntryPointInterface::search()}
     */
    public function search(string $index, string $query, array $options = []): SearchResultInterface
    {
        return $this->searchEntryPoint->search($index, $query, $options);
    }
}
