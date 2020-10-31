<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\DataCollector;

use MeiliSearchBundle\DataCollector\MeiliSearchBundleDataCollector;
use MeiliSearchBundle\Event\SearchEventListInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchBundleDataCollectorTest extends TestCase
{
    public function testCollectorIsConfigured(): void
    {
        $searchList = $this->createMock(SearchEventListInterface::class);

        $collector = new MeiliSearchBundleDataCollector($searchList);

        static::assertSame('meilisearch', $collector->getName());
    }

    public function testCollectorCannotCollect(): void
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);

        $searchList = $this->createMock(SearchEventListInterface::class);
        $searchList->expects(self::never())->method('getPostSearchEvents');

        $collector = new MeiliSearchBundleDataCollector($searchList);
        $collector->collect($request, $response);
    }

    public function testCollectorCanLateCollectAndReset(): void
    {
        $searchList = $this->createMock(SearchEventListInterface::class);
        $searchList->expects(self::exactly(2))->method('getPostSearchEvents')->willReturn([]);

        $collector = new MeiliSearchBundleDataCollector($searchList);
        $collector->lateCollect();

        static::assertNotEmpty($collector->getSearches());
        static::assertArrayHasKey('count', $collector->getSearches());
        static::assertArrayHasKey('searches', $collector->getSearches());

        $collector->reset();

        static::assertNotEmpty($collector->getSearches());
        static::assertArrayHasKey('count', $collector->getSearches());
        static::assertSame(0, $collector->getSearches()['count']);
        static::assertArrayHasKey('searches', $collector->getSearches());
        static::assertSame([], $collector->getSearches()['searches']);
    }
}
