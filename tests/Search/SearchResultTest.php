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
        ], 0, 0, 1, false, 100, 'q=foo');

        $result->filter(function (array $hit, int $key): bool {
            return array_key_exists('title', $hit) && $hit['title'] === 'foo';
        });

        static::assertSame(1, $result->count());
    }
}
