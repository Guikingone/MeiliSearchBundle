<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Cache;

use InvalidArgumentException;
use MeiliSearchBundle\Cache\SearchResultCacheOrchestrator;
use MeiliSearchBundle\Exception\RuntimeException;
use MeiliSearchBundle\Search\SearchResultInterface;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SearchResultCacheOrchestratorTest extends TestCase
{
    public function testOrchestratorCannotAddNewItemIfItAlreadyExist(): void
    {
        $searchResult = $this->createMock(SearchResultInterface::class);

        $cacheItemPool = new ArrayAdapter();
        $orchestrator = new SearchResultCacheOrchestrator($cacheItemPool);
        $orchestrator->add('foo', $searchResult);

        static::expectException(InvalidArgumentException::class);
        $orchestrator->add('foo', $searchResult);
    }

    public function testOrchestratorCanAddNewItemWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $searchResult = $this->createMock(SearchResultInterface::class);
        $searchResult->expects(self::exactly(2))->method('toArray')->willReturn([]);

        $cacheItemPool = new ArrayAdapter();
        $orchestrator = new SearchResultCacheOrchestrator($cacheItemPool);
        $orchestrator->add('foo', $searchResult);
    }

    public function testOrchestratorCanAddNewItemWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(self::equalTo('A search result has been saved'), [
            'identifier' => 'foo',
            'result' => [],
        ]);

        $searchResult = $this->createMock(SearchResultInterface::class);
        $searchResult->expects(self::exactly(2))->method('toArray')->willReturn([]);

        $cacheItemPool = new ArrayAdapter();
        $orchestrator = new SearchResultCacheOrchestrator($cacheItemPool, $logger);
        $orchestrator->add('foo', $searchResult);
    }

    public function testOrchestratorCannotReturnAnUndefinedSearchResult(): void
    {
        $cacheItemPool = new ArrayAdapter();
        $orchestrator = new SearchResultCacheOrchestrator($cacheItemPool);

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('The desired search result cannot be found');
        static::expectExceptionCode(0);
        $orchestrator->get('foo');
    }

    public function testOrchestratorCannotReturnAnInvalidSearchResult(): void
    {
        $searchResult = $this->createMock(SearchResultInterface::class);
        $searchResult->expects(self::never())->method('toArray');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(self::equalTo('A search result has been retrieved'), [
            'identifier' => 'foo',
        ]);

        $cacheItemPool = new ArrayAdapter();
        $item = $cacheItemPool->getItem('foo');
        $item->set('foo');
        $cacheItemPool->save($item);

        $orchestrator = new SearchResultCacheOrchestrator($cacheItemPool, $logger);

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('The desired search result does not contain valid data');
        static::expectExceptionCode(0);
        $orchestrator->get('foo');
    }

    public function testOrchestratorCanReturnAValidSearchResult(): void
    {
        $searchResult = $this->createMock(SearchResultInterface::class);
        $searchResult->expects(self::exactly(2))->method('toArray')->willReturn([
            'hits' => [],
            'offset' => 0,
            'limit' => 20,
            'nbHits' => 0,
            'exhaustiveNbHits' => false,
            'processingTimeMs' => 15,
            'query' => 'foo',
            'exhaustiveFacetsCount' => null,
            'facetsDistribution' => [],
        ]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::exactly(2))->method('info');

        $cacheItemPool = new ArrayAdapter();
        $orchestrator = new SearchResultCacheOrchestrator($cacheItemPool, $logger);

        $orchestrator->add('foo', $searchResult);

        $result = $orchestrator->get('foo');

        static::assertInstanceOf(SearchResultInterface::class, $result);
    }

    public function testOrchestratorCannotClear(): void
    {
        $cacheItemPool = $this->createMock(CacheItemPoolInterface::class);
        $cacheItemPool->expects(self::once())->method('clear')->willReturn(false);

        $orchestrator = new SearchResultCacheOrchestrator($cacheItemPool);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('The cache pool cannot be cleared');
        static::expectExceptionCode(0);
        $orchestrator->clear();
    }

    public function testOrchestratorCanClear(): void
    {
        $cacheItemPool = $this->createMock(CacheItemPoolInterface::class);
        $cacheItemPool->expects(self::once())->method('clear')->willReturn(true);

        $orchestrator = new SearchResultCacheOrchestrator($cacheItemPool);
        $orchestrator->clear();
    }
}
