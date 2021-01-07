<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Search;

use InvalidArgumentException;
use MeiliSearchBundle\Search\ScopedSearchEntryPoint;
use MeiliSearchBundle\Search\SearchEntryPointInterface;
use MeiliSearchBundle\Search\SearchResultInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ScopedSearchEntryPointTest extends TestCase
{
    public function testEntryPointCannotSearchOnInvalidIndex(): void
    {
        $entryPoint = $this->createMock(SearchEntryPointInterface::class);

        $scopedEntryPoint = new ScopedSearchEntryPoint([], $entryPoint);

        self::expectException(InvalidArgumentException::class);
        self::expectErrorMessage('The desired index is not available');
        self::expectExceptionCode(0);
        $scopedEntryPoint->search('foo', 'bar');
    }

    public function testEntryPointCannotBeSearchedWithoutHits(): void
    {
        $result = $this->createMock(SearchResultInterface::class);
        $result->expects(self::once())->method('getHits')->willReturn([]);

        $secondResult = $this->createMock(SearchResultInterface::class);
        $secondResult->expects(self::once())->method('getHits')->willReturn([]);

        $entryPoint = $this->createMock(SearchEntryPointInterface::class);
        $entryPoint->expects(self::exactly(2))->method('search')->withConsecutive([
            self::equalTo('bar'), self::equalTo('bar'),
        ], [
            self::equalTo('random'), self::equalTo('bar'),
        ])->willReturnOnConsecutiveCalls($result, $secondResult);

        $scopedEntryPoint = new ScopedSearchEntryPoint([
            'foo' => ['bar', 'random'],
            'bar' => ['foo', 'random'],
        ], $entryPoint);

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('No result can be found');
        self::expectExceptionCode(0);
        $scopedEntryPoint->search('foo', 'bar');
    }

    public function testEntryPointCanReturnResult(): void
    {
        $result = $this->createMock(SearchResultInterface::class);
        $result->expects(self::exactly(2))->method('getHits')->willReturn([
            'id' => 'foo',
            'title' => 'bar',
        ]);

        $secondResult = $this->createMock(SearchResultInterface::class);
        $secondResult->expects(self::never())->method('getHits');

        $entryPoint = $this->createMock(SearchEntryPointInterface::class);
        $entryPoint->expects(self::once())->method('search')->with(self::equalTo('bar'), self::equalTo('bar'))->willReturn($result);

        $scopedEntryPoint = new ScopedSearchEntryPoint([
            'foo' => ['bar', 'random'],
            'bar' => ['foo', 'random'],
        ], $entryPoint);

        $searchResult = $scopedEntryPoint->search('foo', 'bar');

        self::assertArrayHasKey('id', $searchResult->getHits());
    }
}
