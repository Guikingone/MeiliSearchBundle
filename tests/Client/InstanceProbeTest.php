<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Client;

use MeiliSearch\Client;
use MeiliSearchBundle\Client\InstanceProbe;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class InstanceProbeTest extends TestCase
{
    public function testSystemInformationsCanBeReturned(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('prettySysInfo')->willReturn([
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
                "outputData" => "4.22 GB",
            ],
            "process" => [
                "memory" => "5.2 MB",
                "cpu" => "0.0 %",
            ],
        ]);

        $probe = new InstanceProbe($client);
        $infos = $probe->getSystemInformations();

        static::assertNotEmpty($infos);
        static::assertArrayHasKey('memoryUsage', $infos);
        static::assertArrayHasKey('processorUsage', $infos);
        static::assertArrayHasKey('global', $infos);
        static::assertArrayHasKey('process', $infos);
    }
}
