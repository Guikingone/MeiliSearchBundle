<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Search;

use MeiliSearchBundle\Search\SearchResult;
use PHPUnit\Framework\TestCase;
use function array_key_exists;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SearchResultTest extends TestCase
{
    public function testHitsCanBeFiltered(): void
    {
        $result = SearchResult::create([
            [
                'id' => 1,
                'title' => 'foo',
            ],
            [
                'id' => 2,
                'title' => 'foobar',
            ],
        ], 0, 0, 1, false, 100, 'foo');

        $result->filter(function (array $hit, int $key): bool {
            return array_key_exists('title', $hit) && $hit['title'] === 'foo';
        });

        static::assertArrayHasKey('hits', $result->toArray());
        static::assertArrayHasKey('offset', $result->toArray());
        static::assertArrayHasKey('limit', $result->toArray());
        static::assertArrayHasKey('nbHits', $result->toArray());
        static::assertArrayHasKey('exhaustiveNbHits', $result->toArray());
        static::assertArrayHasKey('processingTimeMs', $result->toArray());
        static::assertArrayHasKey('query', $result->toArray());
        static::assertArrayHasKey('exhaustiveFacetsCount', $result->toArray());
        static::assertArrayHasKey('facetsDistribution', $result->toArray());
        static::assertSame(1, $result->count());
    }
}
