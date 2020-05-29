<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Tests\DataCollector;

use MeiliSearchBundle\Client\ClientInterface;
use MeiliSearchBundle\Client\TraceableClient;
use MeiliSearchBundle\DataCollector\MeiliSearchBundleDataCollector;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchBundleDataCollectorTest extends TestCase
{
    public function testCollectorCanRetrieveSystemInformations(): void
    {
        $meiliClient = $this->createMock(ClientInterface::class);
        $meiliClient->expects(self::once())->method('getSystemInformations')->willReturn([
            "memoryUsage" => "56.3 %",
            "processorUsage" => [
                "0.0 %",
                "25.0 %",
                "4.5 %",
                "20.7 %",
                "4.0 %",
                "18.1 %",
                "3.7 %",
                "14.8 %",
                "3.4 %",
            ],
            "global" => [
                "totalMemory" => "17.18 GB",
                "usedMemory" => "9.67 GB",
                "totalSwap" => "4.29 GB",
                "usedSwap" => "2.58 GB",
                "inputData" => "29.82 GB",
                "outputData" => "4.22 GB"
            ],
            "process" => [
                "memory" => "5.2 MB",
                "cpu" => "0.0 %"
            ],
        ]);

        $client = new TraceableClient($meiliClient);

        $collector = new MeiliSearchBundleDataCollector($client);
        $collector->lateCollect();

        static::assertNotEmpty($collector->getSystemInformations());
    }
}
