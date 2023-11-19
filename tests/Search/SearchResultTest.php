<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Search;

use MeiliSearchBundle\Search\SearchResult;
use PHPUnit\Framework\TestCase;
use Traversable;

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

        $result->filter(function (array $hit, int $_): bool {
            return array_key_exists('title', $hit) && $hit['title'] === 'foo';
        });

        static::assertInstanceOf(Traversable::class, $result->getIterator());
        static::assertEquals([
            [
                'id' => 1,
                'title' => 'foo',
            ],
        ], $result->getHits());
        static::assertEquals([
            'id' => 1,
            'title' => 'foo',
        ], $result->getHit(0));
        static::assertNull($result->getHit(1));
        static::assertArrayHasKey('hits', $result->toArray());
        static::assertArrayHasKey('offset', $result->toArray());
        static::assertArrayHasKey('limit', $result->toArray());
        static::assertArrayHasKey('nbHits', $result->toArray());
        static::assertSame(1, $result->getNbHits());
        static::assertArrayHasKey('exhaustiveNbHits', $result->toArray());
        static::assertFalse($result->getExhaustiveNbHits());
        static::assertArrayHasKey('processingTimeMs', $result->toArray());
        static::assertSame(100, $result->getProcessingTimeMs());
        static::assertArrayHasKey('query', $result->toArray());
        static::assertArrayHasKey('exhaustiveFacetsCount', $result->toArray());
        static::assertNull($result->getExhaustiveFacetsCount());
        static::assertArrayHasKey('facetsDistribution', $result->toArray());
        static::assertEmpty($result->getFacetsDistribution());
        static::assertCount(1, $result);
        static::assertSame(2, $result->getLastIdentifier());
    }
}
