<?php

declare(strict_types=1);

namespace MeiliSearchBundle\ExpressionLanguage;

use MeiliSearchBundle\Search\Search;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

use function count;
use function explode;
use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SearchExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @return ExpressionFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'search',
                static fn (
                    string $index,
                    string $query,
                    ?string $filters = 'empty',
                    ?int $max = null
                ): string => sprintf('IN %s ON %s WHERE %s MAX %d', $index, $query, $filters, $max),
                static function (
                    array $arguments,
                    string $index,
                    string $query,
                    ?string $filters = null,
                    ?int $max = null
                ): Search {
                    $search = Search::on($index, $query);
                    if (null === $max) {
                        return $search;
                    }
                    if (null !== $filters && count(explode(' ', $filters)) > 1) {
                        $filters = explode(' ', $filters);
                        $search->where($filters[0], $filters[1], $filters[2]);
                    }
                    return $search->max($max);
                }
            ),
        ];
    }
}
