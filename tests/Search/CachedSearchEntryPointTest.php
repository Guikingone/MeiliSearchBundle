<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Search;

use MeiliSearchBundle\Cache\SearchResultCacheOrchestratorInterface;
use MeiliSearchBundle\Exception\InvalidArgumentException;
use MeiliSearchBundle\Search\CachedSearchEntryPoint;
use MeiliSearchBundle\Search\SearchEntryPointInterface;
use MeiliSearchBundle\Search\SearchResultInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class CachedSearchEntryPointTest extends TestCase
{
    public function testEntryPointCanUseCachedSearchResultWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $searchResult = $this->createMock(SearchResultInterface::class);

        $cacheOrchestrator = $this->createMock(SearchResultCacheOrchestratorInterface::class);
        $cacheOrchestrator->expects(self::once())->method('get')->with(self::equalTo('foo_random'))->willReturn(
            $searchResult
        );

        $entryPoint = $this->createMock(SearchEntryPointInterface::class);
        $entryPoint->expects(self::never())->method('search');

        $searchEntryPoint = new CachedSearchEntryPoint($cacheOrchestrator, $entryPoint);
        $searchEntryPoint->search('foo', 'Random');
    }

    public function testEntryPointCanUseCachedSearchResultWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $searchResult = $this->createMock(SearchResultInterface::class);

        $cacheOrchestrator = $this->createMock(SearchResultCacheOrchestratorInterface::class);
        $cacheOrchestrator->expects(self::once())->method('get')->with(self::equalTo('foo_random'))->willReturn(
            $searchResult
        );

        $entryPoint = $this->createMock(SearchEntryPointInterface::class);
        $entryPoint->expects(self::never())->method('search');

        $searchEntryPoint = new CachedSearchEntryPoint($cacheOrchestrator, $entryPoint, $logger);
        $searchEntryPoint->search('foo', 'Random');
    }

    public function testEntryPointCanFallbackToDefaultSearchEntryPointWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $cacheOrchestrator = $this->createMock(SearchResultCacheOrchestratorInterface::class);
        $cacheOrchestrator->expects(self::once())->method('get')->willThrowException(
            new InvalidArgumentException('The desired search result cannot be found')
        );
        $cacheOrchestrator->expects(self::once())->method('add');

        $searchResult = $this->createMock(SearchResultInterface::class);

        $entryPoint = $this->createMock(SearchEntryPointInterface::class);
        $entryPoint->expects(self::once())->method('search')->willReturn($searchResult);

        $searchEntryPoint = new CachedSearchEntryPoint($cacheOrchestrator, $entryPoint);
        $result = $searchEntryPoint->search('foo', 'Random');

        static::assertSame($searchResult, $result);
    }

    public function testEntryPointCanFallbackToDefaultSearchEntryPointWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $cacheOrchestrator = $this->createMock(SearchResultCacheOrchestratorInterface::class);
        $cacheOrchestrator->expects(self::once())->method('get')->willThrowException(
            new InvalidArgumentException('The desired search result cannot be found')
        );
        $cacheOrchestrator->expects(self::once())->method('add');

        $searchResult = $this->createMock(SearchResultInterface::class);

        $entryPoint = $this->createMock(SearchEntryPointInterface::class);
        $entryPoint->expects(self::once())->method('search')->willReturn($searchResult);

        $searchEntryPoint = new CachedSearchEntryPoint($cacheOrchestrator, $entryPoint, $logger);
        $result = $searchEntryPoint->search('foo', 'Random');

        static::assertSame($searchResult, $result);
    }
}
