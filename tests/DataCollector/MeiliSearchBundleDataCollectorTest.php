<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\DataCollector;

use MeiliSearchBundle\DataCollector\MeiliSearchBundleDataCollector;
use MeiliSearchBundle\Event\SearchEventListInterface;
use PHPUnit\Framework\TestCase;

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
}
