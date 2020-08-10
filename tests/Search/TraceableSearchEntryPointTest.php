<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Search;

use MeiliSearchBundle\Search\SearchEntryPointInterface;
use MeiliSearchBundle\Search\SearchResultInterface;
use MeiliSearchBundle\Search\TraceableSearchEntryPoint;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableSearchEntryPointTest extends TestCase
{
    public function testSearchDataAreAvailable(): void
    {
        $searchResult = $this->createMock(SearchResultInterface::class);
        $searchResult->expects(self::once())->method('getQuery')->willReturn('q=bar');

        $searchEntryPoint = $this->createMock(SearchEntryPointInterface::class);
        $searchEntryPoint->expects(self::once())->method('search')->willReturn($searchResult);

        $traceableSearchEntryPoint = new TraceableSearchEntryPoint($searchEntryPoint);
        $result = $traceableSearchEntryPoint->search('foo', 'q=bar');

        static::assertNotEmpty($traceableSearchEntryPoint->getData());
        static::assertSame($searchResult, $result);
    }
}
